<?php

namespace App\Http\Controllers;

use App\Models\IctIncidentReport;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AdminActionNotificationService;
use App\Services\AdminActivityLogger;

class IctIncidentReportController extends Controller
{
    public function __construct(
        private readonly AdminActionNotificationService $adminActionNotifications,
        private readonly AdminActivityLogger $activityLogger
    ) {}

    public function index(Request $request)
    {
        $query = IctIncidentReport::where('user_id', Auth::id());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('document_number', 'like', '%' . $search . '%')
                    ->orWhere('department', 'like', '%' . $search . '%')
                    ->orWhere('reported_by', 'like', '%' . $search . '%')
                    ->orWhere('details_of_incident', 'like', '%' . $search . '%');
            });
        }

        $reports = $query->with('serviceRequest:id,control_number,status')
            ->latest()
            ->paginate(15);
        $reports->appends($request->query());
        return view('mis.incident-reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $linkedRequest = null;
        if ($request->filled('service_request_id')) {
            $linkedRequest = ServiceRequest::where('user_id', Auth::id())->findOrFail($request->integer('service_request_id'));
        }

        return view('mis.incident-reports.create', ['user' => Auth::user(), 'linkedRequest' => $linkedRequest]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_number' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:255',
            'reporter_phone' => 'nullable|string|max:50',
            'date_of_incident' => 'required|date',
            'time_of_incident' => 'nullable',
            'nature_of_incident' => 'nullable|array',
            'nature_of_incident.*' => 'string',
            'nature_others' => 'nullable|string|max:255',
            'details_of_incident' => 'nullable|string',
            'service_request_id' => 'nullable|exists:service_requests,id',
        ]);

        if (!empty($validated['service_request_id'])) {
            $linkedRequest = ServiceRequest::where('user_id', Auth::id())->findOrFail($validated['service_request_id']);
            $validated['service_request_id'] = $linkedRequest->id;
        }

        $user = Auth::user();
        $report = DB::transaction(function () use ($validated, $user) {
            $report = IctIncidentReport::create(array_merge($validated, [
                'user_id' => $user->id,
                'reported_by' => $user->full_name,
                'department' => ($validated['department'] ?? null) ?: $user->office,
                'requester_position' => $user->designation,
                'reporter_email' => $user->email,
                'acknowledged_by_name' => $user->full_name,
                'acknowledged_by_role' => $user->designation,
                'noted_by_name' => 'JAY C. LIZA, PhD.',
                'noted_by_role' => 'MIS Head',
                'status' => 'open',
            ]));

            return $report;
        });

        $report->loadMissing('serviceRequest');
        if ($report->serviceRequest) {
            $this->activityLogger->log(
                $report->serviceRequest,
                'incident_created',
                "Incident {$report->document_number} was submitted.",
                [],
                ['incident_report_id' => $report->id]
            );
        }

        $this->notifyAdmins($report, 'ICT incident submitted');

        return redirect()->route('incident-reports.show', $report)
            ->with('success', 'ICT incident report submitted successfully.');
    }

    public function show(IctIncidentReport $incidentReport)
    {
        $this->ensureOwner($incidentReport);
        $incidentReport->load('serviceRequest');

        return view('mis.incident-reports.show', ['report' => $incidentReport]);
    }

    public function edit(IctIncidentReport $incidentReport)
    {
        $this->ensureOwner($incidentReport);

        return redirect()->route('incident-reports.show', $incidentReport);
    }

    public function update(Request $request, IctIncidentReport $incidentReport)
    {
        $this->ensureOwner($incidentReport);

        abort_if(in_array($incidentReport->status, ['resolved', 'closed'], true), 403, 'Completed incident reports can no longer be edited.');

        $validated = $request->validate([
            'id_number' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:255',
            'reporter_phone' => 'nullable|string|max:50',
            'date_of_incident' => 'required|date',
            'time_of_incident' => 'nullable',
            'nature_of_incident' => 'nullable|array',
            'nature_others' => 'nullable|string|max:255',
            'details_of_incident' => 'nullable|string',
        ]);

        $previous = $incidentReport->only(['details_of_incident', 'action_plan']);
        $incidentReport->update($validated + [
            'reported_by' => Auth::user()->full_name,
            'requester_position' => Auth::user()->designation,
            'reporter_email' => Auth::user()->email,
        ]);
        $incidentReport->loadMissing('serviceRequest');
        if ($incidentReport->serviceRequest) {
            $this->activityLogger->log(
                $incidentReport->serviceRequest,
                'incident_updated',
                "Incident {$incidentReport->document_number} was updated.",
                $previous,
                ['incident_report_id' => $incidentReport->id]
            );
        }
        $this->notifyAdmins($incidentReport, 'ICT incident updated');

        return redirect()->route('incident-reports.show', $incidentReport)
            ->with('success', 'ICT Incident Report updated successfully.');
    }

    /** Generate PDF for printing */
    public function generatePdf(IctIncidentReport $incidentReport)
    {
        $this->ensureOwner($incidentReport);

        $pdf = Pdf::loadView('pdf.incident-report', ['report' => $incidentReport])
            ->setPaper('A4', 'portrait');

        $response = $pdf->stream('ICT_Incident_Report_' . $incidentReport->document_number . '.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function ensureOwner(IctIncidentReport $incidentReport): void
    {
        $user = Auth::user();
        $isAdmin = $user instanceof \App\Models\User ? $user->isAdmin() : false;

        abort_unless($incidentReport->user_id === Auth::id() || $isAdmin, 403);
    }

    private function notifyAdmins(IctIncidentReport $report, string $subject): void
    {
        $this->adminActionNotifications->notify(
            $subject,
            ($report->document_number ?: 'Incident report') . ' from ' . ($report->reported_by ?: 'a requester') . '.',
            route('admin.incident-reports.show', $report),
            $report->service_request_id
        );
    }
}
