<?php

namespace App\Http\Controllers;

use App\Models\IctIncidentReport;
use App\Models\LoginAttempt;
use App\Models\Message;
use App\Models\Notification;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserDashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function index()
    {
        $user = Auth::user();

        // Get user's requests
        $recentRequests = ServiceRequest::where('user_id', $user->id)
            ->with('assignedTechnician.user')
            ->withCount(['incidentReports', 'preventiveMaintenanceChecklists'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $attentionRequests = ServiceRequest::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereIn('status', ['awaiting_requester_info', 'awaiting_confirmation', 'reopened', 'escalated'])
                    ->orWhere(function ($scheduleQuery) {
                        $scheduleQuery->whereIn('status', ServiceRequest::OPEN_STATUSES)
                            ->whereNotNull('scheduled_visit_at')
                            ->where(function ($responseQuery) {
                                $responseQuery->whereNull('schedule_response')->orWhere('schedule_response', 'pending');
                            });
                    });
            })
            ->orderByRaw("CASE status WHEN 'awaiting_requester_info' THEN 1 WHEN 'awaiting_confirmation' THEN 2 WHEN 'escalated' THEN 3 ELSE 4 END")
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // Calculate statistics
        $stats = [
            'total' => ServiceRequest::where('user_id', $user->id)->count(),
            'active' => ServiceRequest::where('user_id', $user->id)->open()->count(),
            'awaiting_confirmation' => ServiceRequest::where('user_id', $user->id)
                ->where('status', 'awaiting_confirmation')->count(),
            'closed' => ServiceRequest::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'closed'])->count(),
            'incident_reports' => IctIncidentReport::where('user_id', $user->id)->count(),
            'pm_checklists' => PreventiveMaintenanceChecklist::where('user_id', $user->id)->count(),
        ];

        $incidentReports = IctIncidentReport::where('user_id', $user->id)
            ->with('serviceRequest:id,control_number,status')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $pmChecklists = PreventiveMaintenanceChecklist::where('user_id', $user->id)
            ->with('serviceRequest:id,control_number,status')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $attentionMaintenance = PreventiveMaintenanceChecklist::where('user_id', $user->id)
            ->where('status', 'needs_action')
            ->latest('updated_at')
            ->limit(3)
            ->get();

        return view('user.dashboard', compact(
            'recentRequests',
            'attentionRequests',
            'stats',
            'incidentReports',
            'pmChecklists',
            'attentionMaintenance'
        ));
    }

    public function create()
    {
        return view('user.request-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category' => 'required|string',
            'services' => 'nullable|array',
        ]);

        // Get services from form (array of selected service codes)
        $services = $validated['services'] ?? [];

        // Initialize all boolean columns as false (0)
        $serviceData = [
            // Network Services
            'network_new_computer' => in_array('new_network_computer', $services) ? 1 : 0,
            'network_new_printer' => in_array('new_network_printer', $services) ? 1 : 0,
            'network_data_request' => in_array('new_cabling', $services) ? 1 : 0,
            'network_new_telephone' => in_array('new_telephone_line', $services) ? 1 : 0,
            'network_change_telephone' => in_array('relocation_telephone', $services) ? 1 : 0,
            'network_move_location' => in_array('move_new_location', $services) ? 1 : 0,
            'network_others' => in_array('network_other', $services) ? 'Others Selected' : null,

            // ICT Services
            'ict_troubleshoot_pc' => in_array('troubleshoot_pc', $services) ? 1 : 0,
            'ict_troubleshoot_printer' => in_array('troubleshoot_printer', $services) ? 1 : 0,
            'ict_troubleshoot_telephone' => in_array('network_connection', $services) ? 1 : 0,
            'ict_software_install' => in_array('software_installation', $services) ? 1 : 0,
            'ict_hardware_install' => in_array('hardware_installation', $services) ? 1 : 0,
            'ict_procurement' => in_array('inspection_equipment', $services) ? 1 : 0,
            'ict_others' => in_array('ict_other', $services) ? 'Others Selected' : null,

            // MIS Services
            'mis_new_user' => in_array('new_user_account', $services) ? 1 : 0,
            'mis_reset_password' => in_array('reset_password', $services) ? 1 : 0,
            'mis_software_problem' => in_array('software_problem', $services) ? 1 : 0,
            'mis_data_repair' => in_array('data_repair', $services) ? 1 : 0,
            'mis_request_copy_data' => in_array('copy_data', $services) ? 1 : 0,
            'mis_grant_privilege' => in_array('grant_privilege', $services) ? 1 : 0,
            'mis_others' => in_array('mis_other', $services) ? 'Others Selected' : null,

            // Web Services
            'web_new_email' => in_array('new_email_account', $services) ? 1 : 0,
            'web_reset_email' => in_array('reset_email_password', $services) ? 1 : 0,
            'web_tarpaulin' => in_array('logo_graphic', $services) ? 1 : 0,
            'web_photo_video' => in_array('website_correction', $services) ? 1 : 0,
            'web_website_update' => in_array('website_update', $services) ? 1 : 0,
            'web_request_multimedia' => in_array('multimedia_files', $services) ? 1 : 0,
            'web_others' => in_array('web_other', $services) ? 'Others Selected' : null,
        ];

        $user = Auth::user();
        $controlNumber = 'REQ-'.now()->format('Y-m-d').'-'.str_pad(ServiceRequest::count() + 1, 4, '0', STR_PAD_LEFT);

        // Create request with all service columns
        ServiceRequest::create(array_merge([
            'user_id' => $user->id,
            'control_number' => $controlNumber,
            'requester_name' => $user->full_name,
            'requester_office' => $user->office,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'status' => 'pending',
        ], $serviceData));

        return redirect()->route('dashboard')->with('success', 'Service request created successfully!');
    }

    public function show(int $id)
    {
        $request = ServiceRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('statusLogs')
            ->firstOrFail();

        return view('user.request-details-new', compact('request'));
    }

    /**
     * Generate and cache PDF content for fast delivery
     */
    private function generatePDF(ServiceRequest $serviceRequest): string
    {
        $cacheKey = 'pdf_v11_'.$serviceRequest->id.'_'.$serviceRequest->updated_at->timestamp;

        // Try to get from cache first - instant delivery on subsequent requests
        // PDF binaries can exceed MySQL's packet limit when the application's
        // default cache store is database. Keep document bytes on disk.
        $pdfContent = Cache::store('file')->remember($cacheKey, now()->addDays(30), function () use ($serviceRequest) {
            $request = $serviceRequest; // Rename for PDF template compatibility
            $html = view('pdf.service-request', compact('request'))->render();

            // Optimize DomPDF for speed
            $pdf = Pdf::loadHTML($html);
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

    public function print(int $id)
    {
        $serviceRequest = ServiceRequest::with(['assignedTechnician', 'user'])->findOrFail($id);
        $this->authorize('view', $serviceRequest);

        // Verify user owns this request
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this request');
        }

        try {
            // Get cached or newly generated PDF - cached PDFs are instant
            $pdfContent = $this->generatePDF($serviceRequest);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="MIS-Request-'.$serviceRequest->control_number.'.pdf"',
            ]);
        } catch (\Throwable $e) {
            $this->logPdfFailure('print', $e, $serviceRequest);

            return redirect()->back()->with('error', 'The PDF could not be generated. Please try again.');
        }
    }

    public function viewPDF(int $id)
    {
        $serviceRequest = ServiceRequest::with(['assignedTechnician', 'user'])->findOrFail($id);
        $this->authorize('view', $serviceRequest);

        // Verify user owns this request
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this request');
        }

        try {
            // Get cached or newly generated PDF - cached PDFs are instant
            $pdfContent = $this->generatePDF($serviceRequest);

            $etag = '"'.sha1($pdfContent).'"';
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="MIS-Request-'.$serviceRequest->control_number.'.pdf"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'ETag' => $etag,
            ];

            if (request()->header('If-None-Match') === $etag) {
                return response('', 304, $headers);
            }

            return response($pdfContent, 200, $headers);
        } catch (\Throwable $e) {
            $this->logPdfFailure('view', $e, $serviceRequest);

            return redirect()->back()->with('error', 'The PDF could not be generated. Please try again.');
        }
    }

    public function downloadPDF(int $id)
    {
        $serviceRequest = ServiceRequest::with(['assignedTechnician', 'user'])->findOrFail($id);
        $this->authorize('view', $serviceRequest);

        // Verify user owns this request
        if ($serviceRequest->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this request');
        }

        try {
            // Get cached or newly generated PDF - cached PDFs are instant
            $pdfContent = $this->generatePDF($serviceRequest);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="MIS-Request-'.$serviceRequest->control_number.'.pdf"',
            ]);
        } catch (\Throwable $e) {
            $this->logPdfFailure('download', $e, $serviceRequest);

            return redirect()->back()->with('error', 'The PDF could not be downloaded. Please try again.');
        }
    }

    private function logPdfFailure(string $action, \Throwable $exception, ServiceRequest $serviceRequest): void
    {
        Log::error('Client service-request PDF failed.', [
            'action' => $action,
            'service_request_id' => $serviceRequest->id,
            'exception' => $exception::class,
            'code' => (string) $exception->getCode(),
            // Bound the message so a failed binary cache query cannot inflate
            // the log or be copied into the database-backed session.
            'message' => Str::limit(preg_replace('/\s+/', ' ', $exception->getMessage()), 500),
        ]);
    }

    /**
     * Get user notifications (API endpoint for AJAX)
     */
    private function getNotificationsLegacy()
    {
        $user = Auth::user();

        $notifications = [];

        // 1. Fetch recent status update notifications from the Notification table.
        $statusNotifications = Notification::where('user_id', $user->id)
            ->where('notification_type', 'status_update')
            ->with('serviceRequest')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($statusNotifications as $notif) {
            if ($notif->serviceRequest) {
                $notifications[] = [
                    'id' => $notif->id,
                    'title' => '🔔 Status Update',
                    'message' => $notif->message ?? 'Control #'.$notif->serviceRequest->control_number.' - status has been updated',
                    'type' => 'status_update',
                    'url' => route('request.show', $notif->service_request_id),
                    'time' => $notif->created_at->diffForHumans(),
                    'timestamp' => $notif->created_at->timestamp,
                    'read' => $notif->status === 'sent' ? false : true,
                ];
            }
        }

        // 2. Get user's requests grouped by status
        $requests = ServiceRequest::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Pending requests
        $pendingRequests = $requests->where('status', 'pending');
        if ($pendingRequests->count() > 0) {
            foreach ($pendingRequests as $request) {
                $notifications[] = [
                    'id' => $request->id,
                    'title' => '📋 '.($request->title ?? 'Pending Request'),
                    'message' => 'Control #'.$request->control_number.' - awaiting admin review',
                    'type' => 'pending',
                    'url' => route('request.show', $request->id),
                    'time' => $request->updated_at->diffForHumans(),
                    'timestamp' => $request->updated_at->timestamp,
                    'read' => false,
                ];
            }
        }

        // Processing requests
        $processingRequests = $requests->where('status', 'processing');
        if ($processingRequests->count() > 0) {
            foreach ($processingRequests as $request) {
                $notifications[] = [
                    'id' => $request->id,
                    'title' => '🔄 '.($request->title ?? 'Processing Request'),
                    'message' => 'Control #'.$request->control_number.' - technician is working on it',
                    'type' => 'processing',
                    'url' => route('request.show', $request->id),
                    'time' => $request->updated_at->diffForHumans(),
                    'timestamp' => $request->updated_at->timestamp,
                    'read' => false,
                ];
            }
        }

        // Completed requests (recent)
        $completedRequests = $requests->where('status', 'completed')->take(2);
        if ($completedRequests->count() > 0) {
            foreach ($completedRequests as $request) {
                $notifications[] = [
                    'id' => $request->id,
                    'title' => '✅ '.($request->title ?? 'Completed Request'),
                    'message' => 'Control #'.$request->control_number.' - task completed successfully',
                    'type' => 'completed',
                    'url' => route('request.show', $request->id),
                    'time' => $request->updated_at->diffForHumans(),
                    'timestamp' => $request->updated_at->timestamp,
                    'read' => true,
                ];
            }
        }

        // Rejected requests
        $rejectedRequests = $requests->where('status', 'rejected')->take(1);
        if ($rejectedRequests->count() > 0) {
            foreach ($rejectedRequests as $request) {
                $notifications[] = [
                    'id' => $request->id,
                    'title' => '❌ '.($request->title ?? 'Rejected Request'),
                    'message' => 'Control #'.$request->control_number.' - request was rejected',
                    'type' => 'rejected',
                    'url' => route('request.show', $request->id),
                    'time' => $request->updated_at->diffForHumans(),
                    'timestamp' => $request->updated_at->timestamp,
                    'read' => false,
                ];
            }
        }

        // Cancelled requests
        $cancelledRequests = $requests->where('status', 'cancelled')->take(1);
        if ($cancelledRequests->count() > 0) {
            foreach ($cancelledRequests as $request) {
                $notifications[] = [
                    'id' => $request->id,
                    'title' => '🚫 '.($request->title ?? 'Cancelled Request'),
                    'message' => 'Control #'.$request->control_number.' - request was cancelled',
                    'type' => 'cancelled',
                    'url' => route('request.show', $request->id),
                    'time' => $request->updated_at->diffForHumans(),
                    'timestamp' => $request->updated_at->timestamp,
                    'read' => false,
                ];
            }
        }

        // Sort by timestamp (most recent first)
        usort($notifications, function ($a, $b) {
            return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
        });

        // Remove timestamp before returning
        foreach ($notifications as &$notif) {
            unset($notif['timestamp']);
        }

        return response()->json([
            'notifications' => array_slice($notifications, 0, 10),
            'total_unread' => count(array_filter($notifications, fn ($n) => ! $n['read'])),
        ]);
    }

    /**
     * Get user's login history (API endpoint for modal)
     */
    public function getLoginHistory()
    {
        $user = Auth::user();

        // Get last 10 login attempts
        $loginAttempts = LoginAttempt::where('username', $user->username)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attempt) {
                return [
                    'status' => $attempt->success ? 'Success' : 'Failed',
                    'status_class' => $attempt->success ? 'success' : 'danger',
                    'ip_address' => $attempt->ip_address ?? 'Unknown',
                    'device_info' => $attempt->device_info ?? 'Unknown Device',
                    'user_agent' => $attempt->user_agent ?? 'Unknown',
                    'timestamp' => $attempt->created_at->format('M d, Y H:i:s'),
                    'time_ago' => $attempt->created_at->diffForHumans(),
                    'failure_reason' => $attempt->failure_reason ?? null,
                ];
            });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'created_at' => $user->created_at->format('M d, Y'),
            ],
            'login_attempts' => $loginAttempts,
        ]);
    }

    /**
     * Mark a notification as read
     */
    private function markNotificationReadLegacy(int $id)
    {
        $user = Auth::user();
        $request = ServiceRequest::find($id);

        if (! $request || $request->user_id !== $user->id) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        // In a real implementation, you would mark the notification as read in the database
        // For now, we're handling it client-side, but you can store read status here
        return response()->json(['success' => true]);
    }

    /**
     * Return the user's persistent notifications and unread message count.
     */
    public function getNotifications()
    {
        $user = Auth::user();

        $notificationQuery = Notification::query()
            ->where('user_id', $user->id)
            ->with('serviceRequest:id,user_id,control_number');
        $totalUnread = (clone $notificationQuery)->whereNull('read_at')->count();
        $notifications = $notificationQuery
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (Notification $notification) {
                $serviceRequest = $notification->serviceRequest;

                return [
                    'id' => $notification->id,
                    'title' => $this->notificationTitle($notification->notification_type),
                    'message' => $notification->message ?: 'Your request has been updated.',
                    'type' => $notification->notification_type ?: 'update',
                    'url' => $notification->action_url ?: ($serviceRequest ? route('requests.show', $serviceRequest) : route('requests.index')),
                    'time' => $notification->created_at->diffForHumans(),
                    'delivery' => in_array($notification->notification_type, [
                        'client_update', 'target_date_updated', 'visit_scheduled',
                        'incident_report_update', 'maintenance_update',
                    ], true) ? 'Delivered in app' : match ($notification->status) {
                        'sent' => 'Email sent',
                        'failed' => 'Email delivery failed',
                        default => 'Email queued',
                    },
                    'read' => $notification->read_at !== null,
                ];
            });

        $unreadMessages = Message::query()
            ->fromAdmin()
            ->unread()
            ->whereHas('serviceRequest', fn ($query) => $query->where('user_id', $user->id))
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'total_unread' => $totalUnread,
            'unread_messages' => $unreadMessages,
        ]);
    }

    public function notificationsPage()
    {
        $notifications = Notification::where('user_id', Auth::id())->latest()->paginate(25);

        return view('user.notifications', compact('notifications'));
    }

    /**
     * Mark one notification as read, scoped to the signed-in user.
     */
    public function markNotificationRead(int $id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the signed-in user.
     */
    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return request()->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'All notifications marked as read.');
    }

    public function deleteNotification(int $id)
    {
        Notification::where('user_id', Auth::id())->findOrFail($id)->delete();

        return request()->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Notification deleted.');
    }

    public function clearNotifications()
    {
        $deleted = Notification::where('user_id', Auth::id())->delete();

        return request()->expectsJson()
            ? response()->json(['success' => true, 'deleted' => $deleted])
            : back()->with('success', 'Notifications cleared.');
    }

    private function notificationTitle(?string $type): string
    {
        return match ($type) {
            'status_update' => 'Request status updated',
            'assignment', 'technician_assigned' => 'Technician assigned',
            'request_created' => 'Request received',
            'request_completed' => 'Request completed',
            'clarification_requested' => 'Response required',
            'client_update' => 'MIS progress update',
            'target_date_updated' => 'Request updated',
            'visit_scheduled' => 'Service visit scheduled',
            'request_reminder' => 'Service request reminder',
            'incident_report_update' => 'ICT incident record updated',
            'maintenance_update' => 'Maintenance record updated',
            'request_rejected' => 'Request rejected',
            'message' => 'New message',
            'security_alert' => 'Account security alert',
            default => 'Request update',
        };
    }
}
