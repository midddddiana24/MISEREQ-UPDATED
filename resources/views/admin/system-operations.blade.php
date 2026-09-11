@extends('admin.layout')

@section('title', 'System Operations')
@section('page-title', 'System Operations')
@section('page-subtitle', 'Monitor the services that keep requests, documents, security records, and backups available.')
@section('body-class', 'admin-system-operations-page')

@section('head')
    <style>
        .admin-system-operations-page .content-grid { width: 100%; max-width: 1540px; }
        .operations-shell { display: grid; gap: 12px; }
        .settings-tabs { display: flex; gap: 5px; padding: 4px; border: 1px solid var(--line); border-radius: 9px; background: #fff; width: max-content; max-width: 100%; }
        .settings-tab { padding: 7px 11px; border-radius: 6px; color: var(--ink-600); font-size: 11px; font-weight: 750; text-decoration: none; }
        .settings-tab:hover, .settings-tab.active { color: var(--brand-900); background: var(--brand-100); }
        .health-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .health-card, .operations-card { min-width: 0; padding: 14px; border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: var(--shadow); }
        .health-card { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .health-copy span { display: block; color: var(--ink-500); font-size: 9px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .health-copy strong { display: block; margin-top: 4px; color: var(--ink-900); font-size: 14px; }
        .health-state { flex: 0 0 auto; padding: 4px 7px; border-radius: 999px; color: #17623b; background: #e8f6ee; font-size: 9px; font-weight: 800; }
        .health-state.attention { color: #8a4d12; background: #fff2d9; }
        .operations-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; align-items: start; }
        .operations-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid var(--line); }
        .operations-card h2 { margin: 0; color: var(--ink-900); font-size: 14px; }
        .operations-card-head p { margin: 3px 0 0; color: var(--ink-500); font-size: 10px; line-height: 1.45; }
        .operations-list { display: grid; gap: 7px; margin: 0; }
        .operations-list > div { display: grid; grid-template-columns: minmax(100px, .8fr) minmax(0, 1fr); gap: 10px; padding: 7px 8px; border-radius: 7px; background: #f7f9fa; font-size: 10px; }
        .operations-list dt { color: var(--ink-500); font-weight: 700; }
        .operations-list dd { min-width: 0; margin: 0; color: var(--ink-800); font-weight: 750; text-align: right; overflow-wrap: anywhere; }
        .operations-table-wrap { max-height: 290px; overflow: auto; border: 1px solid var(--line); border-radius: 8px; }
        .operations-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .operations-table th, .operations-table td { padding: 8px 9px; border-bottom: 1px solid var(--line); text-align: left; }
        .operations-table th { position: sticky; top: 0; color: var(--ink-600); background: #f7f9fa; font-size: 9px; text-transform: uppercase; }
        .operations-table tr:last-child td { border-bottom: 0; }
        .secure-action { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px; margin-top: 11px; }
        .secure-action input { min-width: 0; min-height: 36px; font-size: 11px; }
        .secure-action button { white-space: nowrap; }
        .operations-note { margin: 8px 0 0; color: var(--ink-500); font-size: 9px; line-height: 1.45; }
        .operations-error { grid-column: 1 / -1; color: #a52d2d; font-size: 10px; font-weight: 700; }
        @media (max-width: 1060px) { .health-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 760px) {
            .operations-grid, .health-grid { grid-template-columns: 1fr; }
            .settings-tabs { width: 100%; overflow-x: auto; }
            .settings-tab { flex: 1 0 auto; text-align: center; }
        }
        @media (max-width: 390px) {
            .health-card, .operations-card { padding: 12px; }
            .secure-action { grid-template-columns: 1fr; }
            .secure-action button { width: 100%; }
        }
    </style>
@endsection

@section('content')
    <div class="operations-shell">
        <nav class="settings-tabs" aria-label="System settings sections">
            <a class="settings-tab" href="{{ route('admin.settings') }}">Configuration</a>
            <a class="settings-tab active" href="{{ route('admin.settings.operations') }}" aria-current="page">System Operations</a>
            <a class="settings-tab" href="{{ route('admin.operations') }}">Reports &amp; Audit</a>
        </nav>

        <section class="health-grid" aria-label="System health">
            @foreach([
                ['Database', $checks['database'], 'Records and workflows'],
                ['Cache', $checks['cache'], strtoupper($cacheDriver)],
                ['Secure storage', $checks['storage'], 'Protected files'],
                ['Scheduler', $schedulerHealthy, $schedulerSeenAt ? $schedulerSeenAt->diffForHumans() : 'No heartbeat recorded'],
            ] as [$label, $healthy, $detail])
                <article class="health-card">
                    <div class="health-copy"><span>{{ $label }}</span><strong>{{ $detail }}</strong></div>
                    <span class="health-state {{ $healthy ? '' : 'attention' }}">{{ $healthy ? 'Operational' : 'Check' }}</span>
                </article>
            @endforeach
        </section>

        <div class="operations-grid">
            <section class="operations-card">
                <div class="operations-card-head"><div><h2>Background processing</h2><p>PDF preparation and other queued work.</p></div></div>
                <dl class="operations-list">
                    <div><dt>Queue connection</dt><dd>{{ strtoupper($queueDriver) }}</dd></div>
                    <div><dt>Pending jobs</dt><dd>{{ number_format($pendingJobs) }}</dd></div>
                    <div><dt>Failed jobs</dt><dd>{{ number_format($failedJobs) }}</dd></div>
                    <div><dt>Latest failure</dt><dd>{{ $latestFailedJob?->failed_at ? \Illuminate\Support\Carbon::parse($latestFailedJob->failed_at)->format('M d, Y h:i A') : 'None recorded' }}</dd></div>
                </dl>
                <p class="operations-note">The scheduler processes queued work every minute. Failed jobs remain available for protected server-side investigation.</p>
            </section>

            <section class="operations-card">
                <div class="operations-card-head"><div><h2>Session and retention</h2><p>Shared account state and privacy cleanup periods.</p></div></div>
                <dl class="operations-list">
                    <div><dt>Session storage</dt><dd>{{ strtoupper($sessionDriver) }}</dd></div>
                    @foreach($privacyRetention as $label => $days)
                        <div><dt>{{ $label }}</dt><dd>{{ number_format($days) }} days</dd></div>
                    @endforeach
                </dl>
                <form method="POST" action="{{ route('admin.settings.operations.privacy-prune') }}" class="secure-action">
                    @csrf
                    <label class="sr-only" for="privacy_password">Current password</label>
                    <input id="privacy_password" type="password" name="current_password" autocomplete="current-password" placeholder="Current password to confirm" required>
                    <button type="submit" class="btn-ghost">Run Cleanup</button>
                    @error('current_password', 'privacy')<span class="operations-error">{{ $message }}</span>@enderror
                </form>
            </section>

            <section class="operations-card">
                <div class="operations-card-head">
                    <div><h2>Verified backups</h2><p>Database and protected files retained for {{ $backupRetentionDays }} days.</p></div>
                    <span class="health-state {{ $latestBackup ? '' : 'attention' }}">{{ $latestBackup ? 'Available' : 'None' }}</span>
                </div>
                <div class="operations-table-wrap">
                    <table class="operations-table">
                        <thead><tr><th>Archive</th><th>Created</th><th>Size</th></tr></thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td>{{ $backup['name'] }}</td>
                                    <td>{{ $backup['created_at']->format('M d, Y h:i A') }}</td>
                                    <td>{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">No verified backup archive is available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('admin.settings.operations.backup') }}" class="secure-action">
                    @csrf
                    <label class="sr-only" for="backup_password">Current password</label>
                    <input id="backup_password" type="password" name="current_password" autocomplete="current-password" placeholder="Current password to confirm" required>
                    <button type="submit" class="btn-primary">Create Backup</button>
                    @error('current_password', 'backup')<span class="operations-error">{{ $message }}</span>@enderror
                </form>
                <p class="operations-note">Restore remains a protected server operation to prevent accidental replacement of live university records.</p>
            </section>

            <section class="operations-card">
                <div class="operations-card-head"><div><h2>Delivery and recovery</h2><p>Production communication and backup redundancy readiness.</p></div></div>
                <dl class="operations-list">
                    <div><dt>Mail connection</dt><dd>{{ strtoupper($mailDriver) }} · {{ $mailConfigured ? 'Configured' : 'Needs setup' }}</dd></div>
                    <div><dt>Secondary backup</dt><dd>{{ $secondaryBackupConfigured ? 'Configured' : 'Network path needed' }}</dd></div>
                    <div><dt>HTTPS enforcement</dt><dd>{{ config('security.force_https') ? 'Enabled' : 'Awaiting certificate' }}</dd></div>
                    <div><dt>Secure session cookie</dt><dd>{{ config('session.secure') ? 'Enabled' : 'Enabled with HTTPS' }}</dd></div>
                </dl>
                <p class="operations-note">Passwords, mail credentials, database credentials, and backup contents are never displayed in the admin interface.</p>
            </section>
        </div>
    </div>
@endsection
