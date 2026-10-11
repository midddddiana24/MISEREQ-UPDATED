@extends('admin.layout')

@section('title', 'Notifications')
@section('page-title', 'Notification Center')
@section('page-subtitle', 'Review administrative alerts and failed email deliveries.')

@section('content')
    <style>
        .notification-center-panel {
            width: 100%;
            max-width: 1320px;
            min-width: 0;
            margin-inline: auto;
        }

        .notification-center-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--line);
        }

        .notification-center-heading h2 {
            margin: 0;
            color: var(--ink-900);
            font-size: 20px;
        }

        .notification-center-heading p {
            margin: 5px 0 0;
            color: var(--ink-500);
            font-size: 14px;
        }

        .notification-center-tools { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
        .notification-center-tools form, .notification-center-actions form { margin: 0; }
        .notification-center-tools button { min-height: 40px; }
        .notification-center-tools button:disabled { opacity: .55; cursor: not-allowed; }
        .notification-center-note { margin: 0 0 18px; color: var(--ink-500); font-size: 13px; line-height: 1.5; }
        .notification-center-stats { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .notification-center-stat { padding: 8px 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface-soft); color: var(--ink-700); font-size: 13px; }
        .notification-center-stat strong { color: var(--ink-900); }

        .notification-center-list { display: grid; gap: 12px; }

        .notification-center-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(190px, 230px);
            gap: 22px;
            min-width: 0;
            padding: 18px 20px;
            border: 1px solid var(--line);
            border-left: 4px solid var(--brand-700);
            border-radius: 9px;
            background: var(--surface);
        }

        .notification-center-item.is-failed { border-left-color: var(--danger); }
        .notification-center-item.is-read:not(.is-failed) { border-left-color: var(--line); }
        .notification-center-main, .notification-center-side { min-width: 0; }

        .notification-center-date {
            display: block;
            margin-bottom: 7px;
            color: var(--ink-500);
            font-size: 13px;
        }

        .notification-center-item h3 {
            margin: 0 0 6px;
            color: var(--ink-900);
            font-size: 17px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .notification-center-message {
            margin: 0;
            color: var(--ink-700);
            font-size: 14px;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .notification-center-recipient {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 8px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--line);
            color: var(--ink-500);
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .notification-center-recipient strong { color: var(--ink-700); }

        .notification-center-side {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 9px;
            padding-left: 20px;
            border-left: 1px solid var(--line);
        }

        .notification-center-side-label {
            color: var(--ink-500);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .notification-center-response {
            margin: 0;
            color: var(--ink-500);
            font-size: 13px;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .notification-center-actions { display: flex; flex-wrap: wrap; gap: 7px; margin-top: auto; padding-top: 6px; }
        .notification-center-actions .btn-ghost,
        .notification-center-actions .btn-primary,
        .notification-center-actions .btn-danger { min-height: 40px; }
        .notification-center-type { display: inline-block; margin-bottom: 8px; color: var(--ink-500); font-size: 12px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }

        .notification-center-empty {
            padding: 32px 20px;
            border: 1px dashed var(--line);
            border-radius: 9px;
            color: var(--ink-500);
            text-align: center;
            background: var(--surface-soft);
        }

        .notification-center-pagination { margin-top: 18px; }

        @media (max-width: 760px) {
            .notification-center-heading { flex-direction: column; gap: 12px; }
            .notification-center-tools { justify-content: flex-start; }
            .notification-center-item { grid-template-columns: 1fr; gap: 14px; padding: 16px; }
            .notification-center-side {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 7px 12px;
                padding: 12px 0 0;
                border-left: 0;
                border-top: 1px solid var(--line);
            }
            .notification-center-side-label,
            .notification-center-side .status-badge,
            .notification-center-response { grid-column: 1; }
            .notification-center-side .status-badge { justify-self: start; }
            .notification-center-actions { grid-column: 2; grid-row: 1 / span 3; align-self: center; margin: 0; padding: 0; }
        }

        @media (max-width: 430px) {
            .notification-center-side { grid-template-columns: 1fr; }
            .notification-center-actions { grid-column: 1; grid-row: auto; margin-top: 3px; }
            .notification-center-tools { width: 100%; }
            .notification-center-tools form, .notification-center-tools button { flex: 1 1 100%; width: 100%; }
        }
    </style>

    <section class="panel notification-center-panel" aria-labelledby="notification-list-title">
        <header class="notification-center-heading">
            <div>
                <h2 id="notification-list-title">All notifications</h2>
                <p>{{ $notifications->total() }} {{ \Illuminate\Support\Str::plural('notification', $notifications->total()) }} in this view</p>
            </div>
            <div class="notification-center-tools" aria-label="Manage your admin alerts">
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-ghost" @disabled($unreadCount === 0)>Mark all read</button>
                </form>
                <form method="POST" action="{{ route('admin.notifications.clear') }}" onsubmit="return confirm('Clear all of your admin action alerts? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger" @disabled($alertCount === 0)>Clear all alerts</button>
                </form>
            </div>
        </header>
        <p class="notification-center-note">Mark all and Clear all affect only your admin action alerts. Security notices and failed deliveries for other recipients remain visible. Clear all also removes your action alerts with failed delivery.</p>
        <div class="notification-center-stats" aria-label="Notification counts">
            <span class="notification-center-stat"><strong>{{ $unreadCount }}</strong> unread admin {{ \Illuminate\Support\Str::plural('alert', $unreadCount) }}</span>
            <span class="notification-center-stat"><strong>{{ $alertCount }}</strong> total admin {{ \Illuminate\Support\Str::plural('alert', $alertCount) }}</span>
            <span class="notification-center-stat"><strong>{{ $failedCount }}</strong> failed email {{ \Illuminate\Support\Str::plural('delivery', $failedCount) }}</span>
        </div>

        <div class="notification-center-list">
            @forelse($notifications as $notification)
                @php($canManage = $notification->user_id === auth()->id() && $notification->notification_type === 'admin_action_required')
                <article class="notification-center-item {{ $notification->status === 'failed' ? 'is-failed' : ($notification->read_at ? 'is-read' : 'is-unread') }}">
                    <div class="notification-center-main">
                        <span class="notification-center-type">{{ $canManage ? ($notification->read_at ? 'Read admin alert' : 'Unread admin alert') : ($notification->status === 'failed' ? 'Failed delivery record' : 'Security or account notice') }}</span>
                        <time class="notification-center-date" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('M d, Y \a\t h:i A') }}</time>
                        <h3>{{ $notification->subject ?: 'System notification' }}</h3>
                        <p class="notification-center-message">{{ $notification->message }}</p>
                        <div class="notification-center-recipient">
                            <span>Recipient:</span>
                            <strong>{{ $notification->user?->full_name ?: 'System recipient' }}</strong>
                            @if($notification->recipient_email ?: $notification->user?->email)
                                <span>({{ $notification->recipient_email ?: $notification->user?->email }})</span>
                            @endif
                        </div>
                    </div>
                    <div class="notification-center-side">
                        <span class="notification-center-side-label">Delivery</span>
                        <span class="status-badge {{ $notification->status === 'failed' ? 'rejected' : 'completed' }}">{{ ucfirst($notification->status) }}</span>
                        @if($notification->response)<p class="notification-center-response">{{ $notification->response }}</p>@endif
                        <div class="notification-center-actions">
                            @if($notification->status === 'failed')
                                <form method="POST" action="{{ route('admin.notifications.retry', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary">Retry Email</button>
                                </form>
                            @endif
                            @if($notification->action_url)
                                <a class="btn-ghost" href="{{ $notification->action_url }}" @if($canManage && !$notification->read_at) data-notification-open data-read-url="{{ route('admin.notifications.read', $notification) }}" @endif>Open</a>
                            @endif
                            @if($canManage)
                                @if(!$notification->read_at)
                                    <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="btn-ghost">Mark read</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.notifications.delete', $notification) }}" onsubmit="return confirm('Delete this notification?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            @elseif($notification->status !== 'failed' && !$notification->action_url)
                                <span class="table-note">No action</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="notification-center-empty">No notifications found.</p>
            @endforelse
        </div>

        <div class="notification-center-pagination">{{ $notifications->links('admin.partials.pagination', ['paginationLabel' => 'Notification pages']) }}</div>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-notification-open]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                fetch(link.dataset.readUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    }
                }).finally(function() { window.location.href = link.href; });
            });
        });
    </script>
@endsection
