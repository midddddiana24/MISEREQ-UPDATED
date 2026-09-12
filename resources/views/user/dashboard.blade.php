<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - MIS Service Request System</title>
    @php
    $user = auth()->user();
    $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
    ? asset('images/wvsu-janiuay-logo.png')
    : asset('images/wvsu-logo.png');
    @endphp
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

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 22px clamp(14px, 4vw, 20px) 48px;
        }

        .welcome {
            margin-bottom: 16px;
            font-size: 18px;
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

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .panel {
            padding: 22px;
            background: #fff;
            border: 1px solid #d7dce2;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .panel h2 {
            margin-bottom: 12px;
            font-size: 22px;
        }

        .service-card {
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .service-card p {
            color: #4b5563;
            line-height: 1.5;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin: 16px 0;
        }

        .stat {
            padding: 14px;
            background: #eef6ff;
            border-left: 4px solid #1f6fa8;
        }

        .stat strong {
            display: block;
            font-size: 24px;
        }

        .stat-link {
            color: inherit;
            text-decoration: none;
            transition: border-color .15s ease, background .15s ease;
        }

        .stat-link:hover {
            border-left-color: #174f75 !important;
            background: #f7fbfe !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            padding: 9px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }

        .recent-table th,
        .recent-table td {
            padding: 10px 12px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eef2ff;
            color: #1e40af;
            font-weight: 700;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
        }

        .status-badge.pending {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fcd34d;
        }

        .status-badge.submitted {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .status-badge.processing {
            background: #eef2ff;
            color: #1e40af;
        }

        .status-badge.completed {
            background: #ecfdf5;
            color: #16a34a;
        }

        .status-badge.awaiting_confirmation {
            background: #f5f3ff;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }

        .status-badge.awaiting_requester_info {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-badge.reopened {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .status-badge.escalated {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-badge.closed {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-badge.cancelled,
        .status-badge.rejected {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .workflow-alert {
            margin: 16px 0;
            padding: 16px;
            border: 1px solid #cfe0ea;
            border-left: 4px solid #d9aa22;
            border-radius: 8px;
            background: #fff;
        }

        .workflow-alert h2 {
            margin: 0;
            color: #173f62;
            font-size: 16px;
        }

        .workflow-alert p {
            margin: 4px 0 12px;
            color: #64748b;
            font-size: 13px;
        }

        .workflow-items {
            display: grid;
            gap: 8px;
        }

        .workflow-item {
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-radius: 6px;
            background: #f8fafc;
            font-size: 13px;
        }

        .workflow-item strong {
            color: #243b4a;
        }

        .workflow-item span {
            color: #64748b;
        }

        .status-badge.open {
            background: #ffffff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .wide {
            grid-column: span 3;
        }

        .empty {
            color: #6b7280;
            padding: 10px 0;
        }

        @media (max-width: 1024px) {
            .grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .wide {
                grid-column: span 2;
            }
        }

        @media (max-width: 860px) {
            .header-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .nav-shell {
                margin-bottom: 16px;
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

            .grid,
            .stats {
                grid-template-columns: 1fr;
            }

            .wide {
                grid-column: span 1;
            }

            table,
            thead,
            tbody,
            tr,
            th,
            td {
                display: block;
            }

            thead {
                display: none;
            }

            td {
                border-bottom: 0;
                padding: 5px 0;
            }

            tr {
                border-bottom: 1px solid #e5e7eb;
                padding: 12px 0;
            }

            .recent-table td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 3px;
                color: #6b7280;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
        }

        @media (max-width: 560px) {
            .panel {
                padding: 18px;
            }

            .welcome {
                font-size: 16px;
            }
        }
    </style>
    @include('user.partials.theme')
    <style>
        .recent-activity-header {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
        }

        .recent-activity-header h2 {
            margin: 0 !important;
            font-size: 17px !important;
        }

        .recent-activity-header p {
            margin: 3px 0 0;
            color: var(--client-ink-500);
            font-size: 12px;
        }

        .stat-link:hover {
            border-top-color: var(--client-brand-900) !important;
            background: #fbfdfe !important;
            transform: translateY(-1px);
        }

        .stat-link:focus-visible {
            position: relative;
            z-index: 1;
            outline: 3px solid rgba(31, 98, 142, .24);
            outline-offset: 2px;
        }

        @media (max-width: 1024px) {

            .recent-table,
            .recent-table tbody {
                display: block !important;
            }

            .recent-table tbody {
                display: grid !important;
                gap: 8px;
            }

            .recent-table tr:not(.recent-empty-row) {
                padding: 11px 12px !important;
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-rows: auto auto auto;
                column-gap: 10px;
                border: 1px solid var(--client-line) !important;
                border-radius: 7px;
                background: #fff;
            }

            .recent-table tr:not(.recent-empty-row) td {
                width: auto !important;
                padding: 2px 0 !important;
                border: 0 !important;
            }

            .recent-table tr:not(.recent-empty-row) td::before {
                display: none !important;
            }

            .recent-table tr:not(.recent-empty-row) td:nth-child(2),
            .recent-table tr:not(.recent-empty-row) td:nth-child(5) {
                display: none !important;
            }

            .recent-table tr:not(.recent-empty-row) td:nth-child(1) {
                grid-column: 1;
                color: var(--client-brand-900);
                font-size: 12px;
            }

            .recent-table tr:not(.recent-empty-row) td:nth-child(3) {
                grid-column: 1;
                color: var(--client-ink-700);
                font-size: 12px;
                line-height: 1.4;
            }

            .recent-table tr:not(.recent-empty-row) td:nth-child(4) {
                grid-column: 1;
                margin-top: 2px;
            }

            .recent-table tr:not(.recent-empty-row) td:nth-child(6) {
                grid-column: 2;
                grid-row: 1 / 4;
                align-self: center;
            }

            .recent-table tr:not(.recent-empty-row) td:nth-child(6) .button {
                width: auto !important;
                min-height: 36px;
                padding: 7px 10px !important;
                font-size: 11px !important;
            }

            .recent-table .status-badge {
                padding: 3px 7px !important;
                font-size: 10px !important;
            }
        }

        @media (min-width: 701px) and (max-width: 980px) {
            .stats {
                grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
            }

            .stats .stat {
                grid-column: span 2;
            }

            .stats .stat:nth-child(4),
            .stats .stat:nth-child(5) {
                grid-column: span 3;
            }

            .campus-tool-list {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .campus-tool {
                min-height: 132px !important;
                border-right: 1px solid var(--client-line) !important;
                border-bottom: 1px solid var(--client-line) !important;
            }

            .campus-tool:nth-child(2) {
                border-right: 0 !important;
            }

            .campus-tool:nth-child(3) {
                grid-column: 1 / -1;
                min-height: 0 !important;
                border-right: 0 !important;
                border-bottom: 0 !important;
            }
        }

        @media (max-width: 700px) {
            .stats .stat:last-child:nth-child(odd) {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 390px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 7px !important;
            }

            .stat {
                min-height: 76px;
                padding: 10px !important;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .stat strong {
                font-size: 19px !important;
            }

            .stat>span {
                margin-top: 2px;
                font-size: 10px !important;
                line-height: 1.3;
            }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        <section class="client-page-heading" aria-labelledby="dashboardTitle">
            <div>
                <span class="eyebrow">MIS Client Portal</span>
                <h2 id="dashboardTitle">Service dashboard</h2>
                <p>Welcome, {{ $user->first_name }}. Manage your requests and review updates from the MIS office.</p>
            </div>
            <a href="{{ route('requests.create') }}" class="button">New service request</a>
        </section>

        @if (session('success'))
        <div class="panel" style="margin-bottom:16px; border-left:4px solid #16a34a;">{{ session('success') }}</div>
        @endif

        <section class="stats" aria-label="Your MIS record totals">
            <a class="stat stat-link" href="{{ route('requests.index') }}"><strong>{{ $stats['total'] }}</strong><span>Service Requests</span></a>
            <a class="stat stat-link" href="{{ route('requests.index', ['status' => 'active']) }}"><strong>{{ $stats['active'] }}</strong><span>Active Requests</span></a>
            <a class="stat stat-link" href="{{ route('requests.index', ['status' => 'awaiting_confirmation']) }}"><strong>{{ $stats['awaiting_confirmation'] }}</strong><span>Needs Confirmation</span></a>
            <a class="stat stat-link" href="{{ route('incident-reports.index') }}"><strong>{{ $stats['incident_reports'] }}</strong><span>ICT Incident Reports</span></a>
            <a class="stat stat-link" href="{{ route('pm-checklists.index') }}"><strong>{{ $stats['pm_checklists'] }}</strong><span>PM Checklists</span></a>
        </section>

        @if($attentionRequests->isNotEmpty() || $attentionMaintenance->isNotEmpty())
        <section class="workflow-alert" aria-labelledby="actionRequiredTitle">
            <h2 id="actionRequiredTitle">Request updates</h2>
            <p>These requests need your review or have an important MIS update.</p>
            <div class="workflow-items">
                @foreach($attentionRequests as $attentionRequest)
                <a class="workflow-item" href="{{ route('requests.show', $attentionRequest) }}">
                    <span><strong>{{ $attentionRequest->control_number }}</strong> &middot; {{ $attentionRequest->status_label }}</span>
                    <span>{{ $attentionRequest->status === 'awaiting_requester_info' ? 'Send response' : ($attentionRequest->status === 'awaiting_confirmation' ? 'Review service' : 'View update') }} &rsaquo;</span>
                </a>
                @endforeach
                @foreach($attentionMaintenance as $maintenanceRecord)
                <a class="workflow-item" href="{{ route('pm-checklists.show', $maintenanceRecord) }}">
                    <span><strong>{{ $maintenanceRecord->control_number }}</strong> &middot; Preventive maintenance needs attention</span>
                    <span>Review findings &rsaquo;</span>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        <section class="grid">
            <div class="panel campus-tools wide" aria-labelledby="serviceToolsTitle">
                <div class="campus-tools-header">
                    <h2 id="serviceToolsTitle">Service tools</h2>
                    <p>Choose the form that matches your concern.</p>
                </div>
                <div class="campus-tool-list">
                    <a class="campus-tool" href="{{ route('requests.create') }}">
                        <span class="campus-tool-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16v12H4zM8 20h8M12 16v4" />
                            </svg>
                        </span>
                        <span class="campus-tool-copy">
                            <strong>Service request</strong>
                            <span>Network, equipment, software, account, or web support.</span>
                            <small>Open form &rsaquo;</small>
                        </span>
                    </a>
                    <a class="campus-tool" href="{{ route('incident-reports.create') }}">
                        <span class="campus-tool-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3 2 20h20L12 3Z" />
                                <path d="M12 9v4m0 3h.01" />
                            </svg>
                        </span>
                        <span class="campus-tool-copy">
                            <strong>ICT incident</strong>
                            <span>Report security, data loss, malware, or misuse. A maintenance checklist is prepared automatically.</span>
                            <small>Open form &rsaquo;</small>
                        </span>
                    </a>
                    <a class="campus-tool" href="{{ route('requests.create', ['service' => 'preventive_maintenance']) }}">
                        <span class="campus-tool-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14.7 6.3a4 4 0 0 0-5-5L7 4l3 3 2.7-2.7a4 4 0 0 0 2 5L6 18l-2 3 3-2 8.7-8.7a4 4 0 0 0 5-5L18 8l-3-3 2.7-2.7" />
                            </svg>
                        </span>
                        <span class="campus-tool-copy">
                            <strong>Preventive maintenance</strong>
                            <span>Submit a service request for routine inspection. MIS completes the maintenance checklist.</span>
                            <small>Open form &rsaquo;</small>
                        </span>
                    </a>
                </div>
            </div>

            <div class="panel wide">
                <div class="recent-activity-header">
                    <div>
                        <h2>Recent activity</h2>
                        <p>Your five latest MIS records.</p>
                    </div>
                </div>
                @php
                $recentCombined = collect([]);
                $recentCombined = $recentRequests->merge($incidentReports)->merge($pmChecklists)->sortByDesc('updated_at')->values()->take(5);
                @endphp

                @if ($recentCombined->count())
                <div class="table-wrap">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>Control No.</th>
                                <th>Type</th>
                                <th>Title / Subject</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentCombined as $item)
                            @php
                            $control = $item->control_number ?? ($item->control_no ?? 'N/A');
                            $title = $item->title ?? $item->subject ?? $item->name ?? '-';
                            $status = $item->status ?? $item->report_status ?? $item->checklist_status ?? '';
                            $statusLabel = ucfirst(str_replace('_', ' ', $status));
                            $typeLabel = 'Service Request';
                            $showUrl = '#';

                            if ($item instanceof \App\Models\ServiceRequest) {
                            $typeLabel = 'Service Request';
                            $showUrl = route('requests.show', $item);
                            $statusLabel = $item->status_label;
                            } elseif ($item instanceof \App\Models\IctIncidentReport) {
                            $typeLabel = 'ICT Incident Report';
                            $showUrl = route('incident-reports.show', $item);
                            $title = $item->serviceRequest
                            ? 'Linked to ' . $item->serviceRequest->control_number
                            : \Illuminate\Support\Str::limit($item->details_of_incident ?: 'Standalone incident report', 80);
                            } elseif ($item instanceof \App\Models\PreventiveMaintenanceChecklist) {
                            $typeLabel = 'PM Checklist';
                            $showUrl = route('pm-checklists.show', $item);
                            $title = $item->serviceRequest
                            ? 'Linked to ' . $item->serviceRequest->control_number
                            : 'Standalone maintenance record';
                            }
                            @endphp
                            <tr>
                                <td data-label="Control No."><strong>{{ $control }}</strong></td>
                                <td data-label="Type"><span class="type-badge">{{ $typeLabel }}</span></td>
                                <td data-label="Title / Subject">{{ \Illuminate\Support\Str::limit($title, 80) }}</td>
                                <td data-label="Status">{!! $status ? '<span class="status-badge '.e($status).'">'.e($statusLabel).'</span>' : '&mdash;' !!}</td>
                                <td data-label="Last Updated">{{ $item->updated_at->format('M d, Y') }}</td>
                                <td data-label="Action"><a href="{{ $showUrl }}" class="button secondary">View</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="empty">No recent activity yet.</p>
                @endif
            </div>
        </section>
    </main>
</body>

</html>