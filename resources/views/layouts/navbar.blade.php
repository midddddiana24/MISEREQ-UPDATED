<style>
    :root {
        --primary: #003d5c;
        --secondary: #2C3E50;
        --white: #FFFFFF;
        --shadow: rgba(0, 0, 0, 0.06);
    }

    .navbar {
        background: var(--white);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        padding: 0;
        position: sticky;
        top: 0;
        z-index: 999
    }

    .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center
    }

    .navbar-brand {
        display: flex;
        gap: .8rem;
        text-decoration: none;
        font-weight: 700;
        color: var(--primary)
    }

    .navbar-logo {
        width: 36px;
        height: 36px;
        background: var(--primary);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700
    }

    .navbar-brand-text {
        font-size: 1rem;
        color: var(--primary);
        font-weight: 700
    }

    .navbar-menu {
        display: flex;
        gap: 1.5rem;
        align-items: center
    }

    .navbar-welcome {
        color: var(--secondary);
        font-weight: 500
    }

    .navbar-link {
        color: var(--secondary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        padding: .5rem 1rem;
        border-radius: 8px;
        transition: all .25s ease
    }

    .navbar-link:hover {
        background: rgba(209, 136, 79, 0.08);
        color: var(--primary)
    }

    .navbar-link.active {
        background: linear-gradient(135deg, rgba(209, 136, 79, 0.12), rgba(209, 136, 79, 0.06));
        color: var(--primary);
        border-left: 3px solid var(--primary)
    }

    .navbar-btn {
        padding: .55rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none
    }

    .navbar-btn-primary {
        background: linear-gradient(135deg, var(--primary), #164875);
        color: #fff
    }

    .navbar-btn-logout {
        background: transparent;
        color: #e74c3c;
        border: 2px solid #e74c3c
    }

    .notification-bell-container {
        position: relative;
        display: flex;
        align-items: center
    }

    .notification-bell-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.25rem;
        padding: .4rem;
        border-radius: 50%
    }

    .navbar a:focus-visible,
    .navbar button:focus-visible {
        outline: 3px solid rgba(0, 61, 92, .55);
        outline-offset: 2px;
    }

    .app-skip-link {
        position: fixed;
        z-index: 9999;
        top: 8px;
        left: 8px;
        padding: 10px 14px;
        border-radius: 6px;
        background: #fff;
        color: var(--primary);
        font-weight: 700;
        transform: translateY(-150%);
    }

    .app-skip-link:focus {
        transform: translateY(0);
    }

    .notification-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: #fff;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem
    }

    @media(max-width:768px) {
        .navbar-container {
            padding: 1rem
        }

        .navbar-menu {
            gap: 1rem
        }

        .navbar-link {
            width: 100%;
            display: block
        }

        .navbar-btn {
            width: 100%
        }
    }
</style>

<nav class="navbar">
    <a href="#mainContent" class="app-skip-link">Skip to main content</a>
    <div class="navbar-container">
        <button class="navbar-hamburger" id="navbarToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarMenu"><span></span><span></span><span></span></button>
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="navbar-logo">M</div><span class="navbar-brand-text">MIS Service Request</span>
        </a>

        <div class="navbar-menu" id="navbarMenu">
            @if(auth()->check() && !auth()->user()->is_admin)
            <div class="notification-bell-container">
                <button class="notification-bell-btn" id="notificationBellBtn" aria-label="Notifications" aria-expanded="false" aria-controls="notificationDropdown">🔔<span class="notification-badge" id="notificationBadge">0</span></button>
                <div class="notification-dropdown" id="notificationDropdown" style="display:none">
                    <div class="notification-dropdown-header"><span>📬 Notifications</span><button id="clearAllBtn">Clear All</button></div>
                    <ul class="notification-list" id="notificationList">
                        <li class="notification-empty">No notifications</li>
                    </ul>
                </div>
            </div>
            @endif

            @if(auth()->check())
            <span class="navbar-welcome">Welcome, <strong>{{ auth()->user()->first_name }}</strong></span>
            @if(auth()->user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="navbar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Admin Dashboard</a>
            @else
            <a href="{{ route('dashboard') }}" class="navbar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">📋 Dashboard</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button type="submit" class="navbar-btn navbar-btn-logout">🚪 Logout</button></form>
            @else
            <a href="{{ route('login') }}" class="navbar-link">🔐 Login</a>
            <a href="{{ route('register') }}" class="navbar-btn navbar-btn-primary">📝 Register</a>
            @endif
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('navbarToggle');
        const menu = document.getElementById('navbarMenu');
        if (toggle && menu) {
            const setMenu = (open) => {
                toggle.classList.toggle('open', open);
                menu.classList.toggle('active', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            };
            toggle.addEventListener('click', () => setMenu(!menu.classList.contains('active')));
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && menu.classList.contains('active')) {
                    setMenu(false);
                    toggle.focus();
                }
            });
        }

        const bell = document.getElementById('notificationBellBtn');
        const dd = document.getElementById('notificationDropdown');
        const clearAll = document.getElementById('clearAllBtn');

        if (bell && dd) {
            const setDropdown = (open) => {
                dd.style.display = open ? 'block' : 'none';
                bell.setAttribute('aria-expanded', open ? 'true' : 'false');
            };
            bell.addEventListener('click', function(e) {
                e.stopPropagation();
                setDropdown(dd.style.display === 'none' || !dd.style.display);
            });
            document.addEventListener('click', function() {
                setDropdown(false);
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dd.style.display === 'block') {
                    setDropdown(false);
                    bell.focus();
                }
            });
            if (clearAll) clearAll.addEventListener('click', function() {
                const items = document.querySelectorAll('.notification-item.unread');
                items.forEach(i => i.classList.remove('unread'));
            });
        }
    });
</script>