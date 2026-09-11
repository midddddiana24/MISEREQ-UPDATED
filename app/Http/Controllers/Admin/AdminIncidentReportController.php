<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IctIncidentReport;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Technician;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Services\AdminActivityLogger;

class AdminIncidentReportController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}
    public function index(Request $request)
    {
        $validated = $request->validate(['status' => ['nullable', 'in:open,in_progress,resolved,closed']]);
        $statusFilter = $validated['status'] ?? null;
        $archived = request()->boolean('archived');
        $query = $archived ? IctIncidentReport::onlyTrashed() : IctIncidentReport::query();
        if ($statusFilter) $query->where('status', $statusFilter);
        $reports = $query->with('user')->latest()->paginate(15)->withQueryString();
        $statusCounts = ($archived ? IctIncidentReport::onlyTrashed() : IctIncidentReport::query())
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.incident-reports.index', [
            'reports' => $reports,
            'statusOptions' => $this->statusOptions(),
            'misStaffOptions' => $this->misStaffOptions(),
            'statusCounts' => $statusCounts,
            'statusFilter' => $statusFilter,
            'archived' => $archived,
        ]);
    }

    public function create(Request $request)
    {
        $prefillServiceRequest = $request->filled('service_request_id')
            ? ServiceRequest::with('user')->findOrFail($request->integer('service_request_id'))
            : null;

        return view('admin.incident-reports.create', [
            'users' => $this->requesterOptions($prefillServiceRequest?->user_id),
            'serviceRequests' => $this->serviceRequestOptions($prefillServiceRequest?->id),
            'natureOptions' => $this->natureOptions(),
            'statusOptions' => $this->statusOptions(),
            'misStaffOptions' => $this->misStaffOptions(),
            'prefillServiceRequest' => $prefillServiceRequest,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->resolveServiceRequestLink($request->validate($this->rules()));
        $owner = !empty($validated['user_id']) ? User::find($validated['user_id']) : null;

        $report = IctIncidentReport::create($validated + [
            'requester_position' => $owner?->designation,
            'acknowledged_by_name' => $validated['acknowledged_by_name'] ?? $owner?->full_name,
            'acknowledged_by_role' => $validated['acknowledged_by_role'] ?? $owner?->designation,
            'received_date' => $validated['received_date'] ?? now()->toDateString(),
            'received_time' => $validated['received_time'] ?? now()->format('H:i:s'),
            'noted_by_name' => $validated['noted_by_name'] ?? 'JAY C. LIZA, PhD.',
            'noted_by_role' => $validated['noted_by_role'] ?? 'MIS Head',
            'status' => $validated['status'] ?? 'open',
        ]);
        $this->notifyUser($report, 'An ICT incident report was created for your account.');
        $this->logLinkedActivity(
            $report,
            'incident_created',
            "Incident {$report->document_number} was created.",
            [],
            ['incident_report_id' => $report->id, 'status' => $report->status]
        );

        return redirect()
            ->route('admin.incident-reports.show', $report)
            ->with('success', 'ICT incident report created successfully.');
    }

    public function show(IctIncidentReport $incidentReport)
    {
        $incidentReport->load('user', 'serviceRequest');

        return view('admin.incident-reports.show', [
            'report' => $incidentReport,
            'natureOptions' => $this->natureOptions(),
        ]);
    }

    public function edit(IctIncidentReport $incidentReport)
    {
        $incidentReport->load('user', 'serviceRequest');

        return view('admin.incident-reports.edit', [
            'report' => $incidentReport,
            'users' => $this->requesterOptions($incidentReport->user_id),
            'serviceRequests' => $this->serviceRequestOptions($incidentReport->service_request_id),
            'natureOptions' => $this->natureOptions(),
            'statusOptions' => $this->statusOptions(),
            'misStaffOptions' => $this->misStaffOptions($incidentReport->received_by),
        ]);
    }

    public function update(Request $request, IctIncidentReport $incidentReport)
    {
        $validated = $this->resolveServiceRequestLink($request->validate($this->rules($incidentReport)));
        $owner = !empty($validated['user_id']) ? User::find($validated['user_id']) : null;
        if ($owner) {
            $validated['requester_position'] = $owner->designation;
            $validated['acknowledged_by_name'] = ($validated['acknowledged_by_name'] ?? null) ?: $owner->full_name;
            $validated['acknowledged_by_role'] = ($validated['acknowledged_by_role'] ?? null) ?: $owner->designation;
        }

        if (!empty($validated['received_by']) && empty($incidentReport->received_by)) {
            $validated['received_date'] ??= now()->toDateString();
            $validated['received_time'] ??= now()->format('H:i:s');
        }

        $oldValues = $incidentReport->only(['user_id', 'status', 'action_plan', 'remarks', 'details_of_incident']);
        $incidentReport->update($validated);
        $statusLabel = str($incidentReport->status)->replace('_', ' ')->title();
        $this->logLinkedActivity(
            $incidentReport,
            'incident_updated',
            "Incident {$incidentReport->document_number} is now {$statusLabel}.",
            $oldValues,
            $incidentReport->only(array_keys($oldValues))
        );
        $this->notifyUser($incidentReport, "Your ICT incident report is now {$statusLabel}. MIS updated the record.");

        return redirect()
            ->route('admin.incident-reports.show', $incidentReport)
            ->with('success', 'ICT incident report updated successfully.');
    }

    public function destroy(Request $request, IctIncidentReport $incidentReport)
    {
        $validated = $request->validate(['archive_reason' => ['required', 'string', 'max:1000']]);
        $this->notifyUser(
            $incidentReport,
            'Your ICT incident report was archived by MIS.',
            route('incident-reports.index')
        );
        $incidentReport->update(['archived_at' => now(), 'archived_by' => Auth::id(), 'archive_reason' => $validated['archive_reason']]);
        $this->activityLogger->logAdministrative('incident_archived', "Incident {$incidentReport->document_number} archived.", [], ['incident_report_id' => $incidentReport->id, 'reason' => $validated['archive_reason']]);
        $incidentReport->delete();

        return redirect()
            ->route('admin.incident-reports.index')
            ->with('success', 'ICT incident report archived and the requester was notified.');
    }

    public function restore(int $incidentReport)
    {
        $report = IctIncidentReport::onlyTrashed()->findOrFail($incidentReport);
        $report->restore();
        $archiveReason = $report->archive_reason;
        $report->update(['archived_at' => null, 'archived_by' => null, 'archive_reason' => null]);
        $this->activityLogger->logAdministrative('incident_restored', "Incident {$report->document_number} restored.", ['reason' => $archiveReason], ['incident_report_id' => $report->id]);
        $this->notifyUser($report, 'Your ICT incident report was restored by MIS.');

        return redirect()->route('admin.incident-reports.index', ['archived' => 1])
            ->with('success', 'ICT incident report restored successfully.');
    }

    public function pdf(IctIncidentReport $incidentReport)
    {
        $cacheKey = 'incident_pdf_v2_' . $incidentReport->id . '_' . $incidentReport->updated_at->timestamp;
        $content = Cache::store('file')->remember($cacheKey, now()->addDays(30), function () use ($incidentReport) {
            $pdf = Pdf::loadView('pdf.incident-report', ['report' => $incidentReport])->setPaper('A4', 'portrait');
            $options = $pdf->getDomPDF()->getOptions();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('enable_font_subsetting', false);

            return $pdf->output();
        });
        $etag = '"' . sha1($content) . '"';
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ICT_Incident_Report_' . $incidentReport->document_number . '.pdf"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'ETag' => $etag,
        ];

        if (request()->header('If-None-Match') === $etag) return response('', 304, $headers);

        return response($content, 200, $headers);
    }

    private function notifyUser(IctIncidentReport $report, string $message, ?string $actionUrl = null): void
    {
        if (!$report->user_id) return;
        $user = User::find($report->user_id);
        Notification::create([
            'service_request_id' => $report->service_request_id ?? null,
            'user_id' => $report->user_id,
            'recipient_email' => $user?->email,
            'notification_type' => 'incident_report_update',
            'subject' => 'ICT incident record update',
            'message' => $message,
            'action_url' => $actionUrl ?: route('incident-reports.show', $report),
            'status' => 'pending',
        ]);
    }

    private function rules(?IctIncidentReport $incidentReport = null): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'reported_by' => ['required', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:255'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'max:50'],
            'received_by' => ['required', 'string', 'max:255'],
            'received_date' => ['nullable', 'date'],
            'received_time' => ['nullable', 'date_format:H:i'],
            'date_of_incident' => ['required', 'date'],
            'time_of_incident' => ['nullable'],
            'nature_of_incident' => ['nullable', 'array'],
            'nature_of_incident.*' => ['string'],
            'nature_others' => ['nullable', 'string', 'max:255'],
            'details_of_incident' => ['nullable', 'string'],
            'action_plan' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'noted_by_name' => ['nullable', 'string', 'max:255'],
            'noted_by_role' => ['nullable', 'string', 'max:255'],
            'updo_director_name' => ['nullable', 'string', 'max:255'],
            'acknowledged_by_name' => ['nullable', 'string', 'max:255'],
            'acknowledged_by_role' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:open,in_progress,resolved,closed'],
        ];
    }

    private function natureOptions(): array
    {
        return [
            'loss_of_sensitive_data' => 'Loss of sensitive and valuable data',
            'loss_of_valuable_hardware' => 'Loss of valuable hardware',
            'breach_of_information_security' => 'Breach of information security/protocols',
            'ict_hardware_software_theft' => 'ICT hardware/software theft, damage, or loss',
            'virus_worm_malware' => 'Virus/Trojan/Worm infection/DDoS',
            'inappropriate_use' => 'Inappropriate use of WVSU ICT facilities',
            'others_nature' => 'Others',
        ];
    }

    private function statusOptions(): array
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }

    private function requesterOptions(?int $currentUserId = null)
    {
        return User::query()
            ->where(function ($query) use ($currentUserId) {
                $query->where(function ($active) {
                    $active->where('is_active', true)
                        ->where('is_admin', false)
                        ->where('role', '!=', 'admin');
                });
                if ($currentUserId) $query->orWhere('users.id', $currentUserId);
            })
            ->orderBy('first_name')->orderBy('last_name')->get();
    }

    private function serviceRequestOptions(?int $currentRequestId = null)
    {
        return ServiceRequest::query()
            ->with('user:id,first_name,last_name')
            ->when($currentRequestId, fn ($query) => $query->where(function ($builder) use ($currentRequestId) {
                $builder->whereNull('archived_at')->orWhere('service_requests.id', $currentRequestId);
            }), fn ($query) => $query->whereNull('archived_at'))
            ->latest()
            ->limit(200)
            ->get(['id', 'control_number', 'user_id', 'requester_name', 'status']);
    }

    private function resolveServiceRequestLink(array $validated): array
    {
        if (empty($validated['service_request_id'])) return $validated;

        $serviceRequest = ServiceRequest::findOrFail($validated['service_request_id']);
        if (!empty($validated['user_id']) && $serviceRequest->user_id
            && (int) $validated['user_id'] !== (int) $serviceRequest->user_id) {
            throw ValidationException::withMessages([
                'service_request_id' => 'The linked service request belongs to a different record owner.',
            ]);
        }

        $validated['user_id'] = $serviceRequest->user_id ?: ($validated['user_id'] ?? null);

        return $validated;
    }

    private function misStaffOptions(?string $currentName = null)
    {
        $staff = Technician::query()
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Technician $technician) => trim($technician->first_name . ' ' . $technician->last_name))
            ->filter();

        if ($currentName && !$staff->contains($currentName)) {
            $staff->prepend($currentName);
        }

        return $staff->unique()->values();
    }

    private function logLinkedActivity(
        IctIncidentReport $report,
        string $action,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if ($report->serviceRequest) {
            $this->activityLogger->log($report->serviceRequest, $action, $description, $oldValues, $newValues);
            return;
        }

        $this->activityLogger->logAdministrative($action, $description, $oldValues, $newValues);
    }
}
