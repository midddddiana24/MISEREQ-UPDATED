<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminMessengerController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'scope' => 'nullable|in:messages,unread,all',
            'search' => 'nullable|string|max:150',
            'request' => 'nullable|integer|exists:service_requests,id',
        ]);
        $scope = $filters['scope'] ?? 'messages';
        $search = trim((string) ($filters['search'] ?? ''));
        $selectedRequestId = isset($filters['request']) ? (int) $filters['request'] : null;
        $query = ServiceRequest::with([
            'messages' => fn ($messages) => $messages->latest()->limit(1),
            'user',
            'assignedTechnician.user',
        ])->withCount([
            'messages as unread_messages_count' => fn ($messages) => $messages
                ->where('is_admin', false)
                ->whereNull('read_at'),
        ])->withMax('messages', 'created_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('control_number', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('requester_office', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($users) => $users
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($scope === 'unread') {
            $query->where(function ($builder) use ($selectedRequestId) {
                $builder->whereHas('messages', fn ($messages) => $messages
                    ->where('is_admin', false)
                    ->whereNull('read_at'));
                if ($selectedRequestId) $builder->orWhere('service_requests.id', $selectedRequestId);
            });
        } elseif ($scope !== 'all') {
            $query->where(function ($builder) use ($selectedRequestId) {
                // Every account-linked request has a ready conversation, even
                // before the requester or MIS sends the first message.
                $builder->whereNotNull('user_id')
                    ->orWhereHas('messages');
                if ($selectedRequestId) $builder->orWhere('service_requests.id', $selectedRequestId);
            });
            $scope = 'messages';
        }

        if ($selectedRequestId) {
            $query->orderByRaw('CASE WHEN service_requests.id = ? THEN 0 ELSE 1 END', [$selectedRequestId]);
        }

        $requestPage = $query->orderByDesc('messages_max_created_at')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $requests = $requestPage->getCollection()
            ->map(function ($request) {
                // Get the last message for this request
                $lastMessage = $request->messages->first();

                // Count unread messages from users (is_admin = false)
                $unreadCount = $request->unread_messages_count;

                $requesterName = $request->user?->full_name ?: ($request->requester_name ?: 'Walk-in requester');

                return [
                    'id' => $request->id,
                    'control_number' => $request->control_number,
                    'status' => $request->status,
                    'description' => $request->description,
                    'requester_name' => $requesterName,
                    'requester_office' => $request->requester_office ?: $request->office,
                    'phone' => $request->phone,
                    'user' => [
                        'first_name' => $request->user?->first_name ?? ($request->requester_name ?: 'Walk-in requester'),
                        'last_name' => $request->user?->last_name ?? '',
                        'email' => $request->user?->email ?? 'No client account',
                    ],
                    'can_message' => (bool) $request->user_id,
                    'is_walk_in' => $request->is_admin_created && !$request->user_id,
                    'technician' => $request->assignedTechnician?->full_name,
                    'request_url' => route('admin.service-requests.show', $request),
                    'unread_count' => $unreadCount,
                    'last_message_text' => $lastMessage ? Str::limit($lastMessage->message, 50) : 'No messages yet',
                    'last_message_time' => $lastMessage?->created_at,
                    'last_message_from_user' => $lastMessage && !$lastMessage->is_admin,
                ];
            })->values();

        return view('admin.messenger', [
            'requests' => collect($requests)->keyBy('id'),
            'requestPage' => $requestPage,
            'scope' => $scope,
            'search' => $search,
            'selectedRequestId' => $selectedRequestId,
        ]);
    }

    public function unreadSummary()
    {
        $conversations = ServiceRequest::query()
            ->whereHas('messages')
            ->with([
                'messages' => fn ($messages) => $messages->latest('id')->limit(1),
            ])
            ->withCount([
                'messages as unread_messages_count' => fn ($messages) => $messages
                    ->where('is_admin', false)
                    ->whereNull('read_at'),
            ])
            ->withMax('messages', 'id')
            ->orderByDesc('messages_max_id')
            ->limit(100)
            ->get()
            ->map(function (ServiceRequest $serviceRequest) {
                $latest = $serviceRequest->messages->first();

                return [
                    'request_id' => $serviceRequest->id,
                    'unread_count' => (int) $serviceRequest->unread_messages_count,
                    'last_message_id' => (int) ($serviceRequest->messages_max_id ?? 0),
                    'last_message_text' => $latest ? Str::limit($latest->message, 50) : 'No messages yet',
                    'last_message_from_user' => $latest ? !$latest->is_admin : false,
                    'last_message_at' => $latest?->created_at?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'total_unread' => Message::fromUser()
                ->unread()
                ->whereHas('serviceRequest')
                ->count(),
            'conversations' => $conversations,
        ]);
    }
}
