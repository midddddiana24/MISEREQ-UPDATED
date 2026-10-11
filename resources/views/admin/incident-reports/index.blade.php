@extends('admin.layout')

@section('title', 'Incident Reports')
@section('page-title', 'ICT Incident Reports')
@section('page-subtitle', 'Review client-submitted incident reports and manage the full admin-side workflow from one queue.')

@section('header-actions')
    <a href="{{ route('admin.incident-reports.index', array_merge(request()->except('group', 'archived', 'page'), ['group' => $archived ? 'active' : 'archived'])) }}" class="btn-ghost">{{ $archived ? 'Active Records' : 'Archived Records' }}</a>
    @unless($archived)<a href="{{ route('admin.incident-reports.create') }}" class="btn-primary">New Incident Report</a>@endunless
@endsection

@section('content')
    <section class="panel">
        <div class="stats-grid">
            <a class="stat-card" href="{{ route('admin.incident-reports.index', array_merge(request()->except('status', 'archived', 'page'), ['group' => $group])) }}">
                <span class="label">Total Records</span>
                <span class="value">{{ $statusCounts->sum() }}</span>
            </a>
            @foreach($statusOptions as $value => $label)
                <a class="stat-card {{ $statusFilter === $value ? 'admin' : '' }}" href="{{ route('admin.incident-reports.index', array_merge(request()->except('status', 'archived', 'page'), ['status' => $value, 'group' => $group])) }}">
                    <span class="label">{{ $label }}</span>
                    <span class="value">{{ $statusCounts[$value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    </section>

    @include('admin.partials.record-list-filters', ['indexRoute' => 'admin.incident-reports.index', 'searchPlaceholder' => 'Document number, reporter, office, or concern', 'dateLabel' => 'Incident date'])

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $statusFilter ? ($statusOptions[$statusFilter] . ' Incident Reports') : ($group === 'active' ? 'Incident Reports' : $groupOptions[$group]) }}</h2>
                <p>Review and edit reports here, or archive records that no longer belong in the active queue.</p>
            </div>
            @if($statusFilter)<a href="{{ route('admin.incident-reports.index', array_merge(request()->except('status', 'archived', 'page'), ['group' => $group])) }}" class="btn-ghost">All statuses</a>@endif
        </div>

        <div class="table-wrap">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Reporter</th>
                        <th>Owner</th>
                        <th>Incident Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                <div class="table-cell-title">{{ $report->document_number }}</div>
                                <div class="table-cell-meta">{{ optional($report->created_at)->format('M d, Y h:i A') }}</div>
                            </td>
                            <td>
                                <div class="table-cell-title">{{ $report->reported_by }}</div>
                                <div class="table-cell-meta">{{ $report->department ?: 'No department' }}</div>
                            </td>
                            <td>{{ $report->user?->full_name ?? 'Unassigned' }}</td>
                            <td>{{ optional($report->date_of_incident)->format('M d, Y') }}</td>
                            <td><span class="status-badge {{ $report->stage_status }}">{{ str($report->stage_status)->title() }}</span></td>
                            <td>
                                <div class="admin-record-actions">
                                    @unless($archived)<a href="{{ route('admin.incident-reports.show', $report) }}" class="btn-ghost">View</a>
                                    <a href="{{ route('admin.incident-reports.edit', $report) }}" class="btn-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.incident-reports.destroy', $report) }}" onsubmit="return confirm('Archive this incident report? You can restore it from Archived Records.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="archive_reason" value="Archived from the incident queue by an administrator.">
                                        <button type="submit" class="btn-danger">Archive</button>
                                    </form>@else
                                    <form method="POST" action="{{ route('admin.incident-reports.restore', $report->id) }}">@csrf<button type="submit" class="btn-primary">Restore</button></form>@endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No incident reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">
            {{ $reports->links('admin.partials.pagination', ['paginationLabel' => 'Incident report pages']) }}
        </div>
    </section>
@endsection
