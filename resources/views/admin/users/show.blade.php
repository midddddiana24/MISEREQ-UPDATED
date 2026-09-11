@extends('admin.layout')

@section('title', 'User Profile')

@section('page-title', 'User Profile')

@section('page-subtitle', 'Review user identity, access level, and account status before making administrative changes.')

@section('header-actions')
    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-primary">Edit User</a>
    <a href="{{ route('admin.users.index') }}" class="btn-ghost">Back to Users</a>
@endsection

@section('head')
    <style>
        .account-security-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, .65fr); gap: 12px; align-items: start; }
        .unlock-form { display: grid; gap: 8px; }
        .unlock-form textarea { min-height: 76px; resize: vertical; }
        .security-summary-list { display: grid; gap: 7px; margin: 0; }
        .security-summary-list > div { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 9px; border-radius: 7px; background: #f6f8fa; font-size: 11px; }
        .security-summary-list dt { color: var(--ink-500); font-weight: 700; }
        .security-summary-list dd { margin: 0; color: var(--ink-800); font-weight: 750; text-align: right; }
        .security-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 9px; }
        .security-actions form { display: flex; }
        .security-actions button { width: 100%; }
        .session-list { display: grid; gap: 8px; }
        .session-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 11px; border: 1px solid var(--line); border-radius: 8px; }
        .session-item strong, .session-item span { display: block; }
        .session-item span { margin-top: 2px; color: var(--ink-500); font-size: 11px; }
        @media (max-width: 820px) { .account-security-grid { grid-template-columns: 1fr; } }
        @media (max-width: 680px) { .security-actions { grid-template-columns: 1fr; } }
        @media (max-width: 390px) { .security-summary-list > div { align-items: flex-start; flex-direction: column; gap: 3px; } .security-summary-list dd { text-align: left; } }
    </style>
@endsection

@section('content')
    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $user->full_name }}</h2>
                <p>{{ $user->email }}</p>
            </div>
            <span class="status-badge {{ $user->role === 'admin' ? 'processing' : ($user->role === 'technician' ? 'pending' : 'completed') }}">{{ $user->role }}</span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">Account Status</span>
                <div class="value">{{ $user->is_active ? 'Active' : 'Disabled' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Username</span>
                <div class="value">{{ $user->username }}</div>
            </div>
            <div class="info-item">
                <span class="label">Email Verified</span>
                <div class="value">{{ $user->email_verified_at ? 'Yes' : 'No' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Created</span>
                <div class="value">{{ optional($user->created_at)->format('M d, Y h:i A') ?? '-' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Last Updated</span>
                <div class="value">{{ optional($user->updated_at)->format('M d, Y h:i A') ?? '-' }}</div>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 22px;">
            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Back to List</a>
            @if(!$user->is_active)
                <form method="POST" action="{{ route('admin.users.activate', $user) }}">@csrf<button type="submit" class="btn-primary">Reactivate User</button></form>
            @elseif(auth()->id() !== $user->id)
                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Disable this user account? Existing records will be preserved.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Disable User</button>
                </form>
            @endif
        </div>
    </section>

    <div class="account-security-grid" id="account-access">
        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Account Access</h2>
                    <p>Review failed sign-ins and remove a temporary account lock when the user’s identity has been confirmed.</p>
                </div>
                @if(!$user->is_active)
                    <span class="status-badge cancelled">Disabled</span>
                @elseif($user->isAccountLocked())
                    <span class="status-badge rejected">Locked</span>
                @else
                    <span class="status-badge closed">Available</span>
                @endif
            </div>

            <dl class="security-summary-list">
                <div><dt>Failed attempts</dt><dd>{{ (int) $user->failed_login_attempts }}</dd></div>
                <div><dt>Lock expires</dt><dd>{{ $user->locked_until?->format('M d, Y h:i A') ?? 'Not locked' }}</dd></div>
                <div><dt>Two-factor authentication</dt><dd>{{ $user->isTwoFactorEnabled() ? 'Enabled' : 'Not enabled' }}</dd></div>
                <div><dt>Password change</dt><dd>{{ $user->must_change_password ? 'Required at next sign-in' : 'Not required' }}</dd></div>
                <div><dt>Active sessions</dt><dd>{{ $activeSessions->count() }}</dd></div>
                @if($user->isAdmin())<div><dt>Trusted admin devices</dt><dd>{{ $trustedDevices->count() }}</dd></div>@endif
                <div><dt>Last recorded attempt</dt><dd>{{ optional($loginAttempts->first()?->created_at)->format('M d, Y h:i A') ?? 'No history' }}</dd></div>
            </dl>
        </section>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Unlock Account</h2>
                    <p>This clears the failed-attempt counter and temporary login lock. The action is audited and the user is notified.</p>
                </div>
            </div>

            @if(!$user->is_active)
                <div class="alert alert-warning">Reactivate this account before removing its login lock.</div>
            @elseif($user->isAccountLocked() || (int) $user->failed_login_attempts > 0)
                <form method="POST" action="{{ route('admin.users.unlock', $user) }}" class="unlock-form" onsubmit="return confirm('Unlock this account and clear its failed login attempts?');">
                    @csrf
                    <div class="field">
                        <label for="unlock_reason">Reason for unlocking</label>
                        <textarea id="unlock_reason" name="unlock_reason" maxlength="500" required placeholder="Example: User identity confirmed by the MIS Office.">{{ old('unlock_reason') }}</textarea>
                        <span class="field-hint">Do not include the user’s password or authenticator code.</span>
                        @error('unlock_reason')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn-primary">Unlock Account</button>
                </form>
            @else
                <div class="empty-state">This account is not locked and has no failed-attempt counter to clear.</div>
            @endif
        </section>
    </div>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Secure Account Recovery</h2>
                <p>Send a one-time reset link, require a password change, or end access on every device. These actions are audited.</p>
            </div>
        </div>
        <div class="security-actions">
            <form method="POST" action="{{ route('admin.users.send-password-reset', $user) }}" onsubmit="return confirm('Send a one-time password-reset link to {{ $user->email }}?');">
                @csrf
                <button type="submit" class="btn-primary" @disabled(!$user->is_active)>Send Reset Link</button>
            </form>
            <form method="POST" action="{{ route('admin.users.require-password-change', $user) }}" onsubmit="return confirm('Require this user to change their password after the next verified sign-in?');">
                @csrf
                <button type="submit" class="btn-ghost" @disabled(!$user->is_active || $user->must_change_password)>Require Password Change</button>
            </form>
            <form method="POST" action="{{ route('admin.users.force-logout', $user) }}" onsubmit="return confirm('Sign this account out from every device and revoke trusted administrator devices?');">
                @csrf
                <button type="submit" class="btn-danger" @disabled(auth()->id() === $user->id)>Sign Out All Devices</button>
            </form>
        </div>
    </section>

    <div class="account-security-grid">
        <section class="panel">
            <div class="section-heading"><div><h2>Active Sessions</h2><p>Devices with a session that has not been revoked.</p></div></div>
            <div class="session-list">
                @forelse($activeSessions as $session)
                    <div class="session-item">
                        <div><strong>{{ $session->device_name ?: 'Unknown device' }}</strong><span>{{ $session->ip_address ?: 'No IP address' }}</span></div>
                        <span>{{ $session->last_activity_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="empty-state">No active sessions are recorded.</div>
                @endforelse
            </div>
        </section>
        <section class="panel">
            <div class="section-heading"><div><h2>Trusted Administrator Devices</h2><p>New administrator devices require approval when at least one trusted device exists.</p></div></div>
            <div class="session-list">
                @if(!$user->isAdmin())
                    <div class="empty-state">Trusted-device approval applies only to administrator accounts.</div>
                @else
                    @forelse($trustedDevices as $device)
                        <div class="session-item">
                            <div><strong>{{ $device->device_name ?: 'Unknown device' }}</strong><span>{{ $device->ip_address ?: 'No IP address' }}</span></div>
                            <span>{{ $device->last_used_at?->diffForHumans() ?: 'Not used yet' }}</span>
                        </div>
                    @empty
                        <div class="empty-state">The next verified sign-in becomes the first trusted device.</div>
                    @endforelse
                @endif
            </div>
        </section>
    </div>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Recent Login Activity</h2>
                <p>The latest successful and unsuccessful attempts recorded for this username or email address.</p>
            </div>
        </div>
        <div class="table-wrap" style="max-height:420px;overflow:auto;">
            <table class="compact-table">
                <thead><tr><th>Date and Time</th><th>Result</th><th>Reason</th><th>IP Address</th><th>Device</th></tr></thead>
                <tbody>
                    @forelse($loginAttempts as $attempt)
                        <tr>
                            <td>{{ optional($attempt->created_at)->format('M d, Y h:i A') ?? '-' }}</td>
                            <td><span class="status-badge {{ $attempt->success ? 'closed' : 'rejected' }}">{{ $attempt->success ? 'Successful' : 'Failed' }}</span></td>
                            <td>{{ $attempt->failure_reason ?: '-' }}</td>
                            <td>{{ $attempt->ip_address ?: '-' }}</td>
                            <td><div class="table-cell-title">{{ $attempt->device_info ?: 'Unknown device' }}</div><div class="table-cell-meta">{{ \Illuminate\Support\Str::limit($attempt->user_agent, 70) }}</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">No login activity has been recorded for this account.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
