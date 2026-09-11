<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOperationsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'activity_search' => 'nullable|string|max:100',
            'activity_actor' => 'nullable|integer|exists:users,id',
            'activity_action' => 'nullable|string|max:100',
            'activity_module' => ['nullable', Rule::in(['requests', 'incidents', 'maintenance', 'accounts', 'system'])],
            'activity_from' => 'nullable|date',
            'activity_to' => 'nullable|date|after_or_equal:activity_from',
        ]);
        $dateFrom = Carbon::parse($filters['date_from'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $dateTo = Carbon::parse($filters['date_to'] ?? now()->toDateString())->endOfDay();

        $requests = ServiceRequest::with(['assignedTechnician.user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();
        $closedRequests = $requests->whereIn('status', ['completed', 'closed']);
        $metrics = [
            'total' => $requests->count(),
            'closed' => $closedRequests->count(),
            'open' => $requests->whereIn('status', ServiceRequest::OPEN_STATUSES)->count(),
            'average_resolution_hours' => round((float) $closedRequests->filter(fn ($item) => $item->closed_at || $item->date_completed)
                ->avg(function ($item) {
                    return $item->created_at->diffInMinutes($item->closed_at ?: $item->date_completed) / 60;
                }), 1),
            'average_feedback' => round((float) $requests->whereNotNull('feedback_rating')->avg('feedback_rating'), 1),
            'reopened' => $requests->where('status', 'reopened')->count(),
        ];

        $statusCounts = $requests->groupBy('status')->map->count();
        $assignmentCounts = collect([
            'Assigned' => $requests->whereNotNull('assigned_technician_id')->count(),
            'Unassigned' => $requests->whereNull('assigned_technician_id')->count(),
        ]);
        $technicians = Technician::with('user')
            ->withCount(['activeAssignments'])
            ->orderByDesc('active_assignments_count')
            ->get();
        $activityQuery = AdminActivityLog::with(['actor', 'serviceRequest'])
            ->when($filters['activity_search'] ?? null, function ($query, string $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhereHas('serviceRequest', fn ($requestQuery) => $requestQuery
                            ->where('control_number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['activity_actor'] ?? null, fn ($query, $actor) => $query->where('actor_id', $actor))
            ->when($filters['activity_action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['activity_from'] ?? null, fn ($query, $from) => $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['activity_to'] ?? null, fn ($query, $to) => $query->where('created_at', '<=', Carbon::parse($to)->endOfDay()));

        $this->applyActivityModule($activityQuery, $filters['activity_module'] ?? null);

        $recentActivity = $activityQuery->latest()->paginate(25, ['*'], 'activity_page')->withQueryString();
        $activityActors = User::query()
            ->whereIn('id', AdminActivityLog::query()->whereNotNull('actor_id')->select('actor_id'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'username']);
        $activityActions = AdminActivityLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.operations', compact(
            'filters',
            'dateFrom',
            'dateTo',
            'metrics',
            'statusCounts',
            'assignmentCounts',
            'technicians',
            'recentActivity',
            'activityActors',
            'activityActions'
        ));
    }

    private function applyActivityModule($query, ?string $module): void
    {
        match ($module) {
            'incidents' => $query->where('action', 'like', 'incident_%'),
            'maintenance' => $query->where('action', 'like', 'maintenance_%'),
            'accounts' => $query->where(function ($nested) {
                $nested->where('action', 'like', 'user_%')
                    ->orWhere('action', 'like', 'technician_%');
            }),
            'system' => $query->where('action', 'like', 'system_%'),
            'requests' => $query->where(function ($nested) {
                $nested->whereNotNull('service_request_id')
                    ->orWhere('action', 'like', 'request_%')
                    ->orWhere('action', 'like', 'status_%');
            })->where('action', 'not like', 'incident_%')
                ->where('action', 'not like', 'maintenance_%'),
            default => null,
        };
    }
}
