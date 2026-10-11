<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications · MIS Service Request System</title>
    @include('user.partials.theme')
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
        }

        .notifications-panel {
            width: 100%;
            max-width: 1080px;
            margin-inline: auto;
        }

        .notification-page-head {
            margin-bottom: 16px;
            padding-bottom: 15px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid var(--client-line);
        }

        .notification-page-head h1 {
            margin: 0 0 4px;
            color: var(--client-ink-900);
            font-size: clamp(20px, 2.4vw, 25px);
            line-height: 1.2;
        }

        .notification-page-head p {
            margin: 0;
            color: var(--client-ink-500);
            font-size: 14px;
        }

        .notification-page-count {
            display: block;
            margin-top: 9px;
            color: var(--client-ink-700);
            font-size: 13px;
            font-weight: 700;
        }

        .notification-page-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .notification-page-actions form {
            margin: 0;
        }

        .notification-page-list {
            display: grid;
            gap: 12px;
        }

        .notification-page-card {
            min-width: 0;
            padding: 18px 20px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 136px;
            align-items: stretch;
            gap: 22px;
            border: 1px solid var(--client-line);
            border-radius: 12px;
            background: #fff;
        }

        .notification-page-card.is-unread {
            border-left: 4px solid var(--client-brand-700);
            background: #f6fafc;
        }

        .notification-page-copy {
            min-width: 0;
        }

        .notification-page-copy strong {
            display: block;
            color: var(--client-ink-900);
            font-size: 17px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .notification-page-copy p {
            margin: 4px 0 0;
            color: var(--client-ink-700);
            font-size: 14px;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .notification-page-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 7px;
            color: var(--client-ink-500);
            font-size: 13px;
        }

        .notification-page-state {
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--client-surface-soft);
            color: var(--client-ink-700);
            font-size: 12px;
            font-weight: 700;
        }

        .is-unread .notification-page-state {
            background: #dcecf5;
            color: var(--client-brand-700);
        }

        .notification-card-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: center;
            gap: 8px;
            padding-left: 18px;
            border-left: 1px solid var(--client-line);
        }

        .notification-card-actions form {
            margin: 0;
        }

        .notification-card-actions .button {
            width: 100%;
            text-align: center;
        }

        .notification-delete-action {
            color: #953b3b !important;
            border-color: #dfcaca !important;
            background: #fff !important;
        }

        .notification-delete-action:hover {
            background: #fff4f4 !important;
        }

        .notification-empty-state {
            padding: 38px 18px;
            color: var(--client-ink-500);
            border: 1px dashed var(--client-line-strong);
            border-radius: 8px;
            text-align: center;
            background: var(--client-surface-soft);
        }

        .notification-pagination {
            margin-top: 16px;
        }

        @media (max-width: 640px) {
            .notification-page-card { padding: 12px; }

            .notification-page-head {
                flex-direction: column;
            }

            .notification-page-actions {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .notification-page-actions form,
            .notification-page-actions .button {
                width: 100%;
            }

            .notification-page-card {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .notification-card-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                padding: 10px 0 0;
                border-left: 0;
                border-top: 1px solid var(--client-line);
            }

            .notification-card-actions>*,
            .notification-card-actions form,
            .notification-card-actions .button {
                width: 100%;
            }
        }

    </style>
</head>

<body>
    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        <section class="panel notifications-panel" aria-labelledby="notifications-title">
            <header class="notification-page-head">
                <div>
                    <h1 id="notifications-title">Notifications</h1>
                    <p>Updates about your service requests, incidents, and maintenance records.</p>
                    <span class="notification-page-count">{{ $notifications->total() }} {{ \Illuminate\Support\Str::plural('notification', $notifications->total()) }}</span>
                </div>
                <div class="notification-page-actions">
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="button secondary">Mark all read</button>
                    </form>
                    <form method="POST" action="{{ route('notifications.clear') }}" onsubmit="return confirm('Clear all notifications? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button secondary notification-delete-action">Clear all</button>
                    </form>
                </div>
            </header>

            @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            <div class="notification-page-list">
                @forelse($notifications as $notification)
                <article class="notification-page-card {{ $notification->read_at ? '' : 'is-unread' }}">
                    <div class="notification-page-copy">
                        <div class="notification-page-meta">
                            <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('M d, Y') }} at {{ $notification->created_at->format('h:i A') }}</time>
                            <span class="notification-page-state">{{ $notification->read_at ? 'Read' : 'Unread' }}</span>
                        </div>
                        <strong>{{ $notification->subject ?: 'Request update' }}</strong>
                        <p>{{ $notification->message }}</p>
                    </div>
                    <div class="notification-card-actions">
                        @if($notification->action_url)
                        <a class="button secondary" href="{{ $notification->action_url }}" @if(!$notification->read_at) data-notification-open data-read-url="{{ route('notification.read', $notification->id) }}" @endif>Open</a>
                        @endif
                        <form method="POST" action="{{ route('notification.delete', $notification->id) }}" onsubmit="return confirm('Delete this notification?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button secondary notification-delete-action">Delete</button>
                        </form>
                    </div>
                </article>
                @empty
                <div class="notification-empty-state">You have no notifications yet.</div>
                @endforelse
            </div>

            @include('user.partials.record-pagination', ['records' => $notifications, 'paginationLabel' => 'Notification pages'])
        </section>
    </main>
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
</body>

</html>
