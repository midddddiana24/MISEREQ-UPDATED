@extends('admin.layout')

@section('title', 'Technicians')

@section('page-title', 'Technician Management')

@section('page-subtitle', 'Manage technician records, workloads, and assignment readiness without changing the existing technician data flow.')

@section('header-actions')
    <a href="{{ route('admin.technicians.create') }}" class="btn-primary">Add Technician</a>
@endsection

@section('content')
    <section class="panel">
        <div class="toolbar-row">
            <div>
                <h2 style="margin:0;">Technician Admin Panel</h2>
                <div class="table-note">Track availability, workload, and profile records for assignment decisions.</div>
            </div>
            <a href="{{ route('admin.requests.index') }}" class="btn-ghost">View Requests</a>
        </div>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Team Overview</h2>
                <p>Quick visibility into technician capacity and availability.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="label">Total Technicians</span>
                <span class="value">{{ $technicians->count() }}</span>
            </div>
            <div class="stat-card">
                <span class="label">Available</span>
                <span class="value">{{ $technicians->where('status', 'available')->count() }}</span>
            </div>
            <div class="stat-card">
                <span class="label">Busy</span>
                <span class="value">{{ $technicians->where('status', 'busy')->count() }}</span>
            </div>
            <div class="stat-card">
                <span class="label">On Leave</span>
                <span class="value">{{ $technicians->where('status', 'on_leave')->count() }}</span>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Technician Directory</h2>
                <p>Review skills, contact details, and update records as needed.</p>
            </div>
        </div>

        <div class="toolbar-row">
            <div class="table-note">Use this list to decide technician assignments from the request details page.</div>
            <div class="table-note">Records: {{ $technicians->count() }}</div>
        </div>

        <div class="table-wrap">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Assignment</th>
                        <th>Specialization</th>
                        <th>Status</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($technicians as $technician)
                        <tr>
                            <td>
                                <div class="table-cell-title">{{ $technician->full_name }}</div>
                                <div class="table-cell-meta">Workload: {{ $technician->current_workload ?? 0 }}</div>
                            </td>
                            <td>{{ $technician->email }}</td>
                            <td>{{ $technician->job_assignment ?? '-' }}</td>
                            <td>{{ $technician->specialization ?? '-' }}</td>
                            <td><span class="status-badge {{ $technician->status }}">{{ str_replace('_', ' ', $technician->status) }}</span></td>
                            <td>{{ $technician->phone ?? '-' }}</td>
                            <td>
                                <div class="form-actions" style="margin-top: 0;">
                                    <a href="{{ route('admin.technicians.show', $technician->id) }}" class="btn-ghost">View</a>
                                    <a href="{{ route('admin.technicians.edit', $technician->id) }}" class="btn-ghost">Edit</a>
                                    @if($technician->is_active)<form method="POST" action="{{ route('admin.technicians.destroy', $technician->id) }}" onsubmit="return confirm('Deactivate this technician? Historical assignments will be preserved.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Deactivate</button>
                                    </form>@else<form method="POST" action="{{ route('admin.technicians.activate', $technician) }}">@csrf<button type="submit" class="btn-primary">Reactivate</button></form>@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">No technicians found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
