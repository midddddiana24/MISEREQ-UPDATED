@extends('admin.layout')

@section('title', 'PM Checklists')
@section('page-title', 'Preventive Maintenance Checklists')
@section('page-subtitle', 'Manage preventive maintenance records submitted from the client side and maintain their admin review status.')

@section('header-actions')
    <a href="{{ route('admin.pm-checklists.index', array_merge(request()->except('group', 'archived', 'page'), ['group' => $archived ? 'active' : 'archived'])) }}" class="btn-ghost">{{ $archived ? 'Active Records' : 'Archived Records' }}</a>
    @unless($archived)<a href="{{ route('admin.pm-checklists.create') }}" class="btn-primary">New PM Checklist</a>@endunless
@endsection

@section('content')
    <section class="panel">
        <div class="stats-grid">
            <a class="stat-card" href="{{ route('admin.pm-checklists.index', array_merge(request()->except('status', 'archived', 'page'), ['group' => $group])) }}">
                <span class="label">Total Records</span>
                <span class="value">{{ $statusCounts->sum() }}</span>
            </a>
            @foreach($statusOptions as $value => $label)
                <a class="stat-card {{ $statusFilter === $value ? 'admin' : '' }}" href="{{ route('admin.pm-checklists.index', array_merge(request()->except('status', 'archived', 'page'), ['status' => $value, 'group' => $group])) }}">
                    <span class="label">{{ $label }}</span>
                    <span class="value">{{ $statusCounts[$value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    </section>

    @include('admin.partials.record-list-filters', ['indexRoute' => 'admin.pm-checklists.index', 'searchPlaceholder' => 'Checklist number, property, owner, or office', 'dateLabel' => 'Inspection date'])

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $statusFilter ? ($statusOptions[$statusFilter] . ' Maintenance Records') : ($group === 'active' ? 'Preventive Maintenance Records' : $groupOptions[$group]) }}</h2>
                <p>Review and edit checklists here, or archive records that no longer belong in the active queue.</p>
            </div>
            @if($statusFilter)<a href="{{ route('admin.pm-checklists.index', array_merge(request()->except('status', 'archived', 'page'), ['group' => $group])) }}" class="btn-ghost">All statuses</a>@endif
        </div>

        <div class="table-wrap">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Control No.</th>
                        <th>System Owner</th>
                        <th>Owner</th>
                        <th>Inspection Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checklists as $checklist)
                        <tr>
                            <td>
                                <div class="table-cell-title">{{ $checklist->control_number }}</div>
                                <div class="table-cell-meta">{{ $checklist->property_number ?: 'No property number' }}</div>
                            </td>
                            <td>
                                <div class="table-cell-title">{{ $checklist->system_owner }}</div>
                                <div class="table-cell-meta">{{ $checklist->office_department ?: 'No department' }}</div>
                            </td>
                            <td>{{ $checklist->user?->full_name ?? 'Unassigned' }}</td>
                            <td>{{ optional($checklist->date_inspected)->format('M d, Y') }}</td>
                            <td><span class="status-badge {{ $checklist->stage_status }}">{{ str($checklist->stage_status)->title() }}</span></td>
                            <td>
                                <div class="admin-record-actions">
                                    @unless($archived)<a href="{{ route('admin.pm-checklists.show', $checklist) }}" class="btn-ghost">View</a>
                                    <a href="{{ route('admin.pm-checklists.edit', $checklist) }}" class="btn-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.pm-checklists.destroy', $checklist) }}" onsubmit="return confirm('Archive this PM checklist? You can restore it from Archived Records.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="archive_reason" value="Archived from the maintenance queue by an administrator.">
                                        <button type="submit" class="btn-danger">Archive</button>
                                    </form>@else
                                    <form method="POST" action="{{ route('admin.pm-checklists.restore', $checklist->id) }}">@csrf<button type="submit" class="btn-primary">Restore</button></form>@endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No preventive maintenance checklists found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">
            {{ $checklists->links('admin.partials.pagination', ['paginationLabel' => 'Maintenance checklist pages']) }}
        </div>
    </section>
@endsection
