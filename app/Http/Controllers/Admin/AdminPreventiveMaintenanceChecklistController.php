<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AdminActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AdminPreventiveMaintenanceChecklistController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    public function index(Request $request)
    {
        $validated = $request->validate(['status' => ['nullable', 'in:pending,completed,needs_action']]);
        $statusFilter = $validated['status'] ?? null;
        $archived = request()->boolean('archived');
        $query = $archived ? PreventiveMaintenanceChecklist::onlyTrashed() : PreventiveMaintenanceChecklist::query();
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        $checklists = $query->with('user')->latest()->paginate(15)->withQueryString();
        $statusCounts = ($archived ? PreventiveMaintenanceChecklist::onlyTrashed() : PreventiveMaintenanceChecklist::query())
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.pm-checklists.index', [
            'checklists' => $checklists,
            'statusOptions' => $this->statusOptions(),
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

        return view('admin.pm-checklists.create', [
            'users' => $this->requesterOptions($prefillServiceRequest?->user_id),
            'serviceRequests' => $this->serviceRequestOptions($prefillServiceRequest?->id),
            'hardwareOptions' => $this->hardwareOptions(),
            'softwareOptions' => $this->softwareOptions(),
            'infoSystemsOptions' => $this->infoSystemsOptions(),
            'otherIctCheckOptions' => $this->otherIctCheckOptions(),
            'statusOptions' => $this->statusOptions(),
            'prefillServiceRequest' => $prefillServiceRequest,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->resolveRecordLinks($request->validate($this->rules()));

        $checklist = PreventiveMaintenanceChecklist::create($validated + [
            'status' => $validated['status'] ?? 'pending',
        ]);
        $this->notifyUser($checklist, 'A preventive maintenance record was created for your account.');
        $this->logLinkedActivity($checklist, 'maintenance_created', "Maintenance checklist {$checklist->control_number} was created.", [], ['pm_checklist_id' => $checklist->id, 'status' => $checklist->status]);

        return redirect()
            ->route('admin.pm-checklists.show', $checklist)
            ->with('success', 'Preventive maintenance checklist created successfully.');
    }

    public function show(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $pmChecklist->load('user', 'serviceRequest');

        return view('admin.pm-checklists.show', [
            'checklist' => $pmChecklist,
            'hardwareOptions' => $this->hardwareOptions(),
            'softwareOptions' => $this->softwareOptions(),
            'infoSystemsOptions' => $this->infoSystemsOptions(),
            'otherIctCheckOptions' => $this->otherIctCheckOptions(),
        ]);
    }

    public function edit(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $pmChecklist->load('user', 'serviceRequest');

        return view('admin.pm-checklists.edit', [
            'checklist' => $pmChecklist,
            'users' => $this->requesterOptions($pmChecklist->user_id),
            'serviceRequests' => $this->serviceRequestOptions($pmChecklist->service_request_id),
            'hardwareOptions' => $this->hardwareOptions(),
            'softwareOptions' => $this->softwareOptions(),
            'infoSystemsOptions' => $this->infoSystemsOptions(),
            'otherIctCheckOptions' => $this->otherIctCheckOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, PreventiveMaintenanceChecklist $pmChecklist)
    {
        $validated = $this->resolveRecordLinks($request->validate($this->rules($pmChecklist)));

        $oldValues = $pmChecklist->only(['user_id', 'status', 'findings', 'recommendations']);
        $pmChecklist->update($validated);
        $this->logLinkedActivity($pmChecklist, 'maintenance_updated', "Maintenance checklist {$pmChecklist->control_number} is now {$pmChecklist->status}.", $oldValues, $pmChecklist->only(array_keys($oldValues)));
        $statusLabel = str($pmChecklist->status)->replace('_', ' ')->title();
        $this->notifyUser($pmChecklist, "Your preventive maintenance record is now {$statusLabel}. MIS updated the checklist.");

        return redirect()
            ->route('admin.pm-checklists.show', $pmChecklist)
            ->with('success', 'Preventive maintenance checklist updated successfully.');
    }

    public function destroy(Request $request, PreventiveMaintenanceChecklist $pmChecklist)
    {
        $validated = $request->validate(['archive_reason' => ['required', 'string', 'max:1000']]);
        $this->notifyUser(
            $pmChecklist,
            'Your preventive maintenance record was archived by MIS.',
            route('pm-checklists.index')
        );
        $pmChecklist->update(['archived_at' => now(), 'archived_by' => Auth::id(), 'archive_reason' => $validated['archive_reason']]);
        $this->activityLogger->logAdministrative('maintenance_archived', "PM checklist {$pmChecklist->control_number} archived.", [], ['pm_checklist_id' => $pmChecklist->id, 'reason' => $validated['archive_reason']]);
        $pmChecklist->delete();

        return redirect()
            ->route('admin.pm-checklists.index')
            ->with('success', 'Preventive maintenance checklist archived and the requester was notified.');
    }

    public function restore(int $pmChecklist)
    {
        $checklist = PreventiveMaintenanceChecklist::onlyTrashed()->findOrFail($pmChecklist);
        $checklist->restore();
        $archiveReason = $checklist->archive_reason;
        $checklist->update(['archived_at' => null, 'archived_by' => null, 'archive_reason' => null]);
        $this->activityLogger->logAdministrative('maintenance_restored', "PM checklist {$checklist->control_number} restored.", ['reason' => $archiveReason], ['pm_checklist_id' => $checklist->id]);
        $this->notifyUser($checklist, 'Your preventive maintenance record was restored by MIS.');

        return redirect()->route('admin.pm-checklists.index', ['archived' => 1])
            ->with('success', 'Preventive maintenance checklist restored successfully.');
    }

    public function pdf(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $cacheKey = 'maintenance_pdf_v2_'.$pmChecklist->id.'_'.$pmChecklist->updated_at->timestamp;
        $content = Cache::store('file')->remember($cacheKey, now()->addDays(30), function () use ($pmChecklist) {
            $pdf = Pdf::loadView('pdf.preventive-maintenance', ['checklist' => $pmChecklist])->setPaper('A4', 'portrait');
            $options = $pdf->getDomPDF()->getOptions();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('enable_font_subsetting', false);

            return $pdf->output();
        });
        $etag = '"'.sha1($content).'"';
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="PMC_'.$pmChecklist->control_number.'.pdf"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'ETag' => $etag,
        ];

        if (request()->header('If-None-Match') === $etag) {
            return response('', 304, $headers);
        }

        return response($content, 200, $headers);
    }

    private function notifyUser(PreventiveMaintenanceChecklist $checklist, string $message, ?string $actionUrl = null): void
    {
        if (! $checklist->user_id) {
            return;
        }
        $user = User::find($checklist->user_id);
        Notification::create([
            'service_request_id' => $checklist->service_request_id ?? null,
            'user_id' => $checklist->user_id,
            'recipient_email' => $user?->email,
            'notification_type' => 'maintenance_update',
            'subject' => 'Preventive maintenance record update',
            'message' => $message,
            'action_url' => $actionUrl ?: route('pm-checklists.show', $checklist),
            'status' => 'pending',
        ]);
    }

    private function rules(?PreventiveMaintenanceChecklist $pmChecklist = null): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'property_number' => ['nullable', 'string', 'max:100'],
            'date_inspected' => ['required', 'date'],
            'system_owner' => ['required', 'string', 'max:255'],
            'office_department' => ['nullable', 'string', 'max:255'],
            'ict_type' => ['required', 'in:computer,other_ict'],
            'computer_type' => ['nullable', 'in:desktop,laptop'],
            'other_ict_type' => ['nullable', 'string', 'max:100'],
            'other_ict_specify' => ['nullable', 'string', 'max:255'],
            'other_ict_checks' => ['nullable', 'array'],
            'other_ict_checks.*' => ['string'],
            'hardware_checks' => ['nullable', 'array'],
            'hardware_checks.*' => ['string'],
            'software_checks' => ['nullable', 'array'],
            'software_checks.*' => ['string'],
            'info_systems_checks' => ['nullable', 'array'],
            'info_systems_checks.*' => ['string'],
            'info_system_name' => ['nullable', 'string', 'max:255'],
            'findings' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'inspected_by_name' => ['nullable', 'string', 'max:255'],
            'inspected_by_role' => ['nullable', 'string', 'max:255'],
            'noted_by_name' => ['nullable', 'string', 'max:255'],
            'noted_by_role' => ['nullable', 'string', 'max:255'],
            'acknowledged_by_name' => ['nullable', 'string', 'max:255'],
            'acknowledged_by_role' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,completed,needs_action'],
        ];
    }

    private function hardwareOptions(): array
    {
        return [
            'complete_components' => 'Complete Components & Peripherals',
            'remove_dust' => 'Remove Dust Build-up',
            'check_power' => 'Check Power/Overheating Issues',
            'check_network_connectivity' => 'Check/Optimize Network Connectivity',
        ];
    }

    private function softwareOptions(): array
    {
        return [
            'update_drivers' => 'Up-to-date Drivers',
            'remove_unwanted_registry' => 'Remove Unwanted Registry Files',
            'defragment_disk' => 'Defragment Disk Drive',
            'complete_productivity_tools' => 'Complete Productivity Tools',
            'scan_for_viruses' => 'Scan for Viruses',
            'optimize_startup' => 'Optimize Start-up',
            'check_os_licenses' => 'Check OS and Other Licenses',
            'delete_trash_junk' => 'Delete Trash/Junk Files',
            'update_security_tools' => 'Up-to-date Security Tools',
        ];
    }

    private function infoSystemsOptions(): array
    {
        return [
            'connectivity' => 'Connectivity',
            'software_maintenance' => 'Software Maintenance',
            'database_backup' => 'Database Backup',
            'application_backup' => 'Application Backup',
        ];
    }

    private function otherIctCheckOptions(): array
    {
        return [
            'software' => 'Software',
            'hardware' => 'Hardware',
            'network' => 'Network',
            'communications' => 'Communications',
        ];
    }

    private function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'needs_action' => 'Needs Action',
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
                if ($currentUserId) {
                    $query->orWhere('users.id', $currentUserId);
                }
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

    private function resolveRecordLinks(array $validated): array
    {
        if (empty($validated['service_request_id'])) {
            return $validated;
        }

        $serviceRequest = ServiceRequest::findOrFail($validated['service_request_id']);
        if (! empty($validated['user_id']) && $serviceRequest->user_id
            && (int) $validated['user_id'] !== (int) $serviceRequest->user_id) {
            throw ValidationException::withMessages([
                'service_request_id' => 'The linked service request belongs to a different record owner.',
            ]);
        }

        $validated['user_id'] = $serviceRequest->user_id ?: ($validated['user_id'] ?? null);

        return $validated;
    }

    private function logLinkedActivity(
        PreventiveMaintenanceChecklist $checklist,
        string $action,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        if ($checklist->serviceRequest) {
            $this->activityLogger->log($checklist->serviceRequest, $action, $description, $oldValues, $newValues);

            return;
        }

        $this->activityLogger->logAdministrative($action, $description, $oldValues, $newValues);
    }
}
