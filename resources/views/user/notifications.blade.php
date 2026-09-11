<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications · MIS Service Request System</title>
    @include('user.partials.theme')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; }
        .notification-page-head {
            margin-bottom: 16px;
            padding-bottom: 15px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid var(--client-line);
        }
        .notification-page-head h1 { margin: 0 0 4px; color: var(--client-ink-900); font-size: clamp(20px, 2.4vw, 25px); line-height: 1.2; }
        .notification-page-head p { margin: 0; color: var(--client-ink-500); font-size: 12px; }
        .notification-page-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
        .notification-page-actions form { margin: 0; }
        .notification-page-list { display: grid; gap: 8px; }
        .notification-page-card {
            min-width: 0;
            padding: 13px 14px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 14px;
            border: 1px solid var(--client-line);
            border-radius: 8px;
            background: #fff;
        }
        .notification-page-card.is-unread { border-left: 3px solid var(--client-brand-700); background: #f6fafc; }
        .notification-page-copy { min-width: 0; }
        .notification-page-copy strong { display: block; color: var(--client-ink-900); font-size: 13px; line-height: 1.4; }
        .notification-page-copy p { margin: 4px 0 0; color: var(--client-ink-700); font-size: 12px; line-height: 1.55; overflow-wrap: anywhere; }
        .notification-page-copy small { margin-top: 6px; display: block; color: var(--client-ink-500); font-size: 10px; }
        .notification-card-actions { display: flex; align-items: center; gap: 5px; }
        .notification-card-actions form { margin: 0; }
        .notification-delete-action { color: #953b3b !important; border-color: #dfcaca !important; background: #fff !important; }
        .notification-delete-action:hover { background: #fff4f4 !important; }
        .notification-empty-state { padding: 38px 18px; color: var(--client-ink-500); border: 1px dashed var(--client-line-strong); border-radius: 8px; text-align: center; background: var(--client-surface-soft); }
        .notification-pagination { margin-top: 16px; }
        @media (max-width: 640px) {
            .notification-page-head { flex-direction: column; }
            .notification-page-actions { width: 100%; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .notification-page-actions form,
            .notification-page-actions .button { width: 100%; }
            .notification-page-card { grid-template-columns: 1fr; gap: 10px; }
            .notification-card-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .notification-card-actions > *,
            .notification-card-actions form,
            .notification-card-actions .button { width: 100%; }
        }
        @media (max-width: 360px) {
            .notification-page-actions,
            .notification-card-actions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @include('partials.header')

    <main class="page">
        @include('user.partials.navigation')

        <section class="panel" aria-labelledby="notifications-title">
            <header class="notification-page-head">
                <div>
                    <h1 id="notifications-title">Notifications</h1>
                    <p>Updates about your service requests, incidents, and maintenance records.</p>
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
                            <strong>{{ $notification->subject ?: 'Request update' }}</strong>
                            <p>{{ $notification->message }}</p>
                            <small>{{ $notification->created_at->format('M d, Y · h:i A') }}</small>
                        </div>
                        <div class="notification-card-actions">
                            @if($notification->action_url)
                                <a class="button secondary" href="{{ $notification->action_url }}">Open</a>
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

            <div class="notification-pagination">{{ $notifications->links() }}</div>
        </section>
    </main>
</body>
</html>
