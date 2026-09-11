@extends('admin.layout')

@section('title', 'PM Checklists')
@section('page-title', 'Preventive Maintenance Checklists')
@section('page-subtitle', 'Manage preventive maintenance records submitted from the client side and maintain their admin review status.')

@section('header-actions')
    <a href="{{ route('admin.pm-checklists.index', ['archived' => $archived ? 0 : 1]) }}" class="btn-ghost">{{ $archived ? 'Active Records' : 'Archived Records' }}</a>
    @unless($archived)<a href="{{ route('admin.pm-checklists.create') }}" class="btn-primary">New PM Checklist</a>@endunless
@endsection

@section('content')
    <section class="panel">
        <div class="stats-grid">
            <a class="stat-card" href="{{ route('admin.pm-checklists.index', ['archived' => $archived ? 1 : 0]) }}">
                <span class="label">Total Records</span>
                <span class="value">{{ $statusCounts->sum() }}</span>
            </a>
            @foreach($statusOptions as $value => $label)
                <a class="stat-card {{ $statusFilter === $value ? 'admin' : '' }}" href="{{ route('admin.pm-checklists.index', ['status' => $value, 'archived' => $archived ? 1 : 0]) }}">
                    <span class="label">{{ $label }}</span>
                    <span class="value">{{ $statusCounts[$value] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $statusFilter ? ($statusOptions[$statusFilter] . ' Maintenance Records') : ($archived ? 'Archived PM Records' : 'Preventive Maintenance Records') }}</h2>
                <p>Admin can review, edit, print, and remove preventive maintenance records from this list.</p>
            </div>
            @if($statusFilter)<a href="{{ route('admin.pm-checklists.index', ['archived' => $archived ? 1 : 0]) }}" class="btn-ghost">Show All</a>@endif
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
                            <td><span class="status-badge {{ $checklist->status === 'completed' ? 'completed' : ($checklist->status === 'needs_action' ? 'rejected' : 'pending') }}">{{ str_replace('_', ' ', $checklist->status) }}</span></td>
                            <td>
                                <div class="form-actions" style="margin-top:0;">
                                    @unless($archived)<a href="{{ route('admin.pm-checklists.show', $checklist) }}" class="btn-ghost">View</a>
                                    <a href="{{ route('admin.pm-checklists.edit', $checklist) }}" class="btn-ghost">Edit</a>
                                    <form method="POST" action="{{ route('admin.pm-checklists.destroy', $checklist) }}" onsubmit="return confirm('Delete this PM checklist?');">
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
            {{ $checklists->links() }}
        </div>
    </section>
@endsection
