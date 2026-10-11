@extends('admin.layout')

@section('title', 'Operations Report')
@section('page-title', 'Service Operations Report')
@section('page-subtitle', 'Review service levels, resolution performance, workload, feedback, and recent administrative actions.')

@section('content')
    <nav aria-label="System settings sections" style="display:flex;gap:5px;width:max-content;max-width:100%;margin-bottom:12px;padding:4px;border:1px solid var(--line);border-radius:9px;background:#fff;overflow-x:auto;">
        <a href="{{ route('admin.settings') }}" style="padding:7px 11px;border-radius:6px;color:var(--ink-600);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap;">Configuration</a>
        <a href="{{ route('admin.settings.operations') }}" style="padding:7px 11px;border-radius:6px;color:var(--ink-600);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap;">System Operations</a>
        <a href="{{ route('admin.operations') }}" aria-current="page" style="padding:7px 11px;border-radius:6px;color:var(--brand-900);background:var(--brand-100);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap;">Reports &amp; Audit</a>
    </nav>

    <section class="panel">
        <form method="GET" action="{{ route('admin.operations') }}" class="form-grid">
            <div class="field form-col-4">
                <label for="date_from">Created From</label>
                <input id="date_from" type="date" name="date_from" value="{{ $dateFrom->toDateString() }}">
            </div>
            <div class="field form-col-4">
                <label for="date_to">Created To</label>
                <input id="date_to" type="date" name="date_to" value="{{ $dateTo->toDateString() }}">
            </div>
            <div class="form-col-4 form-actions" style="align-self:end;">
                <button type="submit" class="btn-primary">Update Report</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="section-heading"><div><h2>Performance Summary</h2><p>Requests created between {{ $dateFrom->format('M d, Y') }} and {{ $dateTo->format('M d, Y') }}.</p></div></div>
        <div class="compact-grid">
            @foreach([
                ['Total Requests', $metrics['total']],
                ['Open Requests', $metrics['open']],
                ['Closed Requests', $metrics['closed']],
                ['Average Resolution', $metrics['average_resolution_hours'].' hrs'],
                ['Average Feedback', $metrics['average_feedback'] ? $metrics['average_feedback'].' / 5' : 'N/A'],
                ['Returned Requests', $metrics['reopened']],
            ] as [$label, $value])
                <div class="stat-card"><span class="label">{{ $label }}</span><span class="value">{{ $value }}</span></div>
            @endforeach
        </div>
    </section>

    <div class="split-layout">
        <section class="panel">
            <div class="section-heading"><div><h2>Status Distribution</h2><p>Request outcomes and current workflow stages.</p></div></div>
            <div class="table-wrap">
                <table class="compact-table"><thead><tr><th>Status</th><th>Requests</th></tr></thead><tbody>
                    @forelse($statusCounts as $status => $count)
                        <tr><td><span class="status-badge {{ $status }}">{{ str($status)->replace('_', ' ')->title() }}</span></td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No requests in this reporting period.</td></tr>
                    @endforelse
                </tbody></table>
            </div>
        </section>
        <section class="panel">
            <div class="section-heading"><div><h2>Assignment Overview</h2><p>Requests with and without an assigned technician.</p></div></div>
            <div class="table-wrap">
                <table class="compact-table"><thead><tr><th>Assignment</th><th>Requests</th></tr></thead><tbody>
                    @forelse($assignmentCounts as $assignment => $count)
                        <tr><td>{{ $assignment }}</td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No assignment information available.</td></tr>
                    @endforelse
                </tbody></table>
            </div>
        </section>
    </div>

    <section class="panel">
        <div class="section-heading"><div><h2>Technician Capacity</h2><p>Active assignments are calculated from the current workflow states.</p></div></div>
        <div class="table-wrap">
            <table class="compact-table"><thead><tr><th>Technician</th><th>Specialization</th><th>Status</th><th>Active Assignments</th><th>Capacity</th></tr></thead><tbody>
                @foreach($technicians as $technician)
                    <tr>
                        <td>{{ $technician->full_name }}</td>
                        <td>{{ $technician->specialization ?: '-' }}</td>
                        <td><span class="status-badge {{ $technician->status }}">{{ str_replace('_', ' ', $technician->status) }}</span></td>
                        <td>{{ $technician->active_assignments_count }}</td>
                        <td>{{ min(100, round(($technician->active_assignments_count / 10) * 100)) }}%</td>
                    </tr>
                @endforeach
            </tbody></table>
        </div>
    </section>

    <section class="panel">
        <div class="section-heading"><div><h2>Recent Administrative Activity</h2><p>Latest reviews, assignments, updates, and feedback actions across requests.</p></div></div>
        <form method="GET" action="{{ route('admin.operations') }}" class="form-grid" style="margin-bottom:12px;">
            <input type="hidden" name="date_from" value="{{ $dateFrom->toDateString() }}">
            <input type="hidden" name="date_to" value="{{ $dateTo->toDateString() }}">
            <div class="field form-col-4">
                <label for="activity_search">Search activity</label>
                <input id="activity_search" name="activity_search" value="{{ $filters['activity_search'] ?? '' }}" placeholder="Control no., action, or description">
            </div>
            <div class="field form-col-2">
                <label for="activity_module">Module</label>
                <select id="activity_module" name="activity_module">
                    <option value="">All modules</option>
                    @foreach(['requests' => 'Service requests', 'incidents' => 'Incident reports', 'maintenance' => 'Preventive maintenance', 'accounts' => 'Accounts', 'system' => 'System'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['activity_module'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field form-col-2">
                <label for="activity_actor">Administrator</label>
                <select id="activity_actor" name="activity_actor">
                    <option value="">All administrators</option>
                    @foreach($activityActors as $actor)
                        <option value="{{ $actor->id }}" @selected((string) ($filters['activity_actor'] ?? '') === (string) $actor->id)>{{ $actor->full_name ?: $actor->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field form-col-2">
                <label for="activity_action">Action</label>
                <select id="activity_action" name="activity_action">
                    <option value="">All actions</option>
                    @foreach($activityActions as $action)
                        <option value="{{ $action }}" @selected(($filters['activity_action'] ?? '') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field form-col-2">
                <label for="activity_from">From</label>
                <input id="activity_from" type="date" name="activity_from" value="{{ $filters['activity_from'] ?? '' }}">
            </div>
            <div class="field form-col-2">
                <label for="activity_to">To</label>
                <input id="activity_to" type="date" name="activity_to" value="{{ $filters['activity_to'] ?? '' }}">
            </div>
            <div class="form-col-4 form-actions" style="align-self:end;">
                <button type="submit" class="btn-primary">Apply Filters</button>
                <a href="{{ route('admin.operations', ['date_from' => $dateFrom->toDateString(), 'date_to' => $dateTo->toDateString()]) }}" class="btn-ghost">Clear</a>
            </div>
        </form>
        <div class="table-wrap">
            <table class="compact-table"><thead><tr><th>Time</th><th>Request</th><th>Action</th><th>Administrator</th><th>Description</th></tr></thead><tbody>
                @forelse($recentActivity as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                        <td>
                            @if($activity->serviceRequest)
                                <a href="{{ route('admin.service-requests.show', $activity->serviceRequest) }}">{{ $activity->serviceRequest->control_number }}</a>
                            @else
                                Administrative record
                            @endif
                        </td>
                        <td>{{ str($activity->action)->replace('_', ' ')->title() }}</td>
                        <td>{{ $activity->actor?->full_name ?? 'System' }}</td>
                        <td>{{ $activity->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No audit actions recorded yet.</td></tr>
                @endforelse
            </tbody></table>
        </div>
        @if($recentActivity->hasPages())
            <div style="margin-top:12px;">{{ $recentActivity->links('admin.partials.pagination', ['paginationLabel' => 'Audit activity pages']) }}</div>
        @endif
    </section>
@endsection
