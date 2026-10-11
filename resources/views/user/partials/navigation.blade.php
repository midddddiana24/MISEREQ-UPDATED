@php
$clientActionCount = auth()->check()
? auth()->user()->serviceRequests()
->where(function ($query) {
$query->whereIn('status', ['awaiting_requester_info', 'awaiting_confirmation'])
->orWhere(function ($scheduleQuery) {
$scheduleQuery->whereIn('status', \App\Models\ServiceRequest::OPEN_STATUSES)
->whereNotNull('scheduled_visit_at')
->where(function ($responseQuery) {
$responseQuery->whereNull('schedule_response')->orWhere('schedule_response', 'pending');
});
});
})->count()
: 0;
$clientCurrentPage = match (true) {
request()->routeIs('dashboard') => 'Dashboard',
request()->routeIs('requests.*') => 'Service Requests',
request()->routeIs('incident-reports.*') => 'ICT Incidents',
request()->routeIs('pm-checklists.*') => 'Preventive Maintenance',
request()->routeIs('user.messenger') => 'Messages',
request()->routeIs('user.notifications.index') => 'Notifications',
default => 'Settings',
};
@endphp
<style>
    .nav-shell {
        margin-bottom: 18px;
    }

    .client-mobile-alerts,
    .client-menu-group { display: none; }

    .notification-wrap {
        position: relative;
        margin-left: auto;
    }

    .notification-toggle {
        min-height: 44px;
        padding: 10px 12px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #405764;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        font: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .notification-toggle svg {
        width: 17px;
        height: 17px;
    }

    .notification-toggle:hover,
    .notification-toggle[aria-expanded="true"] {
        color: #123f63;
        border-color: #d4e3ec;
        background: #edf5fa;
    }

    .notification-count,
    .nav-count {
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border-radius: 10px;
        background: #b42318;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
    }

    .notification-count[hidden],
    .nav-count[hidden] {
        display: none;
    }

    .notification-menu {
        position: absolute;
        z-index: 50;
        top: calc(100% + 7px);
        right: 0;
        width: min(380px, calc(100vw - 30px));
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid #d7e2e9;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 16px 38px rgba(26, 56, 75, .14);
        box-sizing: border-box;
        max-width: calc(100vw - 24px);
        overflow-x: hidden;
    }

    .notification-menu[hidden] {
        display: none;
    }

    .notification-header {
        flex: 0 0 auto;
        padding: 10px 12px;
        display: flex;
        align-items: stretch;
        flex-direction: column;
        gap: 7px;
        border-bottom: 1px solid #e7edf1;
    }

    .notification-header strong {
        color: #233b4b;
        font-size: 14px;
    }

    .notification-header-actions {
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;
        gap: 6px;
    }

    .notification-read-all,
    .notification-clear {
        min-height: 44px;
        padding: 9px 11px;
        border: 1px solid #cad8e1;
        border-radius: 5px;
        background: #fff;
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .notification-read-all {
        color: #236d9e;
    }

    .notification-clear {
        color: #9b3434;
        border-color: #e2cccc;
    }

    .notification-list {
        min-height: 0;
        flex: 1 1 auto;
        width: 100%;
        max-width: 100%;
        max-height: 340px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .notification-item {
        min-width: 0;
        max-width: 100%;
        padding: 0;
        display: flex;
        align-items: stretch;
        flex-direction: column;
        color: #445864;
        border-bottom: 1px solid #dfe7ec;
        text-decoration: none;
    }

    .notification-item:last-child {
        border-bottom: 0;
    }

    .notification-item:hover {
        background: #f5f9fb;
    }

    .notification-item.is-unread {
        background: #eef7fc;
        box-shadow: inset 3px 0 #236d9e;
    }

    .notification-item-main {
        min-width: 0;
        padding: 11px 12px 6px;
        display: block;
        color: inherit;
        text-decoration: none;
        overflow: hidden;
    }

    .notification-item strong {
        display: block;
        color: #233b4b;
        font-size: 14px;
        line-height: 1.35;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .notification-item span {
        max-width: 100%;
        margin-top: 3px;
        display: block;
        font-size: 14px;
        line-height: 1.45;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .notification-item small {
        max-width: 100%;
        margin-top: 5px;
        display: inline-block;
        color: #586d78;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .notification-item small + small::before {
        content: "\00b7";
        margin: 0 6px;
        color: #8a9aa3;
    }

    .notification-delete {
        min-height: 44px;
        margin: 0 12px 9px auto;
        padding: 5px 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #8b4b4b;
        border: 1px solid #e2cccc;
        border-radius: 5px;
        background: #fff;
        font: 700 12px/1.2 "Segoe UI", sans-serif;
        cursor: pointer;
    }

    .notification-delete:hover {
        color: #8f2525;
        border-color: #e6caca;
        background: #fff1f1;
    }

    .notification-empty {
        margin: 0;
        padding: 24px 15px;
        color: #586d78;
        font-size: 14px;
        text-align: center;
    }

    .nav-shell .nav-tabs {
        margin: 0;
        padding: 4px;
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        border: 1px solid #dce4e9;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(25, 55, 74, .045);
    }

    .nav-shell .nav-tabs a {
        min-height: 44px;
        padding: 10px 12px;
        display: inline-flex;
        align-items: center;
        color: #4b606c !important;
        border: 1px solid transparent;
        border-radius: 5px !important;
        background: transparent !important;
        box-shadow: none !important;
        font-size: 14px;
        font-weight: 680;
        line-height: 1.2;
        text-decoration: none;
        white-space: nowrap;
        transition: color .15s ease, background .15s ease, border-color .15s ease;
    }

    .nav-shell .nav-tabs a:hover {
        color: #123f63 !important;
        border-color: #d9e7ef;
        background: #f2f7fa !important;
    }

    .nav-shell .nav-tabs a.active {
        color: #123f63 !important;
        border-color: #d4e4ed;
        background: #eaf3f8 !important;
        box-shadow: inset 0 -2px #1f628e !important;
    }

    .nav-shell .nav-tabs .notification-item-main {
        width: auto !important;
        min-height: 0;
        padding: 11px 12px 6px !important;
        display: block !important;
        color: inherit !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        font-weight: inherit;
        white-space: normal;
    }

    .nav-shell .nav-tabs .notification-item-main:hover {
        color: inherit !important;
        border-color: transparent !important;
        background: transparent !important;
    }

    .nav-shell .client-mobile-logout { display: none; }

    .nav-toggle {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        min-height: 46px;
        margin-bottom: 10px;
        padding: 10px 12px;
        color: #294152;
        border: 1px solid #d1dce4;
        border-radius: 9px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(25, 65, 91, .05);
        cursor: pointer;
    }

    .nav-toggle:hover,
    .nav-toggle.is-open {
        border-color: #a9c3d3;
        background: #f5f9fb;
    }

    .nav-toggle-copy {
        display: inline-flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        text-align: left;
    }

    .nav-toggle-copy strong {
        font-size: 14px;
        line-height: 1.1;
    }

    .nav-toggle-copy small {
        color: #586d78;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .nav-toggle-icon {
        width: 20px;
        height: 20px;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #1f6fa8;
    }

    .nav-toggle-icon svg {
        display: block;
        width: 20px;
        height: 20px;
    }

    .nav-toggle-icon .nav-close-icon,
    .nav-toggle.is-open .nav-toggle-icon .nav-menu-icon { display: none; }
    .nav-toggle.is-open .nav-toggle-icon .nav-close-icon { display: block; }

    @media (max-width: 860px) {
        .client-nav-topline {
            margin-bottom: 10px;
            display: flex;
            align-items: stretch;
            gap: 8px;
        }

        .nav-shell .client-nav-topline .nav-toggle {
            min-width: 0;
            flex: 1 1 auto;
            margin-bottom: 0;
        }

        .nav-toggle-copy { min-width: 0; }
        .nav-toggle-copy strong { overflow-wrap: anywhere; }

        .nav-shell .client-mobile-alerts {
            position: relative;
            min-width: 76px;
            min-height: 46px;
            padding: 4px 9px;
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1px;
            color: #174f75;
            border: 1px solid #d1dce4;
            border-radius: 9px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(25, 65, 91, .05);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }

        .client-mobile-alerts:hover,
        .client-mobile-alerts:focus-visible,
        .client-mobile-alerts[aria-current="page"] {
            border-color: #a9c3d3;
            background: #edf5fa;
        }

        .client-mobile-alerts svg { width: 17px; height: 17px; }
        .client-mobile-alerts .notification-count {
            position: absolute;
            top: -6px;
            right: -5px;
            margin: 0;
        }

        .nav-shell .client-menu-group {
            margin: 5px 3px 0;
            padding: 10px 8px 2px;
            display: block;
            color: #586d78;
            border-top: 1px solid #dce4e9;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .nav-shell .client-mobile-logout {
            display: block;
            width: 100%;
            margin: 4px 0 0;
            padding-top: 7px;
            border-top: 1px solid #dce4e9;
        }

        .nav-shell .client-mobile-logout button {
            width: 100%;
            min-height: 44px;
            padding: 10px 11px;
            color: #174f75;
            border: 1px solid transparent;
            border-radius: 5px;
            background: transparent;
            font: 700 14px/1.2 Arial, Helvetica, sans-serif;
            text-align: left;
            cursor: pointer;
        }

        .nav-shell .client-mobile-logout button:hover,
        .nav-shell .client-mobile-logout button:focus-visible {
            border-color: #d9e7ef;
            background: #f2f7fa;
        }

        .nav-toggle {
            display: inline-flex !important;
        }

        .nav-shell .nav-tabs {
            display: none !important;
            padding: 7px !important;
            flex-direction: column !important;
            flex-wrap: nowrap !important;
            gap: 4px !important;
            border-radius: 8px !important;
        }

        .nav-shell .nav-tabs.open {
            display: flex !important;
            max-height: calc(100dvh - 170px);
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .nav-shell .nav-tabs a,
        .nav-shell .nav-tabs .active {
            width: 100% !important;
            min-height: 44px;
            padding: 10px 11px !important;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .nav-shell .nav-tabs a.active {
            color: #fff !important;
            border-color: #1f628e !important;
            background: #1f628e !important;
            box-shadow: none !important;
        }

        .notification-wrap {
            display: none;
        }

        .notification-toggle {
            width: 100%;
            min-height: 44px;
            justify-content: flex-start;
        }

        .notification-count {
            margin-left: auto;
        }

        .notification-menu {
            position: fixed;
            z-index: 200;
            top: 78px;
            right: 12px;
            left: auto;
            width: min(380px, calc(100vw - 24px));
            max-height: min(68vh, 520px);
        }

        .notification-list {
            max-height: min(52vh, 400px);
        }
    }

    @media (max-width: 560px) {
        .nav-shell {
            margin-bottom: 14px;
        }

        .nav-toggle {
            border-radius: 7px;
        }

        .notification-toggle {
            min-height: 44px;
        }

        .notification-menu {
            position: fixed;
            top: 66px;
            right: 10px;
            left: auto;
            width: min(340px, calc(100vw - 20px));
            max-width: 340px;
            max-height: min(60dvh, 410px);
        }

        .notification-list {
            max-height: min(46dvh, 300px);
        }

        .notification-header {
            padding: 9px 10px;
            align-items: center;
            flex-direction: row;
            justify-content: space-between;
            gap: 8px;
        }

        .notification-header-actions {
            width: auto;
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: 4px;
        }

        .notification-read-all,
        .notification-clear {
            padding: 8px 9px;
        }

        .notification-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
        }

        .notification-item-main {
            padding: 9px 10px !important;
        }

        .notification-delete {
            min-width: 44px;
            margin: 6px 8px 6px 0;
            padding: 5px 8px;
        }

        .notification-empty {
            padding: 16px 12px;
        }
    }

    @media (max-width: 390px) {
        .nav-shell {
            margin-bottom: 10px;
        }

        .nav-toggle {
            min-height: 44px;
            padding: 8px 10px;
        }

        .client-nav-topline { margin-bottom: 7px; }

        .nav-toggle-copy strong {
            font-size: 14px;
        }

        .nav-shell .nav-tabs {
            padding: 5px !important;
        }

        .nav-shell .nav-tabs a,
        .notification-toggle {
            min-height: 44px;
            padding: 8px 9px !important;
            font-size: 14px;
        }

        .notification-menu {
            top: 60px;
            right: 10px;
            left: auto;
            width: min(340px, calc(100vw - 20px));
            max-height: min(60dvh, 380px);
        }

        .notification-list {
            max-height: min(44dvh, 260px);
        }
    }

    @media (max-height: 500px) and (max-width: 860px) {
        .notification-menu {
            top: 58px;
            max-height: calc(100dvh - 68px);
        }

        .notification-list {
            max-height: calc(100dvh - 180px);
        }
    }
</style>

<div class="nav-shell">
    <div class="client-nav-topline">
        <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="clientNavMenu">
            <span class="nav-toggle-copy">
                <strong>{{ $clientCurrentPage }}</strong>
                <small>Open menu</small>
            </span>
            <span class="nav-toggle-icon" aria-hidden="true">
                <svg class="nav-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18" /></svg>
                <svg class="nav-close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 5l14 14M19 5L5 19" /></svg>
            </span>
        </button>
        <a class="client-mobile-alerts" data-notification-mobile-link href="{{ route('user.notifications.index') }}" aria-label="Notifications" @if(request()->routeIs('user.notifications.index')) aria-current="page" @endif>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                <path d="M10 21h4" />
            </svg>
            <span aria-hidden="true">Alerts</span>
            <span class="notification-count" data-notification-mobile-count hidden aria-hidden="true">0</span>
        </a>
    </div>

    <nav class="nav-tabs" id="clientNavMenu" data-nav-menu aria-label="Client navigation" tabindex="-1">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
        <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.*') ? 'active' : '' }}" @if(request()->routeIs('requests.*')) aria-current="page" @endif>Service Requests @if($clientActionCount)<span class="nav-count" aria-label="{{ $clientActionCount }} requests need attention">{{ $clientActionCount > 99 ? '99+' : $clientActionCount }}</span>@endif</a>
        <a href="{{ route('incident-reports.index') }}" class="{{ request()->routeIs('incident-reports.*') ? 'active' : '' }}" @if(request()->routeIs('incident-reports.*')) aria-current="page" @endif>ICT Incidents</a>
        <a href="{{ route('pm-checklists.index') }}" class="{{ request()->routeIs('pm-checklists.*') ? 'active' : '' }}" @if(request()->routeIs('pm-checklists.*')) aria-current="page" @endif>Preventive Maintenance</a>
        <a href="{{ route('user.messenger') }}" class="{{ request()->routeIs('user.messenger') ? 'active' : '' }}" @if(request()->routeIs('user.messenger')) aria-current="page" @endif>Messages <span class="nav-count" data-message-count hidden>0</span></a>
        <span class="client-menu-group">Account</span>
        <a href="{{ route('user.settings') }}" class="{{ request()->routeIs('user.settings') || request()->routeIs('user.profile') || request()->routeIs('user.security') || request()->routeIs('settings.*') ? 'active' : '' }}" @if(request()->routeIs('user.settings') || request()->routeIs('user.profile') || request()->routeIs('user.security') || request()->routeIs('settings.*')) aria-current="page" @endif>Settings</a>
        <div class="notification-wrap">
            <button type="button" class="notification-toggle" data-notification-toggle aria-expanded="false" aria-controls="clientNotificationMenu">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                    <path d="M10 21h4" />
                </svg>
                <span>Notifications</span>
                <span class="notification-count" data-notification-count hidden>0</span>
            </button>
            <section class="notification-menu" id="clientNotificationMenu" data-notification-menu hidden aria-label="Notifications">
                <div class="notification-header">
                    <strong>Notifications</strong>
                    <div class="notification-header-actions">
                        <button type="button" class="notification-read-all" data-notification-read-all>Mark all read</button>
                        <button type="button" class="notification-clear" data-notification-clear>Clear all</button>
                    </div>
                </div>
                <div class="notification-list" data-notification-list>
                    <p class="notification-empty">Loading notifications...</p>
                </div>
                <a href="{{ route('user.notifications.index') }}" class="notification-read-all" style="display:block;padding:10px 14px;text-align:center;">View all notifications</a>
            </section>
        </div>
        <form class="client-mobile-logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </nav>
    <div id="notificationConfig" hidden
        data-csrf="{{ csrf_token() }}"
        data-feed-url="{{ route('user.notifications') }}"
        data-read-all-url="{{ route('notifications.read-all') }}"
        data-clear-url="{{ route('notifications.clear') }}"
        data-notification-base-url="{{ url('/api/notifications') }}"></div>
</div>

<script>
    (function() {
        const toggle = document.querySelector('[data-nav-toggle]');
        const menu = document.querySelector('[data-nav-menu]');
        if (!toggle || !menu || toggle.dataset.bound === 'true') return;

        toggle.dataset.bound = 'true';

        function setMenu(open) {
            menu.classList.toggle('open', open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            const label = toggle.querySelector('small');
            if (label) label.textContent = open ? 'Close menu' : 'Open menu';
            menu.style.maxHeight = open && window.innerWidth <= 860
                ? Math.max(100, Math.floor(window.innerHeight - menu.getBoundingClientRect().top - 12)) + 'px'
                : '';
        }

        toggle.addEventListener('click', function() {
            setMenu(!menu.classList.contains('open'));
        });

        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 860 && !menu.contains(event.target) && !toggle.contains(event.target)) setMenu(false);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && menu.classList.contains('open')) {
                setMenu(false);
                toggle.focus();
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 860) setMenu(false);
            else if (menu.classList.contains('open')) setMenu(true);
        });
    })();

    (function() {
        const toggle = document.querySelector('[data-notification-toggle]');
        const menu = document.querySelector('[data-notification-menu]');
        const list = document.querySelector('[data-notification-list]');
        const count = document.querySelector('[data-notification-count]');
        const mobileCount = document.querySelector('[data-notification-mobile-count]');
        const mobileLink = document.querySelector('[data-notification-mobile-link]');
        const messageCount = document.querySelector('[data-message-count]');
        const readAll = document.querySelector('[data-notification-read-all]');
        const clearAll = document.querySelector('[data-notification-clear]');
        if (!toggle || !menu || !list || toggle.dataset.bound === 'true') return;

        toggle.dataset.bound = 'true';
        const config = document.getElementById('notificationConfig');
        const csrf = config.dataset.csrf;
        const feedUrl = config.dataset.feedUrl;
        const readAllUrl = config.dataset.readAllUrl;
        const clearUrl = config.dataset.clearUrl;
        const notificationBaseUrl = config.dataset.notificationBaseUrl;
        let latestFeedRequest = 0;

        function setBadge(element, value) {
            if (!element) return;
            const total = Number(value || 0);
            element.textContent = total > 99 ? '99+' : String(total);
            element.hidden = total < 1;
        }

        function makeItem(notification) {
            const item = document.createElement('div');
            item.className = 'notification-item' + (notification.read ? '' : ' is-unread');

            const link = document.createElement('a');
            link.className = 'notification-item-main';
            link.href = notification.url || '#';

            const title = document.createElement('strong');
            title.textContent = notification.title || 'Request update';
            const message = document.createElement('span');
            message.textContent = notification.message || '';
            const time = document.createElement('small');
            time.textContent = notification.time || '';
            const delivery = document.createElement('small');
            delivery.textContent = notification.delivery || '';
            link.append(title, message, time, delivery);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'notification-delete';
            remove.setAttribute('aria-label', 'Delete notification');
            remove.title = 'Delete notification';
            remove.textContent = 'Delete';

            link.addEventListener('click', function(event) {
                if (notification.read || !notification.id) return;
                event.preventDefault();
                fetch(notificationBaseUrl + '/' + notification.id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).finally(function() {
                    window.location.href = link.href;
                });
            });

            remove.addEventListener('click', function() {
                if (!notification.id || !window.confirm('Delete this notification?')) return;
                remove.disabled = true;
                fetch(notificationBaseUrl + '/' + notification.id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).then(function(response) {
                    if (!response.ok) throw new Error('Unable to delete notification');
                    loadNotifications();
                }).catch(function() {
                    remove.disabled = false;
                    window.alert('Unable to delete the notification. Please try again.');
                });
            });

            item.append(link, remove);
            return item;
        }

        function loadNotifications() {
            const requestNumber = ++latestFeedRequest;
            fetch(feedUrl, {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error('Unable to load');
                    return response.json();
                })
                .then(function(data) {
                    if (requestNumber !== latestFeedRequest) return;
                    list.replaceChildren();
                    const notifications = Array.isArray(data.notifications) ? data.notifications : [];
                    if (!notifications.length) {
                        const empty = document.createElement('p');
                        empty.className = 'notification-empty';
                        empty.textContent = 'No notifications yet.';
                        list.appendChild(empty);
                    } else {
                        notifications.forEach(function(notification) {
                            list.appendChild(makeItem(notification));
                        });
                    }
                    setBadge(count, data.total_unread);
                    setBadge(mobileCount, data.total_unread);
                    if (mobileLink) {
                        const total = Number(data.total_unread || 0);
                        mobileLink.setAttribute('aria-label', total > 0
                            ? 'Notifications, ' + total + ' unread items'
                            : 'Notifications, no unread items');
                    }
                    setBadge(messageCount, data.unread_messages);
                })
                .catch(function() {
                    if (requestNumber !== latestFeedRequest) return;
                    list.innerHTML = '<p class="notification-empty">Notifications are temporarily unavailable.</p>';
                });
        }

        toggle.addEventListener('click', function() {
            const open = menu.hidden;
            menu.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) loadNotifications();
        });

        if (readAll) readAll.addEventListener('click', function() {
            fetch(readAllUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            }).then(function(response) {
                if (response.ok) loadNotifications();
            });
        });

        if (clearAll) clearAll.addEventListener('click', function() {
            if (!window.confirm('Clear all notifications? This cannot be undone.')) return;
            clearAll.disabled = true;
            fetch(clearUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            }).then(function(response) {
                if (!response.ok) throw new Error('Unable to clear notifications');
                loadNotifications();
            }).catch(function() {
                window.alert('Unable to clear notifications. Please try again.');
            }).finally(function() {
                clearAll.disabled = false;
            });
        });

        document.addEventListener('click', function(event) {
            if (!menu.hidden && !menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !menu.hidden) {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });

        loadNotifications();
        window.setInterval(function() {
            if (!document.hidden) loadNotifications();
        }, 10000);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) loadNotifications();
        });
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) loadNotifications();
        });
    })();
</script>
