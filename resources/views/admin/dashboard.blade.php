@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'See the current status of requests, incident reports, and preventive maintenance records.')

@section('header-actions')
    <a href="{{ route('admin.requests.index') }}" class="btn-primary">View Service Requests</a>
@endsection

@section('content')
    <style>
        .module-overview-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .module-overview-card {
            min-width: 0;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--surface-soft);
        }
        .module-overview-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--line);
        }
        .module-overview-head h3 { margin: 0; font-size: 15px; }
        .module-overview-head p { margin: 3px 0 0; color: var(--ink-500); font-size: 11px; }
        .module-total { color: var(--brand-900); font-size: 28px; font-weight: 750; line-height: 1; }
        .module-status-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
            margin-top: 11px;
        }
        .module-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
            padding: 7px 8px;
            border-radius: 7px;
            background: #fff;
            color: var(--ink-500);
            font-size: 11px;
            transition: border-color .18s ease, background .18s ease;
        }
        .module-status:hover { background: #edf4f9; box-shadow: inset 0 0 0 1px #c6d9e7; }
        .module-status span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .module-status strong { color: var(--ink-900); font-size: 13px; }
        .activity-list { display: grid; gap: 7px; }
        .activity-item {
            display: grid;
            grid-template-columns: 118px minmax(190px, 1.2fr) minmax(150px, .85fr) 145px auto;
            align-items: center;
            gap: 13px;
            min-width: 0;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: #fff;
        }
        .activity-item > div { min-width: 0; }
        .activity-type {
            display: inline-flex;
            width: fit-content;
            max-width: 100%;
            padding: 4px 7px;
            border-radius: 6px;
            background: var(--brand-100);
            color: var(--brand-900);
            font-size: 10px;
            font-weight: 750;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .activity-title { overflow-wrap: anywhere; color: var(--ink-900); font-size: 13px; font-weight: 700; }
        .activity-meta { margin-top: 2px; color: var(--ink-500); font-size: 11px; overflow-wrap: anywhere; }
        .activity-date { color: var(--ink-500); font-size: 11px; white-space: nowrap; }
        .activity-action { justify-self: end; }
        @media (max-width: 1050px) {
            .module-overview-grid { grid-template-columns: 1fr; }
            .activity-item { grid-template-columns: 105px minmax(0, 1fr) 145px auto; }
            .activity-owner { display: none; }
        }
        @media (max-width: 700px) {
            .activity-item { grid-template-columns: minmax(0, 1fr) auto; gap: 8px 12px; }
            .activity-record { grid-column: 1 / -1; }
            .activity-date { white-space: normal; }
            .activity-action { align-self: end; }
        }
        @media (max-width: 420px) {
            .module-status-grid { grid-template-columns: 1fr; }
            .activity-item { grid-template-columns: 1fr; }
            .activity-record { grid-column: auto; }
            .activity-action { justify-self: stretch; }
            .activity-action .btn-ghost { width: 100%; justify-content: center; }
        }
    </style>

    @php
        $simpleRequestStatusLabels = [
            'submitted' => 'New',
            'pending' => 'Waiting for Assignment',
            'processing' => 'In Progress',
            'awaiting_requester_info' => 'Waiting for User Reply',
            'awaiting_confirmation' => 'Waiting for User Confirmation',
            'completed' => 'Work Completed',
            'closed' => 'Closed',
            'reopened' => 'Returned',
            'escalated' => 'Needs Review',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
        ];
    @endphp

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>System Overview</h2>
                <p>Counts update automatically whenever a record changes status.</p>
            </div>
        </div>

        <div class="module-overview-grid">
            <section class="module-overview-card">
                <div class="module-overview-head">
                    <div><h3>Service Requests</h3><p>All client service requests</p></div>
                    <span class="module-total">{{ $moduleStats['service_requests']['total'] }}</span>
                </div>
                <div class="module-status-grid">
                    <a class="module-status" href="{{ route('admin.requests.index', ['search_queue' => 'triage']) }}"><span>New</span><strong>{{ $moduleStats['service_requests']['new'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.requests.index', ['search_status' => 'processing']) }}"><span>In Progress</span><strong>{{ $moduleStats['service_requests']['ongoing'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.requests.index', ['search_status' => 'awaiting_requester_info']) }}"><span>Waiting for Reply</span><strong>{{ $moduleStats['service_requests']['waiting_reply'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.requests.index', ['search_status' => 'awaiting_confirmation']) }}"><span>Waiting Confirmation</span><strong>{{ $moduleStats['service_requests']['waiting_confirmation'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.requests.index', ['search_status' => 'completed']) }}"><span>Completed</span><strong>{{ $moduleStats['service_requests']['completed'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.requests.index', ['search_status' => 'closed']) }}"><span>Closed</span><strong>{{ $moduleStats['service_requests']['closed'] }}</strong></a>
                </div>
            </section>

            <section class="module-overview-card">
                <div class="module-overview-head">
                    <div><h3>Incident Reports</h3><p>ICT-related incident records</p></div>
                    <span class="module-total">{{ $moduleStats['incident_reports']['total'] }}</span>
                </div>
                <div class="module-status-grid">
                    <a class="module-status" href="{{ route('admin.incident-reports.index', ['status' => 'open']) }}"><span>Open</span><strong>{{ $moduleStats['incident_reports']['open'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.incident-reports.index', ['status' => 'in_progress']) }}"><span>In Progress</span><strong>{{ $moduleStats['incident_reports']['ongoing'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.incident-reports.index', ['status' => 'resolved']) }}"><span>Resolved</span><strong>{{ $moduleStats['incident_reports']['resolved'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.incident-reports.index', ['status' => 'closed']) }}"><span>Closed</span><strong>{{ $moduleStats['incident_reports']['closed'] }}</strong></a>
                </div>
            </section>

            <section class="module-overview-card">
                <div class="module-overview-head">
                    <div><h3>Preventive Maintenance</h3><p>All maintenance checklists</p></div>
                    <span class="module-total">{{ $moduleStats['maintenance']['total'] }}</span>
                </div>
                <div class="module-status-grid">
                    <a class="module-status" href="{{ route('admin.pm-checklists.index', ['status' => 'pending']) }}"><span>Pending</span><strong>{{ $moduleStats['maintenance']['pending'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.pm-checklists.index', ['status' => 'needs_action']) }}"><span>Needs Action</span><strong>{{ $moduleStats['maintenance']['needs_action'] }}</strong></a>
                    <a class="module-status" href="{{ route('admin.pm-checklists.index', ['status' => 'completed']) }}"><span>Completed</span><strong>{{ $moduleStats['maintenance']['completed'] }}</strong></a>
                </div>
            </section>
        </div>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Recent Activity</h2>
                <p>Latest service requests, incident reports, and maintenance records in one list.</p>
            </div>
            <a href="{{ route('admin.operations') }}" class="btn-ghost">Operations &amp; History</a>
        </div>

        <div class="activity-list" aria-label="Recent activity from all request modules">
            @forelse($recentActivity->take(8) as $item)
                @php
                    $activityStatus = $item->status ?? 'pending';
                    $activityType = $item->dashboard_type ?? 'Record';
                    $activityStatusLabel = $activityType === 'Service Request'
                        ? ($simpleRequestStatusLabels[$activityStatus] ?? str($activityStatus)->replace('_', ' ')->title())
                        : str($activityStatus)->replace('_', ' ')->title();
                    $activityStatusClass = match ($activityStatus) {
                        'in_progress', 'processing' => 'processing',
                        'resolved', 'completed', 'closed' => 'completed',
                        'needs_action', 'escalated', 'reopened' => 'rejected',
                        default => 'pending',
                    };
                @endphp
                <article class="activity-item">
                    <div><span class="activity-type">{{ $activityType }}</span></div>
                    <div class="activity-record">
                        <div class="activity-title">{{ $item->dashboard_reference ?? '-' }}</div>
                        <div class="activity-meta">{{ $item->dashboard_meta ?? 'No additional details' }}</div>
                    </div>
                    <div class="activity-owner">
                        <div class="activity-title">{{ $item->dashboard_owner ?? '-' }}</div>
                        <div class="activity-meta">Owner / Requester</div>
                    </div>
                    <div>
                        <span class="status-badge {{ $activityStatusClass }}">{{ $activityStatusLabel }}</span>
                        <div class="activity-date">Updated {{ optional($item->updated_at)->format('M d, Y') }}</div>
                    </div>
                    <div class="activity-action"><a href="{{ $item->dashboard_url ?? '#' }}" class="btn-ghost">Open</a></div>
                </article>
            @empty
                <div class="empty-state">No recent activity yet.</div>
            @endforelse
        </div>
    </section>
@endsection
