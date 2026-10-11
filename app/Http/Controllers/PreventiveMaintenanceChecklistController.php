<?php

namespace App\Http\Controllers;

use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AdminActionNotificationService;
use App\Support\PreventiveMaintenanceOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PreventiveMaintenanceChecklistController extends Controller
{
    public function __construct(private readonly AdminActionNotificationService $adminActionNotifications) {}

    public function index(Request $request)
    {
        $query = PreventiveMaintenanceChecklist::where('user_id', Auth::id());
        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'submitted' => (clone $statsQuery)->whereIn('status', PreventiveMaintenanceChecklist::storedStatusesFor('submitted'))->count(),
            'pending' => (clone $statsQuery)->whereIn('status', PreventiveMaintenanceChecklist::storedStatusesFor('pending'))->count(),
            'processing' => (clone $statsQuery)->whereIn('status', PreventiveMaintenanceChecklist::storedStatusesFor('processing'))->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
        ];

        if ($status = $request->query('status')) {
            $stage = PreventiveMaintenanceChecklist::LEGACY_STATUSES[$status] ?? $status;
            if (array_key_exists($stage, PreventiveMaintenanceChecklist::STATUS_OPTIONS)) {
                $query->whereIn('status', PreventiveMaintenanceChecklist::storedStatusesFor($stage));
            }
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('control_number', 'like', '%'.$search.'%')
                    ->orWhere('system_owner', 'like', '%'.$search.'%')
                    ->orWhere('office_department', 'like', '%'.$search.'%')
                    ->orWhere('property_number', 'like', '%'.$search.'%');
            });
        }

        $checklists = $query->with('serviceRequest:id,control_number,status')
            ->latest()
            ->paginate(5);
        $checklists->appends($request->query());

        return view('mis.pm-checklists.index', compact('checklists', 'stats'));
    }

    public function create(Request $request)
    {
        $linkedRequest = $request->filled('service_request_id')
            ? ServiceRequest::where('user_id', Auth::id())->findOrFail($request->integer('service_request_id'))
            : null;

        return view('mis.pm-checklists.request', [
            'linkedRequest' => $linkedRequest,
            'hardwareOptions' => PreventiveMaintenanceOptions::HARDWARE,
            'softwareOptions' => PreventiveMaintenanceOptions::SOFTWARE,
            'otherIctCheckOptions' => PreventiveMaintenanceOptions::OTHER_ICT,
            'infoSystemsOptions' => PreventiveMaintenanceOptions::INFORMATION_SYSTEMS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_request_id' => ['nullable', 'integer', 'exists:service_requests,id'],
            'property_number' => ['nullable', 'string', 'max:100'],
            'date_inspected' => ['required', 'date'],
            'system_owner' => ['required', 'string', 'max:255'],
            'office_department' => ['nullable', 'string', 'max:255'],
            'ict_type' => ['required', Rule::in(['computer', 'other_ict'])],
            'computer_type' => [Rule::requiredIf($request->input('ict_type') === 'computer'), 'nullable', Rule::in(['desktop', 'laptop'])],
            'other_ict_type' => [Rule::requiredIf($request->input('ict_type') === 'other_ict'), 'nullable', 'string', 'max:100'],
            'other_ict_specify' => ['nullable', 'string', 'max:255'],
            'hardware_checks' => ['nullable', 'array'],
            'hardware_checks.*' => [Rule::in(array_keys(PreventiveMaintenanceOptions::HARDWARE))],
            'software_checks' => ['nullable', 'array'],
            'software_checks.*' => [Rule::in(array_keys(PreventiveMaintenanceOptions::SOFTWARE))],
            'other_ict_checks' => ['nullable', 'array'],
            'other_ict_checks.*' => [Rule::in(array_keys(PreventiveMaintenanceOptions::OTHER_ICT))],
            'info_systems_checks' => ['nullable', 'array'],
            'info_systems_checks.*' => [Rule::in(array_keys(PreventiveMaintenanceOptions::INFORMATION_SYSTEMS))],
            'info_system_name' => ['nullable', 'string', 'max:255'],
            'findings' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! empty($validated['service_request_id'])) {
            $validated['service_request_id'] = ServiceRequest::where('user_id', Auth::id())
                ->findOrFail($validated['service_request_id'])->id;
        }

        if ($validated['ict_type'] === 'computer') {
            unset($validated['other_ict_type'], $validated['other_ict_specify'], $validated['other_ict_checks']);
        } else {
            unset($validated['computer_type'], $validated['hardware_checks'], $validated['software_checks']);
        }

        $user = Auth::user();
        $checklist = PreventiveMaintenanceChecklist::create($validated + [
            'user_id' => $user->id,
            'acknowledged_by_name' => $user->full_name,
            'acknowledged_by_role' => $user->designation,
            'status' => 'submitted',
        ]);

        $this->adminActionNotifications->notify(
            'Preventive maintenance checklist submitted',
            "{$checklist->control_number} from {$user->full_name} is ready for MIS review.",
            route('admin.pm-checklists.show', $checklist),
            $checklist->service_request_id
        );

        return redirect()->route('pm-checklists.show', $checklist)
            ->with('success', 'Preventive maintenance checklist submitted for MIS review.');
    }

    public function show(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);
        $pmChecklist->load('serviceRequest');

        return view('mis.pm-checklists.show', ['checklist' => $pmChecklist]);
    }

    public function edit(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);

        return redirect()->route('pm-checklists.show', $pmChecklist);
    }

    public function update(Request $request, PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);
        abort(403, 'Only MIS staff may edit the technical maintenance checklist.');
    }

    /** Generate PDF for printing */
    public function generatePdf(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);

        $pdf = Pdf::loadView('pdf.preventive-maintenance', ['checklist' => $pmChecklist])
            ->setPaper('A4', 'portrait');

        $response = $pdf->stream('PMC_'.$pmChecklist->control_number.'.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function ensureOwner(PreventiveMaintenanceChecklist $pmChecklist): void
    {
        $user = Auth::user();
        $isAdmin = $user instanceof User ? $user->isAdmin() : false;

        abort_unless($pmChecklist->user_id === Auth::id() || $isAdmin, 403);
    }
}
