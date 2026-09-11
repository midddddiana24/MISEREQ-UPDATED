<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Notification;
use App\Models\Technician;
use App\Models\Setting;
use App\Services\AdminActivityLogger;
use App\Services\EmailNotificationService;
use App\Services\FileUploadService;
use App\Services\ServiceRequestWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminServiceRequestController extends Controller
{
    public function __construct(
        private readonly EmailNotificationService $emailNotificationService,
        private readonly ServiceRequestWorkflow $workflow,
        private readonly AdminActivityLogger $activityLogger,
        private readonly FileUploadService $fileUploads
    ) {}

    /**
     * Show form to create service request on behalf of user
     */
    public function createOnBehalf()
    {
        return view('admin.service-requests.create-on-behalf', [
            'serviceOptions' => $this->clientServiceOptions(),
        ]);
    }

    /**
     * Store service request created by admin on behalf of user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeOnBehalf(Request $request)
    {
        $allowedServices = collect($this->clientServiceOptions())->flatMap(fn($options) => array_keys($options))->all();
        $validated = $request->validate([
            'requester_name' => 'required|string|max:255',
            'requester_designation' => 'nullable|string|max:255',
            'requester_office' => 'nullable|string|max:255',
            'unit_head_name' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'date_of_request' => 'nullable|date',
            'time_of_request' => 'nullable|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*' => ['string', Rule::in($allowedServices)],
            'description' => 'nullable|string|max:5000',
            'property_no' => 'nullable|string|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . $this->uploadMaxKilobytes(),
        ]);

        // Generate unique control number
        $controlNumber = 'SR-' . strtoupper(Str::random(4)) . '-' . date('YmdHis');

        $storedAttachments = collect($request->file('attachments', []))
            ->map(function ($file) {
                $stored = $this->fileUploads->upload($file, 'request-attachments');

                return [
                    'path' => $stored['path'],
                    'original_name' => $stored['original_filename'],
                    'mime_type' => $file->getMimeType(),
                    'size' => $stored['size'] ?? $file->getSize(),
                ];
            })
            ->values()
            ->all();

        // Create service request
        // Note: Observer will automatically create initial status log
        $authId = \Illuminate\Support\Facades\Auth::id();
        $serviceRequest = ServiceRequest::create([
            'control_number' => $controlNumber,
            'user_id' => null,
            'created_by' => $authId,
            'is_admin_created' => true,
            'requester_name' => $validated['requester_name'],
            'requester_designation' => $validated['requester_designation'] ?? null,
            'unit_head_name' => $validated['unit_head_name'] ?? null,
            'requester_office' => $validated['requester_office'] ?? null,
            'office' => $validated['office'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'services' => array_values($validated['services']),
            'description' => $validated['description'] ?? null,
            'property_no' => $validated['property_no'] ?? null,
            'ict_property_no' => $validated['property_no'] ?? null,
            'attachment' => $storedAttachments[0]['path'] ?? null,
            'status' => 'submitted',
            'category' => $this->inferCategory($validated['services']),
            'date_of_request' => $validated['date_of_request'] ?? now()->toDateString(),
            'time_of_request' => $validated['time_of_request'] ?? now()->format('H:i'),
        ]);

        foreach ($storedAttachments as $attachment) {
            $serviceRequest->attachments()->create($attachment + ['uploaded_by' => $authId]);
        }

        $this->activityLogger->log(
            $serviceRequest,
            'walk_in_request_created',
            'Account-free walk-in request encoded by MIS.',
            [],
            [
                'requester_name' => $serviceRequest->requester_name,
                'requester_office' => $serviceRequest->requester_office,
                'phone' => $serviceRequest->phone,
            ]
        );

        return redirect()->route('admin.service-requests.show', $serviceRequest->id)
            ->with('success', 'Walk-in request created successfully for ' . $serviceRequest->requester_name . '.');
    }

    /**
     * Show service request details
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load([
            'user',
            'assignedTechnician.user',
            'incidentReports',
            'preventiveMaintenanceChecklists',
        ]);

        // Get available technicians for assignment
        $technicians = \App\Models\Technician::where('is_active', true)->where('status', '!=', 'on_leave')
            ->with('user')
            ->withCount(['activeAssignments'])
            ->orderBy('status', 'asc')
            ->orderBy('current_workload')
            ->get();
        return view('admin.service-requests.show', compact('serviceRequest', 'technicians'));
    }

    /**
     * Show the official request details that an administrator may correct.
     */
    public function edit(ServiceRequest $serviceRequest)
    {
        return view('admin.service-requests.edit', [
            'serviceRequest' => $serviceRequest,
            'serviceOptions' => $this->serviceOptions(),
        ]);
    }

    /**
     * Update form data without changing ownership, assignment, or workflow status.
     */
    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $allowedServices = collect($this->serviceOptions())->flatMap(fn($options) => array_keys($options))->all();
        $validated = $request->validate([
            'requester_name' => 'required|string|max:255',
            'requester_designation' => 'nullable|string|max:255',
            'requester_office' => 'nullable|string|max:255',
            'unit_head_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'office' => 'nullable|string|max:255',
            'date_of_request' => 'nullable|date',
            'time_of_request' => 'nullable|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*' => ['string', Rule::in($allowedServices)],
            'property_no' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $trackedFields = [
            'requester_name',
            'requester_designation',
            'requester_office',
            'unit_head_name',
            'phone',
            'office',
            'date_of_request',
            'time_of_request',
            'services',
            'property_no',
            'description',
        ];
        $oldValues = $serviceRequest->only($trackedFields);

        $serviceRequest->update([
            'requester_name' => $validated['requester_name'],
            'requester_designation' => $validated['requester_designation'] ?? null,
            'requester_office' => $validated['requester_office'] ?? null,
            'unit_head_name' => $validated['unit_head_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'office' => $validated['office'] ?? null,
            'date_of_request' => $validated['date_of_request'] ?? null,
            'time_of_request' => $validated['time_of_request'] ?? null,
            'services' => array_values($validated['services']),
            'property_no' => $validated['property_no'] ?? null,
            'ict_property_no' => $validated['property_no'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $this->activityLogger->log(
            $serviceRequest,
            'request_details_updated',
            'Service request form details corrected by an administrator.',
            $oldValues,
            $serviceRequest->fresh()->only($trackedFields)
        );

        $this->notifyClient(
            $serviceRequest,
            'status_update',
            'Service request details updated',
            'MIS updated the form details for request ' . $serviceRequest->control_number . '.'
        );

        return redirect()
            ->route('admin.service-requests.show', $serviceRequest)
            ->with('success', 'Request details updated. The PDF preview now shows the changes.');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,pending,processing,awaiting_requester_info,awaiting_confirmation,completed,closed,reopened,escalated,cancelled,rejected',
            'remarks' => 'nullable|string|max:2000',
            'date_responded' => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        $additionalData = array_filter([
            'date_responded' => $validated['date_responded'] ?? null,
        ]);
        $this->workflow->transition(
            $serviceRequest,
            $validated['status'],
            $validated['remarks'] ?? null,
            $additionalData
        );

        return back()->with(
            'success',
            'Request status updated to ' . str($validated['status'])->replace('_', ' ')->title() . '.'
        );
    }

    public function markCompleted(Request $request, ServiceRequest $serviceRequest)
    {
        if (!$serviceRequest->assigned_technician_id) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'assigned_technician_id' => 'Assign a technician before completing this request.',
            ]);
        }

        if ($serviceRequest->status !== 'processing') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => 'This request must be In Progress before it can be completed.',
            ]);
        }

        $validated = $request->validate([
            'comments' => 'nullable|string|max:3000',
            'mis_head_signature' => 'nullable|string|max:255',
            'end_user_signature' => 'nullable|string|max:255',
            'acceptance_date' => 'nullable|date',
        ]);

        $this->workflow->transition(
            $serviceRequest,
            'awaiting_confirmation',
            $validated['comments'] ?: 'Technician work completed; awaiting requester confirmation.',
            [
                'comments' => $validated['comments'] ?? null,
                'mis_head_signature' => $validated['mis_head_signature'] ?? null,
                'end_user_signature' => $validated['end_user_signature'] ?? null,
                'acceptance_date' => !empty($validated['acceptance_date'])
                    ? \Carbon\Carbon::parse($validated['acceptance_date'])
                    : now(),
            ]
        );

        return back()->with('success', 'Work marked complete. The request is awaiting requester confirmation.');
    }

    public function reject(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate(['reason' => 'required|string|max:2000']);
        $user = Auth::user();
        $reason = 'Rejected by ' . ($user->full_name ?? $user->name) . ': ' . $validated['reason'];
        $this->workflow->transition($serviceRequest, 'rejected', $reason, [], false);
        if ($serviceRequest->user?->email) {
            dispatch(new \App\Jobs\SendRequestRejectedNotification($serviceRequest, $validated['reason']));
        }

        return back()->with('success', 'Request rejected.');
    }

    public function triage(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'assigned_technician_id' => ['nullable', Rule::exists('technicians', 'id')->where(fn($query) => $query->where('is_active', true)->where('status', '!=', 'on_leave'))],
            'technician_notes' => 'nullable|string|max:2000',
            'start_processing' => 'nullable|boolean',
        ]);

        $trackedFields = [
            'assigned_technician_id',
            'technician_notes',
        ];
        $oldValues = $serviceRequest->only($trackedFields);
        $oldTechnician = $serviceRequest->assignedTechnician;
        $assignmentChanged = (string) $serviceRequest->assigned_technician_id
            !== (string) ($validated['assigned_technician_id'] ?? null);

        $serviceRequest->update([
            'assigned_technician_id' => $validated['assigned_technician_id'] ?? null,
            'assigned_by' => Auth::id(),
            'assigned_date' => !empty($validated['assigned_technician_id']) ? now() : null,
            'technician_notes' => $validated['technician_notes'] ?? null,
        ]);

        $this->activityLogger->log(
            $serviceRequest,
            'request_triaged',
            'Request assignment and work instructions reviewed.',
            $oldValues,
            $serviceRequest->only($trackedFields)
        );

        $oldTechnician?->updateWorkload();
        $serviceRequest->load('assignedTechnician.user');
        $serviceRequest->assignedTechnician?->updateWorkload();

        // Technician coordination is handled directly inside the MIS office.
        // The assignment remains recorded for workload and accountability.

        if ($serviceRequest->status === 'submitted') {
            $this->workflow->transition($serviceRequest, 'pending', 'Admin review completed.');
            $serviceRequest->refresh();
        }

        if (
            $request->boolean('start_processing')
            && in_array($serviceRequest->status, ['pending', 'reopened', 'escalated'], true)
        ) {
            $this->workflow->transition($serviceRequest, 'processing', 'Request reviewed and work started.');
        }

        return back()->with('success', 'Request details saved successfully.');
    }

    public function close(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate(['reason' => 'nullable|string|max:2000']);
        $this->workflow->transition(
            $serviceRequest,
            'closed',
            $validated['reason'] ?? 'Resolution confirmed by administrator.'
        );

        return back()->with('success', 'Request closed successfully.');
    }

    public function reopen(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate(['reason' => 'required|string|max:2000']);
        $this->workflow->transition($serviceRequest, 'reopened', $validated['reason']);

        return back()->with('success', 'Request reopened and returned to the active queue.');
    }

    public function updateFeedbackFollowUp(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->feedback_submitted_at, 422, 'This request has no submitted feedback.');
        $validated = $request->validate([
            'feedback_follow_up_status' => 'required|in:reviewing,contacted,resolved',
            'feedback_follow_up_notes' => 'required|string|max:3000',
        ]);
        $oldValues = $serviceRequest->only(['feedback_follow_up_status', 'feedback_follow_up_notes']);
        $serviceRequest->update([
            ...$validated,
            'feedback_reviewed_by' => Auth::id(),
            'feedback_reviewed_at' => now(),
        ]);
        $this->activityLogger->log(
            $serviceRequest,
            'feedback_follow_up',
            'Requester feedback follow-up updated.',
            $oldValues,
            $validated
        );
        $this->notifyClient(
            $serviceRequest,
            'client_update',
            'MIS feedback follow-up updated',
            $validated['feedback_follow_up_notes']
        );

        return back()->with('success', 'Feedback follow-up updated and the requester was notified.');
    }

    public function askClarification(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless(in_array($serviceRequest->status, ServiceRequest::OPEN_STATUSES, true), 422, 'Only active requests can require clarification.');
        abort_if($serviceRequest->clarifications()->whereNull('answered_at')->exists(), 422, 'The requester already has an unanswered clarification request.');
        $validated = $request->validate(['question' => 'required|string|max:3000']);

        $clarification = $serviceRequest->clarifications()->create([
            'asked_by' => Auth::id(),
            'question' => $validated['question'],
        ]);

        if ($serviceRequest->status !== 'awaiting_requester_info') {
            $this->workflow->transition($serviceRequest, 'awaiting_requester_info', $validated['question']);
        }

        return back()->with('success', 'Clarification request sent to the requester.');
    }

    public function publishClientUpdate(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate(['client_update' => 'required|string|max:3000']);
        $serviceRequest->update([
            'client_update' => $validated['client_update'],
            'client_update_at' => now(),
            'client_update_by' => Auth::id(),
        ]);
        $serviceRequest->statusLogs()->create([
            'status' => $serviceRequest->status,
            'remarks' => 'MIS update: ' . $validated['client_update'],
            'updated_by' => Auth::id(),
        ]);
        $this->notifyClient($serviceRequest, 'client_update', 'New MIS progress update', $validated['client_update']);

        return back()->with('success', 'Client-visible update published.');
    }

    public function scheduleVisit(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless(in_array($serviceRequest->status, ServiceRequest::OPEN_STATUSES, true), 422, 'Only active requests can be scheduled.');
        $validated = $request->validate([
            'scheduled_visit_at' => 'required|date|after:now',
            'scheduled_location' => 'required|string|max:255',
        ]);
        if ($serviceRequest->assigned_technician_id) {
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_visit_at']);
            $conflict = ServiceRequest::where('assigned_technician_id', $serviceRequest->assigned_technician_id)
                ->whereKeyNot($serviceRequest->id)
                ->whereIn('status', ServiceRequest::OPEN_STATUSES)
                ->whereBetween('scheduled_visit_at', [
                    $scheduledAt->copy()->subMinutes(59),
                    $scheduledAt->copy()->addMinutes(59),
                ])->first();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'scheduled_visit_at' => 'This technician already has a nearby visit for ' . $conflict->control_number . '.',
                ]);
            }
        }
        $serviceRequest->update([
            ...$validated,
            'schedule_response' => 'pending',
            'schedule_response_note' => null,
            'schedule_responded_at' => null,
        ]);
        $this->notifyClient(
            $serviceRequest,
            'visit_scheduled',
            'MIS service visit scheduled',
            'A service visit is scheduled for ' . $serviceRequest->scheduled_visit_at->format('M d, Y g:i A')
                . ' at ' . $serviceRequest->scheduled_location . '.'
        );

        return back()->with('success', 'Service visit scheduled and requester notified.');
    }

    private function notifyClient(ServiceRequest $serviceRequest, string $type, string $subject, string $message): void
    {
        if (!$serviceRequest->user_id) {
            return;
        }

        Notification::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $serviceRequest->user_id,
            'recipient_email' => $serviceRequest->user?->email,
            'notification_type' => $type,
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    private function serviceOptions(): array
    {
        return [
            'Network / Telephone Services' => [
                'new_network_computer' => 'New network for computer',
                'new_network_printer' => 'New network for printer',
                'new_cabling' => 'New network cabling',
                'new_telephone_line' => 'New telephone line',
                'new_cabling_telephone' => 'Relocation of telephone',
                'move_new_location' => 'Move to a new location',
                'others_network' => 'Other network service',
            ],
            'ICT Equipment Services' => [
                'troubleshoot_pc' => 'Troubleshoot PC or laptop',
                'troubleshoot_printer' => 'Troubleshoot printer',
                'network_connection' => 'Telephone or internet connection',
                'software_installation' => 'Software installation',
                'hardware_install' => 'Hardware installation',
                'inspection_equipment' => 'Inspect delivered IT equipment',
                'others_ict' => 'Other ICT equipment service',
            ],
            'MIS Software / Application Services' => [
                'account_creation' => 'New user account',
                'password_reset' => 'Reset password',
                'software_problem' => 'Software problem',
                'data_repair' => 'Data repair',
                'copy_data' => 'Request for copy of data',
                'system_access' => 'Grant privilege or access',
                'others_mis' => 'Other MIS software service',
            ],
            'Web Administrator Services' => [
                'new_email_account' => 'New WVSU email account',
                'reset_email_password' => 'Reset email password',
                'logo_graphic' => 'Tarpaulin or graphic design',
                'photo_video' => 'Photo or video documentation',
                'website_update' => 'Website update or article posting',
                'multimedia_files' => 'Request for multimedia files',
                'web_other' => 'Other web service',
            ],
        ];
    }

    private function clientServiceOptions(): array
    {
        return [
            'Network / Telephone Services' => [
                'new_network_computer' => 'New network for computer',
                'new_network_printer' => 'New network for printer',
                'new_cabling' => 'New cabling for network',
                'new_telephone_line' => 'New telephone line',
                'new_cabling_telephone' => 'Cabling for telephone',
                'move_new_location' => 'Move to a new location',
                'others_network' => 'Other network service',
            ],
            'ICT Equipment Services' => [
                'troubleshoot_pc' => 'Troubleshoot PC or laptop',
                'troubleshoot_printer' => 'Troubleshoot printer',
                'network_connection' => 'Troubleshoot network or internet',
                'software_installation' => 'Software installation',
                'hardware_install' => 'Hardware installation',
                'others_ict' => 'Other ICT equipment service',
            ],
            'MIS Software / System Services' => [
                'account_creation' => 'Account or user creation',
                'password_reset' => 'Password reset or change',
                'system_access' => 'System access request',
                'others_mis' => 'Other MIS software service',
            ],
        ];
    }

    private function inferCategory(array $services): string
    {
        $serviceSet = collect($services);

        if ($serviceSet->contains(fn($service) => str_contains($service, 'network') || str_contains($service, 'telephone') || str_contains($service, 'cabling') || $service === 'move_new_location')) {
            return 'Network';
        }
        if ($serviceSet->contains(fn($service) => str_contains($service, 'troubleshoot') || str_contains($service, 'hardware') || str_contains($service, 'installation') || $service === 'inspection_equipment' || $service === 'others_ict')) {
            return 'ICT';
        }
        if ($serviceSet->contains(fn($service) => in_array($service, ['account_creation', 'password_reset', 'software_problem', 'data_repair', 'copy_data', 'system_access', 'others_mis'], true))) {
            return 'MIS';
        }

        return 'Web';
    }

    private function uploadMaxKilobytes(): int
    {
        return max(1024, min(20480, (int) Setting::get('max_upload_size', 10) * 1024));
    }

    /**
     * Generate and cache PDF content for fast delivery
     */
    private function generatePDF(ServiceRequest $serviceRequest): string
    {
        $cacheKey = 'pdf_v11_' . $serviceRequest->id . '_' . $serviceRequest->updated_at->timestamp;

        // Try to get from cache first - instant delivery on subsequent requests
        // Keep generated document bytes out of the database cache. Large PDFs
        // can exceed MySQL's max_allowed_packet during cache or session writes.
        $pdfContent = Cache::store('file')->remember($cacheKey, now()->addDays(30), function () use ($serviceRequest) {
            $html = \view('pdf.service-request', ['request' => $serviceRequest])->render();

            // Optimize DomPDF for speed
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            // Disable slow features
            $dompdf = $pdf->getDomPDF();
            $options = $dompdf->getOptions();
            $options->set('isPhpEnabled', false);
            $options->set('isRemoteEnabled', false);
            $options->set('logOutputFile', null);
            $options->set('enable_font_subsetting', false);

            return $pdf->output();
        });

        return $pdfContent;
    }

    /**
     * View PDF for a service request (admin version - no user ownership check)
     */
    public function viewPDF(int $id)
    {
        try {
            // Eager load relationships to prevent N+1 queries
            $serviceRequest = ServiceRequest::with(['assignedTechnician', 'user'])->findOrFail($id);

            // Get cached or newly generated PDF - cached PDFs are instant
            $pdfContent = $this->generatePDF($serviceRequest);

            $etag = '"' . sha1($pdfContent) . '"';
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="MIS-Request-' . $serviceRequest->control_number . '.pdf"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'ETag' => $etag,
            ];

            if (request()->header('If-None-Match') === $etag) {
                return response('', 304, $headers);
            }

            return response($pdfContent, 200, $headers);
        } catch (\Throwable $e) {
            $this->logPdfFailure('view', $e, $serviceRequest ?? null);
            return redirect()->back()->with('error', 'The PDF could not be generated. Please try again.');
        }
    }

    /**
     * Download PDF for a service request (admin version)
     */
    public function downloadPDF(int $id)
    {
        try {
            // Eager load relationships to prevent N+1 queries
            $serviceRequest = ServiceRequest::with(['assignedTechnician', 'user'])->findOrFail($id);

            // Get cached or newly generated PDF - cached PDFs are instant
            $pdfContent = $this->generatePDF($serviceRequest);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="MIS-Request-' . $serviceRequest->control_number . '.pdf"',
            ]);
        } catch (\Throwable $e) {
            $this->logPdfFailure('download', $e, $serviceRequest ?? null);
            return redirect()->back()->with('error', 'The PDF could not be downloaded. Please try again.');
        }
    }

    private function logPdfFailure(string $action, \Throwable $exception, ?ServiceRequest $serviceRequest): void
    {
        Log::error('Admin service-request PDF failed.', [
            'action' => $action,
            'service_request_id' => $serviceRequest?->id,
            'exception' => $exception::class,
            'code' => (string) $exception->getCode(),
            'message' => Str::limit(preg_replace('/\s+/', ' ', $exception->getMessage()), 500),
        ]);
    }
}
