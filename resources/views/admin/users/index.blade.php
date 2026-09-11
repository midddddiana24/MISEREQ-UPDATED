@extends('admin.layout')

@section('title', 'Users')

@section('page-title', 'User Management')

@section('page-subtitle', 'Manage user accounts, roles, and credentials using the existing admin user controller actions.')

@section('header-actions')
    <a href="{{ route('admin.users.create') }}" class="btn-primary">Add User</a>
@endsection

@section('content')
    <section class="panel">
        <div class="toolbar-row">
            <div>
                <h2 style="margin:0;">User Admin Panel</h2>
                <div class="table-note">Create, review, and maintain account roles used across the service request system.</div>
            </div>
            <a href="{{ route('admin.settings') }}" class="btn-ghost">Open Settings</a>
        </div>
    </section>

    <section class="panel">
        <div class="stats-grid">
            <div class="stat-card">
                <span class="label">Total Users</span>
                <span class="value">{{ $users->count() }}</span>
            </div>
            <div class="stat-card">
                <span class="label">Admins</span>
                <span class="value">{{ $users->where('role', 'admin')->count() }}</span>
            </div>
            <div class="stat-card">
                <span class="label">End Users</span>
                <span class="value">{{ $users->where('role', 'user')->count() }}</span>
            </div>
            <div class="stat-card">
                <span class="label">Locked Accounts</span>
                <span class="value">{{ $users->filter(fn ($account) => $account->isAccountLocked())->count() }}</span>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>User Directory</h2>
                <p>Access detailed profiles or update account permissions.</p>
            </div>
        </div>

        <div class="toolbar-row">
            <div class="table-note">Administrative account management for end users, technicians, and admins.</div>
            <div class="table-note">Records: {{ $users->count() }}</div>
        </div>

        <div class="table-wrap">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="table-cell-title">{{ $user->full_name }}</div>
                                <div class="table-cell-meta">{{ $user->username }}</div>
                            </td>
                            <td>{{ $user->username }}</td>
                            <td>
                                <div class="table-cell-title">{{ $user->email }}</div>
                                <div class="table-cell-meta">{{ $user->email_verified_at ? 'Verified email' : 'Unverified email' }}</div>
                            </td>
                            <td><span class="status-badge {{ $user->role === 'admin' ? 'processing' : ($user->role === 'technician' ? 'pending' : 'completed') }}">{{ $user->role }}</span></td>
                            <td>
                                @if(!$user->is_active)
                                    <span class="status-badge cancelled">Disabled</span>
                                @elseif($user->isAccountLocked())
                                    <span class="status-badge rejected">Locked</span>
                                    <div class="table-cell-meta">Until {{ $user->locked_until->format('M d, h:i A') }}</div>
                                @else
                                    <span class="status-badge closed">Active</span>
                                    @if((int) $user->failed_login_attempts > 0)
                                        <div class="table-cell-meta">{{ $user->failed_login_attempts }} failed attempt(s)</div>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
                            <td>{{ optional($user->created_at)->format('M d, Y') }}</td>
                            <td>
                                <div class="form-actions" style="margin-top: 0;">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn-ghost">View</a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-ghost">Edit</a>
                                    @if($user->is_active && ($user->isAccountLocked() || (int) $user->failed_login_attempts > 0))
                                        <a href="{{ route('admin.users.show', $user->id) }}#account-access" class="btn-primary">Review Lock</a>
                                    @endif
                                    @if(!$user->is_active)
                                        <form method="POST" action="{{ route('admin.users.activate', $user) }}">@csrf<button type="submit" class="btn-primary">Reactivate</button></form>
                                    @elseif(auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Disable this user account? Existing records will be preserved.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger">Disable</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
