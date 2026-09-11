<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmailNotificationService;
use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @method bool isAdmin()
 */
class MessageController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploads,
        private readonly EmailNotificationService $emailNotifications
    ) {}

    /**
     * Load messenger page for user with optimized queries
     */
    public function userMessenger(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $selectedRequestId = max(0, $request->integer('request'));
        $query = $user->serviceRequests()
            ->select('id', 'control_number', 'description', 'status', 'created_at', 'user_id')
            ->with([
                'messages' => fn ($messages) => $messages->latest('id')->limit(1),
            ])
            ->withCount([
                'messages as unread_messages_count' => fn ($messages) => $messages
                    ->where('is_admin', true)
                    ->whereNull('read_at'),
            ])
            ->withMax('messages', 'id');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('control_number', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('messages', fn ($messages) => $messages
                        ->where('message', 'like', '%'.$search.'%'));
            });
        }

        if ($selectedRequestId) {
            $query->orderByRaw('CASE WHEN service_requests.id = ? THEN 0 ELSE 1 END', [$selectedRequestId]);
        }

        $requests = $query
            ->orderByDesc('messages_max_id')
            ->orderByDesc('created_at')
            ->get();

        return view('user.messenger_new', ['requests' => $requests]);
    }

    /**
     * Load messenger page for admin with optimized queries
     */
    public function adminMessenger()
    {
        // Get all service requests for admin with eager loading
        $requests = ServiceRequest::with('user:id,first_name,last_name,email')
            ->select('id', 'control_number', 'description', 'status', 'created_at', 'user_id', 'assigned_technician_id')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.messenger', ['requests' => $requests]);
    }

    /**
     * Get all messages for a service request
     */
    public function getMessages(int $requestId, Request $httpRequest)
    {
        $request = ServiceRequest::findOrFail($requestId);

        // Check authorization
        /** @var User|null $user */
        $user = Auth::user();
        if ($request->user_id !== Auth::id() && ! ($user instanceof User && $user->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messageQuery = Message::where('service_request_id', $requestId)
            ->with(['sender:id,first_name,last_name,is_admin', 'attachments']);
        $limit = $httpRequest->integer('limit');
        $hasMore = false;

        if ($limit > 0) {
            $limit = min(100, max(10, $limit));
            if ($beforeId = $httpRequest->integer('before_id')) {
                $messageQuery->where('id', '<', $beforeId);
            }
            $messageRows = $messageQuery->orderByDesc('id')->limit($limit + 1)->get();
            $hasMore = $messageRows->count() > $limit;
            $messageRows = $messageRows->take($limit)->sortBy('id')->values();
        } else {
            // Preserve the existing client Messenger behavior when no page size is requested.
            $messageRows = $messageQuery->orderBy('id')->get();
        }

        // Mark all unread messages as read for the current user
        $this->markIncomingMessagesAsRead($requestId, $user);

        return response()->json([
            'success' => true,
            'messages' => $messageRows->map(fn (Message $message) => $this->formatMessage($message))->values(),
            'has_more' => $hasMore,
            'oldest_id' => $messageRows->first()?->id,
            'last_id' => $messageRows->last()?->id,
        ]);
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'service_request_id' => 'required|exists:service_requests,id',
            'message' => 'required|string|min:1|max:5000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:'.$this->uploadMaxKilobytes(),
        ]);

        $serviceRequest = ServiceRequest::findOrFail($validated['service_request_id']);

        // Check authorization - user can message their own request, admin can message any request
        /** @var User|null $user */
        $user = Auth::user();
        if ($serviceRequest->user_id !== Auth::id() && ! ($user instanceof User && $user->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user instanceof User && $user->isAdmin() && ! $serviceRequest->user_id) {
            return response()->json([
                'error' => 'This is an account-free walk-in request. Contact the requester using the recorded phone number.',
            ], 422);
        }

        $message = DB::transaction(function () use ($request, $validated, $user) {
            $message = Message::create([
                'service_request_id' => $validated['service_request_id'],
                'sender_id' => Auth::id(),
                'message' => $validated['message'],
                'is_admin' => $user instanceof User && $user->isAdmin(),
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $stored = $this->fileUploads->upload($file, 'message-attachments', [
                    'service_request_id' => $validated['service_request_id'],
                    'message_id' => $message->id,
                ]);
                $message->attachments()->create([
                    'uploaded_by' => Auth::id(),
                    'path' => $stored['path'],
                    'original_name' => $stored['original_filename'],
                    'mime_type' => $file->getMimeType(),
                    'size' => $stored['size'] ?? $file->getSize(),
                ]);
            }

            return $message;
        });

        if ($message->is_admin && $serviceRequest->user_id) {
            $this->emailNotifications->sendNewMessageNotification($serviceRequest, $message);
        }

        // Load sender relationship
        $message->load(['sender:id,first_name,last_name,is_admin', 'attachments']);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message),
        ]);
    }

    /**
     * Get unread message count for a request (for real-time updates)
     */
    public function getUnreadCount(int $requestId)
    {
        $request = ServiceRequest::findOrFail($requestId);

        // Check authorization
        /** @var User|null $user */
        $user = Auth::user();
        if ($request->user_id !== Auth::id() && ! ($user instanceof User && $user->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get unread count
        $unreadCount = Message::where('service_request_id', $requestId)
            ->where('is_admin', ! ($user instanceof User && $user->isAdmin()))
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get new messages since a specific timestamp (for polling)
     */
    public function getNewMessages(int $requestId, Request $request)
    {
        $serviceRequest = ServiceRequest::findOrFail($requestId);

        // Check authorization
        /** @var User|null $user */
        $user = Auth::user();
        if ($serviceRequest->user_id !== Auth::id() && ! ($user instanceof User && $user->isAdmin())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $since = $request->query('since'); // Backward-compatible client cursor.
        $afterId = max(0, $request->integer('after_id'));

        $query = Message::where('service_request_id', $requestId);

        if ($afterId) {
            $query->where('id', '>', $afterId);
        } elseif ($since) {
            try {
                $query->where('created_at', '>', Carbon::parse($since)->format('Y-m-d H:i:s.u'));
            } catch (\Throwable) {
                return response()->json(['error' => 'Invalid message cursor.'], 422);
            }
        }

        $messages = $query->with(['sender:id,first_name,last_name,is_admin', 'attachments'])
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (Message $message) => $this->formatMessage($message));

        // Mark as read
        $this->markIncomingMessagesAsRead($requestId, $user);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'last_id' => ($messages->last()['id'] ?? null) ?: ($afterId ?: null),
            'read_message_ids' => Message::where('service_request_id', $requestId)
                ->where('sender_id', Auth::id())
                ->whereNotNull('read_at')
                ->latest('id')
                ->limit(100)
                ->pluck('id'),
        ]);
    }

    /**
     * Mark a specific message as read
     */
    public function markAsRead(int $messageId)
    {
        $message = Message::with('serviceRequest')->findOrFail($messageId);

        /** @var User|null $user */
        $user = Auth::user();
        $serviceRequest = $message->serviceRequest;

        if (! $serviceRequest || ($serviceRequest->user_id !== Auth::id() && ! ($user instanceof User && $user->isAdmin()))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only the recipient can mark as read
        $isAdmin = $user instanceof User && $user->isAdmin();
        $isIncoming = $isAdmin ? ! $message->is_admin : $message->is_admin;
        if (! $isIncoming) {
            return response()->json(['error' => 'Only a message recipient can mark it as read'], 403);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read',
        ]);
    }

    public function downloadAttachment(MessageAttachment $attachment)
    {
        $attachment->load('message.serviceRequest');
        $serviceRequest = $attachment->message?->serviceRequest;
        $user = Auth::user();

        abort_unless($serviceRequest, 404);
        abort_unless(
            $serviceRequest->user_id === Auth::id() || ($user instanceof User && $user->isAdmin()),
            403
        );
        abort_unless(Storage::disk('secure')->exists($attachment->path), 404);

        return Storage::disk('secure')->download($attachment->path, $attachment->original_name);
    }

    private function formatMessage(Message $message): array
    {
        /** @var User|null $viewer */
        $viewer = Auth::user();
        $attachmentRoute = $viewer instanceof User && $viewer->isAdmin()
            ? 'admin.messages.attachments.download'
            : 'messages.attachments.download';

        return [
            'id' => $message->id,
            'service_request_id' => $message->service_request_id,
            'sender_id' => $message->sender_id,
            'sender_name' => trim(($message->sender?->first_name ?? 'Former').' '.($message->sender?->last_name ?? 'User')),
            'is_admin' => $message->is_admin,
            'message' => $message->message,
            'read_at' => $message->read_at?->toIso8601String(),
            'is_read' => $message->read_at !== null,
            'created_at' => $message->created_at->format('M d, Y h:i A'),
            'created_at_iso' => $message->created_at->toIso8601String(),
            'attachments' => $message->attachments->map(fn (MessageAttachment $attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'download_url' => route($attachmentRoute, $attachment),
            ])->values(),
        ];
    }

    private function markIncomingMessagesAsRead(int $requestId, ?User $user): void
    {
        $isAdmin = $user instanceof User && $user->isAdmin();

        Message::where('service_request_id', $requestId)
            ->where('is_admin', ! $isAdmin)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function uploadMaxKilobytes(): int
    {
        return max(1024, min(20480, (int) Setting::get('max_upload_size', 10) * 1024));
    }
}
