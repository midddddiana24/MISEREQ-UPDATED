<?php

namespace App\Http\Controllers;

use App\Models\IctIncidentReport;
use App\Models\Notification;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use App\Services\EmailNotificationService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminDashboardController extends Controller implements HasMiddleware
{
    public function __construct(private readonly EmailNotificationService $emailNotifications) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('admin'),
        ];
    }

    public function index()
    {
        $recentRequests = ServiceRequest::with('user')->latest('updated_at')->limit(10)->get();

        $recentIncidentReports = IctIncidentReport::with('user')->latest('updated_at')->limit(10)->get();
        $recentPmChecklists = PreventiveMaintenanceChecklist::with('user')->latest('updated_at')->limit(10)->get();

        $serviceStatusCounts = ServiceRequest::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $incidentStatusCounts = IctIncidentReport::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $maintenanceStatusCounts = PreventiveMaintenanceChecklist::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $moduleStats = [
            'service_requests' => [
                'total' => $serviceStatusCounts->sum(),
                'new' => (int) (($serviceStatusCounts['submitted'] ?? 0) + ($serviceStatusCounts['pending'] ?? 0)),
                'ongoing' => (int) ($serviceStatusCounts['processing'] ?? 0),
                'waiting_reply' => (int) ($serviceStatusCounts['awaiting_requester_info'] ?? 0),
                'waiting_confirmation' => (int) ($serviceStatusCounts['awaiting_confirmation'] ?? 0),
                'completed' => (int) ($serviceStatusCounts['completed'] ?? 0),
                'closed' => (int) ($serviceStatusCounts['closed'] ?? 0),
            ],
            'incident_reports' => [
                'total' => $incidentStatusCounts->sum(),
                'open' => (int) ($incidentStatusCounts['open'] ?? 0),
                'ongoing' => (int) ($incidentStatusCounts['in_progress'] ?? 0),
                'resolved' => (int) ($incidentStatusCounts['resolved'] ?? 0),
                'closed' => (int) ($incidentStatusCounts['closed'] ?? 0),
            ],
            'maintenance' => [
                'total' => $maintenanceStatusCounts->sum(),
                'pending' => (int) ($maintenanceStatusCounts['pending'] ?? 0),
                'needs_action' => (int) ($maintenanceStatusCounts['needs_action'] ?? 0),
                'completed' => (int) ($maintenanceStatusCounts['completed'] ?? 0),
            ],
        ];

        $recentActivity = $recentRequests
            ->map(function ($request) {
                $request->dashboard_type = 'Service Request';
                $request->dashboard_reference = $request->control_number ?? '-';
                $request->dashboard_owner = $request->requester_name ?? ($request->user->full_name ?? '-');
                $request->dashboard_meta = $request->office ?? 'No office set';
                $request->dashboard_url = route('admin.service-requests.show', $request);

                return $request;
            })
            ->merge($recentIncidentReports->map(function ($report) {
                $report->dashboard_type = 'ICT Incident';
                $report->dashboard_reference = $report->document_number ?? '-';
                $report->dashboard_owner = $report->reported_by ?? ($report->user->full_name ?? '-');
                $report->dashboard_meta = $report->department ?: 'No department set';
                $report->dashboard_url = route('admin.incident-reports.show', $report);

                return $report;
            }))
            ->merge($recentPmChecklists->map(function ($checklist) {
                $checklist->dashboard_type = 'PM Checklist';
                $checklist->dashboard_reference = $checklist->control_number ?? '-';
                $checklist->dashboard_owner = $checklist->system_owner ?? ($checklist->user->full_name ?? '-');
                $checklist->dashboard_meta = $checklist->office_department ?: 'No department set';
                $checklist->dashboard_url = route('admin.pm-checklists.show', $checklist);

                return $checklist;
            }))
            ->sortByDesc('updated_at')
            ->values()
            ->take(12);

        return view('admin.dashboard', compact(
            'recentActivity',
            'moduleStats'
        ));
    }

    public function getNotifications()
    {
        $query = Notification::where('user_id', auth()->id())
            ->where('notification_type', 'admin_action_required');
        $totalUnread = (clone $query)->whereNull('read_at')->count();
        $notifications = $query
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => 'action-'.$notification->id,
                'title' => $notification->subject ?: 'Client action required',
                'message' => $notification->message,
                'type' => 'warning',
                'url' => $notification->action_url ?: route('admin.requests.index'),
                'read_url' => route('admin.notifications.read', $notification),
                'delete_url' => route('admin.notifications.delete', $notification),
                'time' => $notification->created_at->diffForHumans(),
                'read' => $notification->read_at !== null,
            ])
            ->values()
            ->all();

        return response()->json([
            'notifications' => $notifications,
            'total_unread' => $totalUnread,
        ]);
    }

    public function notificationsPage()
    {
        $notifications = Notification::query()
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('status', 'failed');
            })
            ->with('user:id,first_name,last_name,email')
            ->latest()
            ->paginate(25);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function retryNotification(Notification $notification)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $sent = $this->emailNotifications->retryNotification($notification);

        return back()->with($sent ? 'success' : 'error', $sent ? 'Notification sent successfully.' : 'Notification could not be sent. Review its delivery error.');
    }

    public function markNotificationRead(Notification $notification)
    {
        $this->ensureNotificationOwner($notification);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function deleteNotification(Notification $notification)
    {
        $this->ensureNotificationOwner($notification);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function clearNotifications()
    {
        $deleted = Notification::where('user_id', auth()->id())
            ->where('notification_type', 'admin_action_required')
            ->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    private function ensureNotificationOwner(Notification $notification): void
    {
        abort_unless(
            $notification->user_id === auth()->id()
            && $notification->notification_type === 'admin_action_required',
            403
        );
    }
}
