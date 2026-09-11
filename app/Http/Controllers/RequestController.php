<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyAdminNewRequest;
use App\Jobs\SendRequestCreatedNotification;
use App\Models\ClarificationAttachment;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestAttachment;
use App\Models\ServiceRequestClarification;
use App\Models\ServiceRequestDraft;
use App\Models\Setting;
use App\Models\User;
use App\Services\AdminActionNotificationService;
use App\Services\EmailNotificationService;
use App\Services\FileUploadService;
use App\Services\ServiceRequestWorkflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    public function __construct(
        private readonly EmailNotificationService $emailNotificationService,
        private readonly ServiceRequestWorkflow $workflow,
        private readonly AdminActionNotificationService $adminActionNotifications,
        private readonly FileUploadService $fileUploads
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ServiceRequest::where('user_id', $user->id);

        $status = $request->query('status', $request->query('filter'));
        if (! empty($status) && $status !== 'all') {
            if ($status === 'active') {
                $query->whereIn('status', ServiceRequest::OPEN_STATUSES);
            } elseif ($status === 'closed') {
                $query->whereIn('status', ['completed', 'closed']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('control_number', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%');
            });
        }

        $requests = $query
            ->withCount(['incidentReports', 'preventiveMaintenanceChecklists'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        $requests->appends($request->query());

        return view('user.requests.history-new', compact('requests'));
    }

    public function create()
    {
        return view('user.request-create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', ServiceRequest::class);
        $validated = $request->validate($this->rules());

        $user = Auth::user();

        // Generate control number: SR-YYYYMMDDHHMMSS-userId
        $controlNumber = 'SR-'.now()->format('YmdHis').'-'.$user->id;

        $storedAttachments = $this->storeUploadedAttachments($request);
        $attachmentPath = $storedAttachments[0]['path'] ?? null;

        // Get validated values with safe null coalescing
        $timeRequest = $validated['time_request'] ?? null;
        $dateRequest = $validated['date_request'] ?? null;

        // Create service request
        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'control_number' => $controlNumber,
            'requester_name' => $validated['requester_name'] ?? $user->full_name,
            'requester_designation' => $validated['designation'] ?? $user->designation,
            'requester_office' => $validated['requester_office'] ?? $user->office,
            'office' => $validated['office'] ?? $user->office,
            'phone' => $validated['phone_local'] ?? $user->phone ?? null,
            'unit_head_name' => $validated['unit_head_name'] ?? null,
            'services' => $validated['services'] ?? [],
            'description' => $validated['description'] ?? null,
            'property_no' => $validated['property_no'] ?? null,
            'category' => $validated['category'] ?? $this->inferCategory($validated['services'] ?? []),
            'attachment' => $attachmentPath,
            'status' => 'pending', // Status is "pending" when user creates it
            'date_of_request' => $dateRequest ? Carbon::parse($dateRequest) : now(),
            'time_of_request' => $timeRequest ? Carbon::parse(($dateRequest ?? now()->toDateString()).' '.$timeRequest) : now(),
        ]);

        foreach ($storedAttachments as $attachment) {
            $serviceRequest->attachments()->create($attachment + ['uploaded_by' => $user->id]);
        }

        // Log the activity
        Log::info('Service request created', [
            'control_number' => $controlNumber,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Queue email notifications (non-blocking)
        dispatch(new SendRequestCreatedNotification($serviceRequest));
        dispatch(new NotifyAdminNewRequest($serviceRequest, $user));

        $this->notifyAdmins(
            $serviceRequest,
            'New service request submitted',
            ($serviceRequest->requester_name ?: 'A requester').' submitted a request for MIS review.'
        );

        ServiceRequestDraft::where('user_id', $user->id)
            ->where('form_type', 'service_request')
            ->delete();

        return redirect()->route('dashboard')
            ->with('success', "Service request '{$controlNumber}' submitted successfully! The admin team will review it shortly.");
    }

    public function show(ServiceRequest $request)
    {
        $this->authorize('view', $request);

        $request->load([
            'messages' => fn ($query) => $query->latest(),
            'incidentReports' => fn ($query) => $query->latest(),
            'preventiveMaintenanceChecklists' => fn ($query) => $query->latest(),
            'statusLogs' => fn ($query) => $query->with('updatedBy')->oldest(),
            'activityLogs' => fn ($query) => $query
                ->whereIn('action', ['incident_created', 'incident_updated', 'maintenance_created', 'maintenance_updated'])
                ->with('actor')
                ->oldest(),
            'assignedTechnician.user',
            'attachments',
            'clarifications.askedBy',
            'clarifications.attachments',
            'clientUpdateAuthor',
        ]);

        return view('user.request-details-new', compact('request'));
    }

    public function edit(ServiceRequest $request)
    {
        $this->authorize('edit', $request);

        return view('user.request-create', ['requestModel' => $request]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorize('edit', $serviceRequest);

        $validated = $request->validate($this->rules(false));

        $storedAttachments = $this->storeUploadedAttachments($request);
        $attachmentPath = $serviceRequest->attachment ?: ($storedAttachments[0]['path'] ?? null);

        $timeRequest = $validated['time_request'] ?? null;
        $dateRequest = $validated['date_request'] ?? optional($serviceRequest->date_of_request)->format('Y-m-d');

        $serviceRequest->update([
            'requester_name' => $validated['requester_name'] ?? $serviceRequest->requester_name,
            'requester_designation' => $validated['designation'] ?? $serviceRequest->requester_designation,
            'requester_office' => $validated['requester_office'] ?? $serviceRequest->requester_office,
            'office' => $validated['office'] ?? $serviceRequest->office,
            'phone' => $validated['phone_local'] ?? $serviceRequest->phone,
            'unit_head_name' => $validated['unit_head_name'] ?? $serviceRequest->unit_head_name,
            'services' => $validated['services'] ?? [],
            'description' => $validated['description'] ?? null,
            'property_no' => $validated['property_no'] ?? null,
            'category' => $validated['category'] ?? $this->inferCategory($validated['services'] ?? []),
            'attachment' => $attachmentPath,
            'date_of_request' => $dateRequest ? Carbon::parse($dateRequest) : $serviceRequest->date_of_request,
            'time_of_request' => $timeRequest ? Carbon::parse(($dateRequest ?? now()->toDateString()).' '.$timeRequest) : $serviceRequest->time_of_request,
        ]);

        foreach ($storedAttachments as $attachment) {
            $serviceRequest->attachments()->create($attachment + ['uploaded_by' => Auth::id()]);
        }

        $this->notifyAdmins(
            $serviceRequest,
            'Service request edited',
            'The requester updated the request details or attachments.'
        );

        return redirect()
            ->route('requests.show', $serviceRequest)
            ->with('success', 'Service request updated successfully.');
    }

    public function destroy(Request $httpRequest, ServiceRequest $request)
    {
        $this->authorize('cancel', $request);
        $validated = $httpRequest->validate(['cancellation_reason' => 'required|string|max:2000']);
        $this->workflow->transition(
            $request,
            'cancelled',
            $validated['cancellation_reason'],
            ['cancellation_reason' => $validated['cancellation_reason']]
        );
        $this->notifyAdmins($request, 'Request cancelled', $validated['cancellation_reason']);

        return redirect()
            ->route('requests.show', $request)
            ->with('success', 'Service request cancelled successfully.');
    }

    public function submitFeedback(Request $httpRequest, ServiceRequest $request)
    {
        $this->authorize('submitFeedback', $request);

        $validated = $httpRequest->validate([
            'feedback_rating' => 'required|integer|min:1|max:5',
            'feedback_resolved' => 'required|boolean',
            'feedback_comment' => 'nullable|string|max:2000',
        ]);

        if (! $validated['feedback_resolved']) {
            abort_unless($request->canBeReopenedByUser(), 403, 'The reopening period for this request has ended. Please submit a new request.');
        }

        $request->update([
            ...$validated,
            'feedback_submitted_at' => now(),
            'feedback_follow_up_status' => $validated['feedback_resolved'] ? 'resolved' : 'reviewing',
        ]);

        if (! $validated['feedback_resolved'] || $request->status !== 'closed') {
            $this->workflow->transition(
                $request,
                $validated['feedback_resolved'] ? 'closed' : 'reopened',
                $validated['feedback_resolved']
                    ? 'Requester confirmed that the concern was resolved.'
                    : ($validated['feedback_comment'] ?: 'Requester reported that the concern is not yet resolved.')
            );
        }

        if (! $validated['feedback_resolved'] || $validated['feedback_rating'] <= 2) {
            $this->notifyAdmins(
                $request,
                'Feedback requires follow-up',
                $validated['feedback_comment'] ?: 'The requester reported that the concern is not fully resolved.'
            );
        }

        return back()->with(
            'success',
            $validated['feedback_resolved']
                ? 'Thank you. Your feedback was recorded and the request was closed.'
                : 'Thank you. The request was reopened for follow-up.'
        );
    }

    public function confirmResolution(Request $httpRequest, ServiceRequest $request)
    {
        $this->authorize('confirmWorkCompletion', $request);
        $validated = $httpRequest->validate(['comment' => 'nullable|string|max:2000']);
        $this->workflow->transition(
            $request,
            'closed',
            $validated['comment'] ?? 'Requester confirmed resolution.'
        );
        $this->notifyAdmins(
            $request,
            'Resolution confirmed',
            $validated['comment'] ?: 'The requester confirmed the concern is resolved.'
        );

        return back()->with('success', 'Resolution confirmed. This request is now closed.');
    }

    public function reopen(Request $httpRequest, ServiceRequest $request)
    {
        $this->authorize('reopen', $request);
        $validated = $httpRequest->validate(['reason' => 'required|string|max:2000']);
        $this->workflow->transition($request, 'reopened', $validated['reason']);
        $this->notifyAdmins($request, 'Request reopened', $validated['reason']);

        return back()->with('success', 'Your request was reopened for additional work.');
    }

    public function answerClarification(Request $httpRequest, ServiceRequest $request, ServiceRequestClarification $clarification)
    {
        $this->authorize('answerClarification', $request);
        abort_unless($request->status === 'awaiting_requester_info', 422, 'This request is no longer waiting for information.');
        $validated = $httpRequest->validate([
            'answer' => 'required|string|max:3000',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:'.$this->uploadMaxKilobytes(),
        ]);

        $clarification->update([
            'answer' => $validated['answer'],
            'answered_by' => Auth::id(),
            'answered_at' => now(),
        ]);
        foreach ($httpRequest->file('attachments', []) as $file) {
            $clarification->attachments()->create([
                'uploaded_by' => Auth::id(),
                ...collect($this->fileUploads->upload($file, 'clarification-attachments', [
                    'service_request_id' => $request->id,
                    'clarification_id' => $clarification->id,
                ]))->only(['path', 'original_name', 'mime_type', 'size'])->all(),
            ]);
        }

        $targetStatus = $request->status_before_clarification;
        if (! in_array($targetStatus, $this->workflow->allowedTransitions($request), true)) {
            $targetStatus = $request->assigned_technician_id ? 'processing' : 'pending';
        }
        $this->workflow->transition(
            $request,
            $targetStatus,
            'Requester supplied the requested information.',
            ['status_before_clarification' => null]
        );
        $this->notifyAdmins($request, 'Requester supplied clarification', $validated['answer']);

        return back()->with('success', 'Your response was sent to MIS.');
    }

    public function respondToSchedule(Request $httpRequest, ServiceRequest $request)
    {
        $this->authorize('respondToSchedule', $request);
        $validated = $httpRequest->validate([
            'schedule_response' => 'required|in:accepted,reschedule_requested',
            'schedule_response_note' => 'nullable|required_if:schedule_response,reschedule_requested|string|max:2000',
        ]);
        $request->update([
            ...$validated,
            'schedule_responded_at' => now(),
        ]);
        $request->statusLogs()->create([
            'status' => $request->status,
            'remarks' => $validated['schedule_response'] === 'accepted'
                ? 'Requester accepted the scheduled MIS visit.'
                : 'Requester requested a schedule change: '.$validated['schedule_response_note'],
            'updated_by' => Auth::id(),
        ]);
        $this->notifyAdmins(
            $request,
            $validated['schedule_response'] === 'accepted' ? 'Schedule accepted' : 'Rescheduling requested',
            $validated['schedule_response_note'] ?: 'The requester accepted the scheduled MIS visit.'
        );

        return back()->with('success', $validated['schedule_response'] === 'accepted'
            ? 'Scheduled visit confirmed.'
            : 'Your rescheduling request was sent to MIS.');
    }

    public function downloadClarificationAttachment(ServiceRequest $request, ClarificationAttachment $attachment)
    {
        $clarification = $attachment->clarification;
        abort_unless($clarification && $clarification->service_request_id === $request->id, 404);
        $this->authorize('accessClarificationAttachment', $request);
        abort_unless(Storage::disk('secure')->exists($attachment->path), 404);

        return $this->fileUploads->downloadByPath($attachment->path, $attachment->original_name);
    }

    public function checkDuplicates(Request $httpRequest)
    {
        $validated = $httpRequest->validate([
            'property_no' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);
        $propertyNo = trim((string) ($validated['property_no'] ?? ''));
        $keywords = collect(preg_split('/\s+/', strtolower((string) ($validated['description'] ?? ''))))
            ->filter(fn ($word) => strlen($word) >= 5)
            ->unique()
            ->take(3);

        if ($propertyNo === '' && $keywords->isEmpty()) {
            return response()->json(['matches' => []]);
        }

        $matches = ServiceRequest::where('user_id', Auth::id())
            ->open()
            ->where(function ($query) use ($propertyNo, $keywords) {
                if ($propertyNo !== '') {
                    $query->where('property_no', $propertyNo);
                }
                foreach ($keywords as $keyword) {
                    $propertyNo !== '' ? $query->orWhere('description', 'like', "%{$keyword}%") : $query->where('description', 'like', "%{$keyword}%");
                }
            })
            ->latest()
            ->limit(3)
            ->get(['id', 'control_number', 'status', 'description'])
            ->map(fn ($match) => [
                'control_number' => $match->control_number,
                'status' => $match->status_label,
                'url' => route('requests.show', $match),
            ]);

        return response()->json(['matches' => $matches]);
    }

    private function notifyAdmins(ServiceRequest $request, string $subject, string $message): void
    {
        $this->adminActionNotifications->notify(
            $subject,
            $request->control_number.': '.$message,
            route('admin.service-requests.show', $request),
            $request->id
        );
    }

    public function downloadAttachment(ServiceRequest $request)
    {
        $this->authorize('downloadAttachment', $request);
        abort_unless(Storage::disk('secure')->exists($request->attachment), 404);

        return $this->fileUploads->downloadByPath($request->attachment, basename($request->attachment));
    }

    public function downloadUploadedAttachment(ServiceRequest $request, ServiceRequestAttachment $attachment)
    {
        $this->authorize('downloadUploadedAttachment', $request);
        abort_unless($attachment->service_request_id === $request->id, 404);
        abort_unless(Storage::disk('secure')->exists($attachment->path), 404);

        return $this->fileUploads->downloadByPath($attachment->path, $attachment->original_name);
    }

    public function destroyUploadedAttachment(ServiceRequest $request, ServiceRequestAttachment $attachment)
    {
        $this->authorize('destroyUploadedAttachment', $request);
        abort_unless($attachment->service_request_id === $request->id, 404);

        $this->fileUploads->deleteByPath($attachment->path);
        if ($request->attachment === $attachment->path) {
            $request->update(['attachment' => null]);
        }
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }

    private function ensureOwner(ServiceRequest $request): void
    {
        abort_unless($request->user_id === Auth::id(), 403);
    }

    private function rules(bool $attachmentRequired = true): array
    {
        return [
            'requester_name' => 'nullable|string|max:255',
            'unit_head_name' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone_local' => 'nullable|string|max:20',
            'requester_office' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'date_request' => 'nullable|date',
            'time_request' => 'nullable',
            'services' => 'required|array|min:1',
            'services.*' => 'string',
            'description' => 'nullable|string|max:5000',
            'property_no' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'attachment' => ($attachmentRequired ? 'nullable' : 'nullable').'|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:'.$this->uploadMaxKilobytes(),
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:'.$this->uploadMaxKilobytes(),
        ];
    }

    private function uploadMaxKilobytes(): int
    {
        return max(1024, min(20480, (int) Setting::get('max_upload_size', 10) * 1024));
    }

    private function storeUploadedAttachments(Request $request): array
    {
        $files = $request->file('attachments', []);
        $files = is_array($files) ? $files : [$files];

        // Continue accepting the original single-file field for older clients.
        if ($request->hasFile('attachment')) {
            $files[] = $request->file('attachment');
        }

        return collect($files)
            ->filter()
            ->map(function ($file) {
                return collect($this->fileUploads->upload($file, 'request-attachments'))
                    ->only(['path', 'original_name', 'mime_type', 'size'])
                    ->all();
            })
            ->values()
            ->all();
    }

    private function inferCategory(array $services): string
    {
        $serviceSet = collect($services);

        if ($serviceSet->contains(fn ($service) => str_starts_with($service, 'new_') || str_contains($service, 'network') || str_contains($service, 'telephone') || str_contains($service, 'cabling') || str_contains($service, 'location'))) {
            return 'Network';
        }

        if ($serviceSet->contains(fn ($service) => str_contains($service, 'troubleshoot') || str_contains($service, 'hardware') || str_contains($service, 'software_installation') || str_contains($service, 'others_ict'))) {
            return 'ICT';
        }

        if ($serviceSet->contains(fn ($service) => str_contains($service, 'account') || str_contains($service, 'password') || str_contains($service, 'system_access') || str_contains($service, 'others_mis'))) {
            return 'MIS';
        }

        return 'General';
    }
}
