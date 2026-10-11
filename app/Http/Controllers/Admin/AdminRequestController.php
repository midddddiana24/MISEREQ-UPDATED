<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Services\AdminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminRequestController extends Controller
{
    public function __construct(
        private readonly AdminActivityLogger $activityLogger
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:150',
            'search_id' => 'nullable|string|max:100',
            'search_name' => 'nullable|string|max:150',
            'search_status' => ['nullable', Rule::in(ServiceRequest::STATUSES)],
            'search_stage' => ['nullable', Rule::in(array_keys(ServiceRequest::ADMIN_STAGES))],
            'search_category' => 'nullable|string|max:100',
            'search_technician' => 'nullable|integer|exists:technicians,id',
            'search_queue' => 'nullable|in:attention,triage,unassigned,awaiting_requester_info,awaiting_confirmation,reopened,feedback,archived',
            'search_date_from' => 'nullable|date',
            'search_date_to' => 'nullable|date|after_or_equal:search_date_from',
        ]);

        $query = (($filters['search_queue'] ?? null) === 'archived'
            ? ServiceRequest::onlyTrashed()
            : ServiceRequest::query())
            ->with(['user', 'assignedTechnician.user'])
            ->latest('created_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('control_number', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['search_id'])) {
            $query->where('control_number', 'like', '%'.$filters['search_id'].'%');
        }

        if (! empty($filters['search_name'])) {
            $name = $filters['search_name'];
            $query->where(function ($builder) use ($name) {
                $builder->where('requester_name', 'like', "%{$name}%")
                    ->orWhereHas('user', function ($userQuery) use ($name) {
                        $userQuery->where('first_name', 'like', "%{$name}%")
                            ->orWhere('last_name', 'like', "%{$name}%")
                            ->orWhere('full_name', 'like', "%{$name}%");
                    });
            });
        }

        if (! empty($filters['search_status'])) {
            $query->where('status', $filters['search_status']);
        }

        if (! empty($filters['search_stage'])) {
            $query->whereIn('status', ServiceRequest::ADMIN_STAGES[$filters['search_stage']]);
        }

        if (! empty($filters['search_category'])) {
            $query->where('category', $filters['search_category']);
        }

        if (! empty($filters['search_technician'])) {
            $query->where('assigned_technician_id', $filters['search_technician']);
        }

        $this->applyQueueFilter($query, $filters['search_queue'] ?? null);

        if (! empty($filters['search_date_from'])) {
            $query->whereDate('created_at', '>=', $filters['search_date_from']);
        }

        if (! empty($filters['search_date_to'])) {
            $query->whereDate('created_at', '<=', $filters['search_date_to']);
        }

        $requests = $query->paginate(20)->appends($request->query());
        $technicians = Technician::with('user')->orderBy('status')->get();

        $stats = [
            'total' => ServiceRequest::count(),
            'triage' => ServiceRequest::whereIn('status', ['submitted', 'pending'])->count(),
            'unassigned' => ServiceRequest::open()->whereNull('assigned_technician_id')->count(),
            'processing' => ServiceRequest::where('status', 'processing')->count(),
            'attention' => ServiceRequest::whereIn('status', ['reopened', 'escalated'])->count(),
            'awaiting_confirmation' => ServiceRequest::where('status', 'awaiting_confirmation')->count(),
            'awaiting_requester_info' => ServiceRequest::where('status', 'awaiting_requester_info')->count(),
            'reopened' => ServiceRequest::where('status', 'reopened')->count(),
            'feedback' => ServiceRequest::whereNotNull('feedback_submitted_at')
                ->where(function ($builder) {
                    $builder->where('feedback_resolved', false)
                        ->orWhere('feedback_rating', '<=', 2);
                })
                ->where(function ($builder) {
                    $builder->whereNull('feedback_follow_up_status')
                        ->orWhere('feedback_follow_up_status', '!=', 'resolved');
                })->count(),
            'closed' => ServiceRequest::whereIn('status', ['completed', 'closed'])->count(),
            'archived' => ServiceRequest::onlyTrashed()->count(),
        ];

        return view('admin.requests', compact('requests', 'stats', 'filters', 'technicians'));
    }

    public function archive(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'archive_reason' => 'required|string|max:2000',
        ]);

        $this->activityLogger->log(
            $serviceRequest,
            'request_archived',
            'Request archived by an administrator.',
            [],
            ['reason' => $validated['archive_reason']]
        );

        if ($serviceRequest->user_id) {
            Notification::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => $serviceRequest->user_id,
                'recipient_email' => $serviceRequest->user?->email,
                'notification_type' => 'client_update',
                'subject' => 'Service request archived',
                'message' => $serviceRequest->control_number.': '.$validated['archive_reason'],
                'action_url' => route('requests.index'),
                'status' => 'pending',
            ]);
        }

        $serviceRequest->update([
            'archived_at' => now(),
            'archived_by' => Auth::id(),
            'archive_reason' => $validated['archive_reason'],
        ]);
        $serviceRequest->delete();

        return redirect()->route('admin.requests.index')->with('success', 'Request archived successfully.');
    }

    public function restore(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->trashed(), 422, 'This request is not archived.');
        $archiveReason = $serviceRequest->archive_reason;
        $serviceRequest->restore();
        $serviceRequest->update([
            'archived_at' => null,
            'archived_by' => null,
            'archive_reason' => null,
        ]);
        $this->activityLogger->log(
            $serviceRequest,
            'request_restored',
            'Archived request restored to the request queue.',
            ['archive_reason' => $archiveReason],
            []
        );

        if ($serviceRequest->user_id) {
            Notification::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => $serviceRequest->user_id,
                'recipient_email' => $serviceRequest->user?->email,
                'notification_type' => 'client_update',
                'subject' => 'Service request restored',
                'message' => $serviceRequest->control_number.' was restored to the MIS request records.',
                'action_url' => route('requests.show', $serviceRequest),
                'status' => 'pending',
            ]);
        }

        return redirect()->route('admin.requests.index', ['search_queue' => 'archived'])
            ->with('success', 'Request restored successfully.');
    }

    private function applyQueueFilter($query, ?string $queue): void
    {
        match ($queue) {
            'attention' => $query->whereIn('status', ['reopened', 'escalated']),
            'triage' => $query->whereIn('status', ['submitted', 'pending']),
            'unassigned' => $query->open()->whereNull('assigned_technician_id'),
            'awaiting_confirmation' => $query->where('status', 'awaiting_confirmation'),
            'awaiting_requester_info' => $query->where('status', 'awaiting_requester_info'),
            'reopened' => $query->where('status', 'reopened'),
            'escalated' => $query->where('status', 'escalated'),
            'feedback' => $query->whereNotNull('feedback_submitted_at')
                ->where(function ($builder) {
                    $builder->where('feedback_resolved', false)->orWhere('feedback_rating', '<=', 2);
                })
                ->where(function ($builder) {
                    $builder->whereNull('feedback_follow_up_status')
                        ->orWhere('feedback_follow_up_status', '!=', 'resolved');
                }),
            'archived' => null,
            default => null,
        };
    }
}
