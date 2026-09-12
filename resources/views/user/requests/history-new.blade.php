<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Service Requests History - MIS Service Request System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f4f8fc;
            overflow-x: hidden;
        }

        .site-header {
            background: #1f6fa8;
            border-bottom: 4px solid #f4c430;
            color: #fff;
        }

        .header-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 4px;
        }

        .brand small {
            display: block;
            font: 700 15px Georgia, "Times New Roman", serif;
        }

        .brand h1 {
            font: 700 clamp(20px, 3vw, 32px) Georgia, "Times New Roman", serif;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .brand p {
            font: 700 16px Georgia, "Times New Roman", serif;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .button,
        button.button {
            display: inline-block;
            padding: 9px 13px;
            border: 1px solid #1f6fa8;
            border-radius: 3px;
            background: #1f6fa8;
            color: #fff;
            font: 700 14px Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: #fff;
            color: #1f6fa8;
        }

        .button:hover {
            background: #164875;
        }

        .button.secondary:hover {
            background: #f0f0f0;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 22px clamp(14px, 4vw, 20px) 48px;
        }

        .nav-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .nav-shell {
            margin-bottom: 18px;
        }

        .nav-toggle {
            display: none;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid #cfd8e3;
            border-radius: 10px;
            background: #fff;
            color: #1f3b57;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            margin-bottom: 12px;
        }

        .nav-toggle-icon {
            display: inline-grid;
            gap: 4px;
        }

        .nav-toggle-icon span {
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: #1f6fa8;
        }

        .nav-tabs a {
            padding: 8px 14px;
            border-radius: 3px;
            color: #2d123d;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-tabs a.active {
            background: #2f7fbd;
            color: #fff;
        }

        .nav-tabs a:hover {
            background: #e5e7eb;
        }

        .panel {
            padding: 22px;
            background: #fff;
            border: 1px solid #d7dce2;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .section-header {
            background: #f3f4f6;
            padding: 16px 22px;
            margin: -22px -22px 16px -22px;
            border-radius: 6px 6px 0 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header-content h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .header-content small {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin: 4px 0 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 28px;
        }

        .stat-card {
            padding: 16px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid #d7dce2;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .stat-label {
            display: block;
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-value {
            color: #111827;
            font-size: 30px;
            font-weight: 700;
        }

        .stat-hint {
            margin-top: 6px;
            color: #6b7280;
            font-size: 12px;
        }

        .filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 14px;
            border: 1px solid #d7dce2;
            background: #fff;
            border-radius: 3px;
            color: #2d123d;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background: #e5e7eb;
        }

        .filter-btn.active {
            background: #2f7fbd;
            color: #fff;
            border-color: #2f7fbd;
        }

        .search-container {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            max-width: 300px;
            padding: 8px 12px;
            border: 1px solid #d7dce2;
            border-radius: 3px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #1f6fa8;
            box-shadow: 0 0 0 3px rgba(31, 111, 168, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table thead {
            background: #f3f4f6;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tbody tr:hover {
            background: #f9fafb;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            background: #f3f4f6;
            color: #1f6fa8;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-submitted {
            background: #f1f5f9;
            color: #475569;
        }

        .status-processing {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-awaiting_confirmation {
            background: #ede9fe;
            color: #6d28d9;
        }

        .status-awaiting_requester_info {
            background: #fef3c7;
            color: #92400e;
        }

        .status-reopened {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-escalated {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #374151;
        }

        .status-closed {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .action-link {
            color: #1f6fa8;
            text-decoration: none;
            font-weight: 600;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .request-actions {
            min-width: 190px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .request-action {
            min-height: 36px;
            padding: 7px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #174f75;
            border: 1px solid #c5d3dc;
            border-radius: 6px;
            background: #fff;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
        }

        .request-action:hover {
            border-color: #8fb4ca;
            background: #eef6fb;
            text-decoration: none;
        }

        .request-action.primary {
            color: #fff;
            border-color: #236d9e;
            background: #236d9e;
        }

        .request-action.primary:hover {
            border-color: #174f75;
            background: #174f75;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 4px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #d7dce2;
            border-radius: 3px;
            text-decoration: none;
            color: #1f6fa8;
            font-size: 14px;
        }

        .pagination a:hover {
            background: #e5e7eb;
        }

        .pagination .active {
            background: #1f6fa8;
            color: #fff;
            border-color: #1f6fa8;
        }

        @media (max-width: 1024px) {
            .panel {
                padding: 20px;
            }
        }

        @media (max-width: 860px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-toggle {
                display: inline-flex;
            }

            .nav-tabs {
                display: none;
                flex-direction: column;
                gap: 6px;
                padding: 12px;
                border: 1px solid #d7dce2;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                margin-bottom: 0;
            }

            .nav-tabs.open {
                display: flex;
            }

            .nav-tabs a {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters {
                gap: 4px;
            }

            .filter-btn {
                flex: 1;
                min-width: 60px;
                padding: 6px 10px;
                font-size: 12px;
            }

            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 8px;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 18px;
            }

            .section-header {
                padding: 14px 18px;
                margin: -18px -18px 16px -18px;
            }

            .search-input {
                max-width: 100%;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            table thead {
                display: none;
            }

            table,
            table tbody,
            table tr,
            table td {
                display: block;
                width: 100%;
            }

            table tbody tr {
                padding: 14px 0;
                border-bottom: 1px solid #e5e7eb;
            }

            table td {
                border-bottom: 0;
                padding: 6px 0;
            }

            table td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 3px;
                color: #6b7280;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            table td[data-label="Actions"]::before {
                margin-bottom: 7px;
            }

            .request-actions {
                min-width: 0;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .request-action {
                width: 100%;
                min-height: 42px;
            }
        }
    </style>
    @include('user.partials.theme')
    <style>
        .request-list-note {
            margin: 0 0 7px;
            color: var(--client-ink-500);
            font-size: 11px;
        }

        .request-history-scroll {
            max-height: min(62vh, 620px);
            overflow: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            border: 1px solid var(--client-line);
            border-radius: 8px;
            background: #fff;
        }

        .request-history-scroll:focus-visible {
            outline: 3px solid rgba(35, 109, 158, .28);
            outline-offset: 2px;
        }

        .request-history-table thead {
            position: sticky;
            z-index: 2;
            top: 0;
        }

        @media (max-width: 1024px) {
            .request-history-scroll {
                max-height: min(58vh, 560px);
                padding: 9px;
            }

            .request-history-table,
            .request-history-table tbody {
                display: block !important;
                width: 100%;
            }

            .request-history-table thead {
                display: none !important;
            }

            .request-history-table tbody {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .request-history-table tbody tr {
                min-width: 0;
                padding: 13px !important;
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-content: start;
                gap: 8px 12px;
                border: 1px solid var(--client-line) !important;
                border-radius: 8px;
                background: #fff;
            }

            .request-history-table tbody td {
                min-width: 0;
                width: auto !important;
                padding: 0 !important;
                display: block !important;
                border: 0 !important;
                overflow-wrap: anywhere;
            }

            .request-history-table tbody td::before {
                content: attr(data-label);
                margin-bottom: 3px;
                display: block !important;
                color: var(--client-ink-500);
                font-size: 9px;
                font-weight: 800;
                letter-spacing: .045em;
                line-height: 1.3;
                text-transform: uppercase;
            }

            .request-history-table tbody td[data-label="Control Number"],
            .request-history-table tbody td[data-label="Actions"] {
                grid-column: 1 / -1;
            }

            .request-history-table .request-actions {
                min-width: 0;
                display: flex;
                gap: 5px;
            }

            .request-history-table .request-action {
                width: auto;
                min-height: 38px;
                flex: 1 1 auto;
                padding: 7px 8px;
            }
        }

        @media (max-width: 640px) {
            .request-history-scroll {
                max-height: min(54vh, 470px);
                padding: 7px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }

            .stat-card {
                padding: 12px !important;
            }

            .stat-card .stat-value {
                font-size: 20px !important;
            }

            .stat-card .stat-hint {
                display: none;
            }

            .filters {
                margin: 0 -2px 14px !important;
                padding: 2px 2px 5px;
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto;
                scrollbar-width: thin;
            }

            .filters .filter-btn {
                flex: 0 0 auto !important;
                min-height: 38px;
                padding: 8px 11px !important;
                white-space: nowrap;
            }

            .search-container {
                margin-bottom: 14px !important;
            }

            .search-input {
                width: 100%;
                max-width: none !important;
            }

            .request-history-table tbody {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .request-history-table tbody tr {
                padding: 12px !important;
            }

            .request-history-table tbody td[data-label="Category"],
            .request-history-table tbody td[data-label="Date Submitted"] {
                display: none !important;
            }

            .request-history-table tbody td[data-label="Status"] {
                grid-column: 1 / -1;
            }

            .request-history-table .request-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .request-history-table .request-action {
                width: 100%;
                min-height: 40px;
            }
        }
    </style>
</head>

<body>
    @php
    $user = auth()->user();
    $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
    ? asset('images/wvsu-janiuay-logo.png')
    : asset('images/wvsu-logo.png');
    @endphp

    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        <div class="panel">
            <div class="section-header">
                <div class="header-content">
                    <div>
                        <h1>Service Requests History</h1>
                        <small>Request records &middot; West Visayas State University</small>
                    </div>
                    <a href="{{ route('requests.create') }}" class="button">+ New Request</a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Total Requests</span>
                    <span class="stat-value">{{ auth()->user()->serviceRequests()->count() }}</span>
                    <div class="stat-hint">All service requests in your history.</div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Awaiting Confirmation</span>
                    <span class="stat-value">{{ auth()->user()->serviceRequests()->where('status', 'awaiting_confirmation')->count() }}</span>
                    <div class="stat-hint">Technician work ready for your review.</div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Active</span>
                    <span class="stat-value">{{ auth()->user()->serviceRequests()->whereIn('status', \App\Models\ServiceRequest::OPEN_STATUSES)->count() }}</span>
                    <div class="stat-hint">Requests currently in the MIS workflow.</div>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Closed</span>
                    <span class="stat-value">{{ auth()->user()->serviceRequests()->whereIn('status', ['completed', 'closed'])->count() }}</span>
                    <div class="stat-hint">Completed and confirmed request records.</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <a href="{{ route('requests.index', ['status' => 'all', 'search' => request('search')]) }}" class="filter-btn {{ request('status', 'all') === 'all' ? 'active' : '' }}">All</a>
                <a href="{{ route('requests.index', ['status' => 'active', 'search' => request('search')]) }}" class="filter-btn {{ request('status') === 'active' ? 'active' : '' }}">Active</a>
                <a href="{{ route('requests.index', ['status' => 'awaiting_confirmation', 'search' => request('search')]) }}" class="filter-btn {{ request('status') === 'awaiting_confirmation' ? 'active' : '' }}">Confirm Resolution</a>
                <a href="{{ route('requests.index', ['status' => 'closed', 'search' => request('search')]) }}" class="filter-btn {{ request('status') === 'closed' ? 'active' : '' }}">Closed</a>
                <a href="{{ route('requests.index', ['status' => 'cancelled', 'search' => request('search')]) }}" class="filter-btn {{ request('status') === 'cancelled' ? 'active' : '' }}">Cancelled</a>
            </div>

            <!-- Search -->
            <div class="search-container">
                <label for="searchInput" class="visually-hidden">Search by control number</label>
                <input type="search" id="searchInput" class="search-input" placeholder="Search by control number" oninput="searchRequests()">
            </div>

            <!-- Scrollable request records -->
            @if($requests->count())
            <p class="request-list-note" id="requestListHint">Showing {{ $requests->count() }} request{{ $requests->count() === 1 ? '' : 's' }} on this page. Scroll inside the list to see more.</p>
            @endif
            <div class="request-history-scroll" tabindex="0" role="region" aria-label="Service request records" @if($requests->count()) aria-describedby="requestListHint" @endif>
                <table class="request-history-table">
                    <thead>
                        <tr>
                            <th>Control Number</th>
                            <th>Category</th>
                            <th>Date Submitted</th>
                            <th>Linked Records</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr data-request-id="{{ $request->id }}" data-status="{{ $request->status }}">
                            <td data-label="Control Number"><strong>{{ $request->control_number }}</strong></td>
                            <td data-label="Category">
                                @if($request->category)
                                <span class="category-badge">
                                    @php
                                    $categoryVal = strtolower($request->category);
                                    if ($categoryVal === 'network') {
                                    echo '🌐 Network';
                                    } elseif ($categoryVal === 'ict') {
                                    echo '💻 ICT';
                                    } elseif ($categoryVal === 'mis') {
                                    echo '📊 MIS';
                                    } elseif ($categoryVal === 'web') {
                                    echo '🌍 Web';
                                    } else {
                                    echo ucfirst(str_replace('_', ' ', $categoryVal));
                                    }
                                    @endphp
                                </span>
                                @else
                                <span class="category-badge">-</span>
                                @endif
                            </td>
                            <td data-label="Date Submitted">{{ $request->created_at->format('M d, Y') }}</td>
                            <td data-label="Linked Records">
                                @if($request->incident_reports_count || $request->preventive_maintenance_checklists_count)
                                @if($request->incident_reports_count)<span>{{ $request->incident_reports_count }} incident</span>@endif
                                @if($request->incident_reports_count && $request->preventive_maintenance_checklists_count)<span> &middot; </span>@endif
                                @if($request->preventive_maintenance_checklists_count)<span>{{ $request->preventive_maintenance_checklists_count }} PM</span>@endif
                                @else
                                <span style="color:#586d78;">None</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ strtolower($request->status ?? 'pending') }}">
                                    {{ $request->status_label }}
                                </span>
                                <div style="margin-top:4px;color:#6b7280;font-size:11px;">{{ $request->responsibility_label }}</div>
                            </td>
                            <td data-label="Actions">
                                <div class="request-actions">
                                    <a href="{{ route('requests.show', $request->id) }}" class="request-action primary">View details</a>
                                    @if($request->canBeEditedByUser())
                                    <a href="{{ route('requests.edit', $request) }}" class="request-action">Edit</a>
                                    @endif
                                    <a href="{{ route('request.pdf', $request->id) }}" class="request-action" target="_blank" rel="noopener" data-pdf-action>View PDF</a>
                                    @if($request->attachment)
                                    <a href="{{ route('requests.attachment', $request) }}" class="request-action">Attachment</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">📜</div>
                                    <p>No service requests yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </main>

    <script>
        function searchRequests() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const controlNumber = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                if (searchInput === '' || controlNumber.includes(searchInput)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>