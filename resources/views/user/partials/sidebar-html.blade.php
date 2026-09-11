@php use Illuminate\Support\Facades\Auth; @endphp

<aside class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle" title="Collapse sidebar">◀</button>

    <div class="sidebar-header">
        <div class="logo-section">
            <div class="logo">M</div>
            <div class="logo-text">MIS Service Request</div>
        </div>
    </div>

    <div class="user-info">
        <div class="user-avatar">
            {{ substr(Auth::user()->first_name, 0, 1) }}{{ substr(Auth::user()->last_name, 0, 1) }}
        </div>
        <div class="user-details">
            <h4>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
            <p>{{ Auth::user()->office ?? 'User' }}</p>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="menu-icon">📊</span>
                <span class="menu-label">Dashboard</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('requests.create') }}" class="{{ request()->routeIs('requests.create') ? 'active' : '' }}">
                <span class="menu-icon">➕</span>
                <span class="menu-label">New Request</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('incident-reports.index') }}" class="{{ request()->routeIs('incident-reports.*') ? 'active' : '' }}">
                <span class="menu-icon">IR</span>
                <span class="menu-label">ICT Incident</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('pm-checklists.index') }}" class="{{ request()->routeIs('pm-checklists.*') ? 'active' : '' }}">
                <span class="menu-icon">PM</span>
                <span class="menu-label">Preventive Maintenance</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('user.messenger') }}" class="{{ request()->routeIs('user.messenger') ? 'active' : '' }}">
                <span class="menu-icon">💬</span>
                <span class="menu-label">Messages</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('user.settings') }}" class="{{ request()->routeIs('user.settings') ? 'active' : '' }}">
                <span class="menu-icon">⚙️</span>
                <span class="menu-label">Settings</span>
            </a>
        </li>
    </ul>
</aside>
