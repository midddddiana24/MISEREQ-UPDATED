@extends('admin.layout')

@section('title', 'Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Manage the few system-wide controls used by MIS requests, notifications, and administrator security.')
@section('body-class', 'admin-settings-page')

@section('head')
    <style>
        .admin-settings-page .content-grid { width: 100%; max-width: 1540px; }
        .settings-workspace { display: grid; gap: 12px; }
        .settings-command {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            box-shadow: var(--shadow);
        }
        .settings-command-copy { min-width: 0; }
        .settings-command-copy h2 { margin: 0; color: var(--ink-900); font-size: 16px; }
        .settings-command-copy p { max-width: 720px; margin: 4px 0 0; color: var(--ink-500); font-size: 11px; line-height: 1.5; }
        .settings-command-actions { display: flex; align-items: center; flex: 0 0 auto; gap: 7px; }
        .settings-grid {
            display: grid;
            grid-template-columns: minmax(240px, .9fr) minmax(360px, 1.3fr) minmax(260px, 1fr);
            align-items: stretch;
            gap: 12px;
        }
        .settings-card {
            min-width: 0;
            padding: 15px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            box-shadow: var(--shadow);
        }
        .settings-card-head { margin-bottom: 13px; }
        .settings-card-kicker { display: block; margin-bottom: 4px; color: var(--brand-800); font-size: 9px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .settings-card h2 { margin: 0; color: var(--ink-900); font-size: 14px; }
        .settings-card-head p { margin: 4px 0 0; color: var(--ink-500); font-size: 10px; line-height: 1.45; }
        .settings-fields { display: grid; gap: 11px; }
        .setting-field label { display: block; margin-bottom: 5px; color: var(--ink-700); font-size: 10px; font-weight: 750; }
        .setting-field input, .setting-field select { width: 100%; min-height: 39px; padding: 8px 9px; font-size: 12px; }
        .setting-help { display: block; margin-top: 5px; color: var(--ink-500); font-size: 9px; line-height: 1.4; }
        .setting-error { display: block; margin-top: 5px; color: #a52d2d; font-size: 10px; font-weight: 650; }
        .switch-list { display: grid; gap: 7px; }
        .setting-switch {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            min-height: 60px;
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcfd;
            cursor: pointer;
        }
        .setting-switch:hover { border-color: #b8cbd9; background: #f7fafc; }
        .setting-switch-copy { min-width: 0; }
        .setting-switch-title { display: block; color: var(--ink-800); font-size: 11px; font-weight: 750; }
        .setting-switch-description { display: block; margin-top: 2px; color: var(--ink-500); font-size: 9px; line-height: 1.35; }
        .setting-toggle { position: relative; width: 36px; height: 20px; flex: 0 0 auto; }
        .setting-toggle input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .setting-toggle-track {
            position: absolute;
            inset: 0;
            border: 1px solid #a9b4bd;
            border-radius: 999px;
            background: #dfe5ea;
            transition: .18s ease;
        }
        .setting-toggle-track::after {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .25);
            transition: .18s ease;
        }
        .setting-toggle input:checked + .setting-toggle-track { border-color: var(--brand-800); background: var(--brand-800); }
        .setting-toggle input:checked + .setting-toggle-track::after { transform: translateX(16px); }
        .setting-toggle input:focus-visible + .setting-toggle-track { outline: 3px solid rgba(31, 111, 168, .25); outline-offset: 2px; }
        .security-summary { display: grid; gap: 7px; margin-top: 10px; }
        .security-stat, .delivery-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 35px;
            padding: 7px 9px;
            border-radius: 7px;
            color: var(--ink-600);
            background: #f6f8fa;
            font-size: 10px;
        }
        .security-stat strong, .delivery-row strong { color: var(--ink-800); font-size: 10px; }
        .security-note { margin: 9px 0 0; padding: 8px 9px; border-left: 3px solid #d59a2f; color: #704713; background: #fff9e9; font-size: 9px; line-height: 1.45; }
        .settings-status {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
            align-items: center;
            gap: 8px;
            padding: 11px 13px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            box-shadow: var(--shadow);
        }
        .delivery-row { min-width: 0; }
        .delivery-row span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .settings-footnote { margin: 0; color: var(--ink-500); font-size: 9px; line-height: 1.45; }
        @media (max-width: 1120px) {
            .settings-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .settings-card-security { grid-column: 1 / -1; }
            .settings-card-security .switch-list { grid-template-columns: minmax(0, 1fr) minmax(220px, .65fr); align-items: start; }
            .settings-status { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .settings-status form { grid-column: 1 / -1; justify-self: end; }
        }
        @media (max-width: 720px) {
            .settings-command { align-items: flex-start; }
            .settings-command-actions { width: 100%; }
            .settings-command-actions .btn-primary { width: 100%; }
            .settings-command { flex-direction: column; }
            .settings-grid, .settings-card-security .switch-list, .settings-status { grid-template-columns: 1fr; }
            .settings-card-security { grid-column: auto; }
            .settings-status form { grid-column: auto; justify-self: stretch; }
            .settings-status form .btn-ghost { width: 100%; }
        }
        @media (max-width: 390px) {
            .settings-command, .settings-card { padding: 12px; }
            .setting-switch { min-height: 56px; padding: 8px; }
        }
    </style>
@endsection

@section('content')
    @php
        $supportEmail = old('support_email', $systemSettings['support_email'] ?? config('mail.from.address'));
        $uploadLimit = (int) old('max_upload_size', $systemSettings['max_upload_size'] ?? 10);
        $lastUpdated = $settingsLastUpdated
            ? \Illuminate\Support\Carbon::parse($settingsLastUpdated)->format('M d, Y h:i A')
            : 'Not saved yet';
        $twoFactorRequired = (bool) old('require_2fa_admins', $securitySettings['require_2fa_admins'] ?? false);
        $clientTwoFactorRequired = (bool) old('require_2fa_clients', $securitySettings['require_2fa_clients'] ?? false);
        $clientSessionTimeout = (int) old('client_session_timeout', $securitySettings['client_session_timeout'] ?? 120);
        $maxClientSessions = (int) old('max_active_client_sessions', $securitySettings['max_active_client_sessions'] ?? 3);
    @endphp

    <div class="settings-workspace">
        <nav class="settings-tabs" aria-label="System settings sections" style="display:flex;gap:5px;width:max-content;max-width:100%;padding:4px;border:1px solid var(--line);border-radius:9px;background:#fff;overflow-x:auto;">
            <a href="{{ route('admin.settings') }}" aria-current="page" style="padding:7px 11px;border-radius:6px;color:var(--brand-900);background:var(--brand-100);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap;">Configuration</a>
            <a href="{{ route('admin.settings.operations') }}" style="padding:7px 11px;border-radius:6px;color:var(--ink-600);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap;">System Operations</a>
            <a href="{{ route('admin.operations') }}" style="padding:7px 11px;border-radius:6px;color:var(--ink-600);font-size:11px;font-weight:750;text-decoration:none;white-space:nowrap;">Reports &amp; Audit</a>
        </nav>

        <header class="settings-command">
            <div class="settings-command-copy">
                <h2>System-wide controls</h2>
                <p>Changes apply to client requests, walk-in records, Messenger attachments, email notifications, and administrator access.</p>
            </div>
            <div class="settings-command-actions">
                <button type="submit" form="systemSettingsForm" class="btn-primary">Save Settings</button>
            </div>
        </header>

        <form id="systemSettingsForm" method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')

            <div class="settings-grid">
                <section class="settings-card" aria-labelledby="serviceSettingsTitle">
                    <div class="settings-card-head">
                        <span class="settings-card-kicker">General</span>
                        <h2 id="serviceSettingsTitle">Service configuration</h2>
                        <p>Contact and file settings used across the request workflow.</p>
                    </div>

                    <div class="settings-fields">
                        <div class="setting-field">
                            <label for="support_email">MIS support email</label>
                            <input
                                id="support_email"
                                type="email"
                                name="support_email"
                                value="{{ $supportEmail }}"
                                maxlength="255"
                                autocomplete="email"
                                aria-describedby="supportEmailHelp"
                                @error('support_email') aria-invalid="true" @enderror
                                required
                            >
                            <span class="setting-help" id="supportEmailHelp">Used as the reply-to address for system emails.</span>
                            @error('support_email')<span class="setting-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="setting-field">
                            <label for="max_upload_size">Maximum file size</label>
                            <select id="max_upload_size" name="max_upload_size" aria-describedby="uploadSizeHelp" required>
                                @foreach([5, 10, 15, 20] as $size)
                                    <option value="{{ $size }}" @selected($uploadLimit === $size)>{{ $size }} MB per file</option>
                                @endforeach
                            </select>
                            <span class="setting-help" id="uploadSizeHelp">Applies consistently to client, walk-in, and Messenger uploads.</span>
                            @error('max_upload_size')<span class="setting-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                <section class="settings-card" aria-labelledby="notificationSettingsTitle">
                    <div class="settings-card-head">
                        <span class="settings-card-kicker">Communication</span>
                        <h2 id="notificationSettingsTitle">Notifications</h2>
                        <p>Turn off only the messages MIS does not want the system to create.</p>
                    </div>

                    <div class="switch-list">
                        @foreach([
                            'notify_new_requests' => ['Request confirmations', 'Email clients after a service request is submitted.'],
                            'notify_request_updates' => ['Request and Messenger updates', 'Email clients about workflow changes and new MIS messages.'],
                            'notify_completed_requests' => ['Completion confirmations', 'Email clients when requested work is completed.'],
                            'notify_admin_alerts' => ['Administrator action alerts', 'Create alerts for new requests and work requiring attention.'],
                            'security_login_alerts' => ['Account security alerts', 'Notify clients about password, 2FA, session, and new-device activity.'],
                        ] as $key => [$label, $description])
                            @php $enabled = (bool) old($key, $notificationSettings[$key] ?? true); @endphp
                            <label class="setting-switch">
                                <span class="setting-switch-copy">
                                    <span class="setting-switch-title">{{ $label }}</span>
                                    <span class="setting-switch-description">{{ $description }}</span>
                                </span>
                                <span class="setting-toggle">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" @checked($enabled)>
                                    <span class="setting-toggle-track" aria-hidden="true"></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="settings-card settings-card-security" aria-labelledby="securitySettingsTitle">
                    <div class="settings-card-head">
                        <span class="settings-card-kicker">Access</span>
                        <h2 id="securitySettingsTitle">Administrator security</h2>
                        <p>Protect access to records and system controls.</p>
                    </div>

                    <div class="switch-list">
                        <label class="setting-switch">
                            <span class="setting-switch-copy">
                                <span class="setting-switch-title">Require two-factor authentication</span>
                                <span class="setting-switch-description">Administrators without 2FA must complete setup before entering the admin area.</span>
                            </span>
                            <span class="setting-toggle">
                                <input type="hidden" name="require_2fa_admins" value="0">
                                <input type="checkbox" name="require_2fa_admins" value="1" @checked($twoFactorRequired)>
                                <span class="setting-toggle-track" aria-hidden="true"></span>
                            </span>
                        </label>

                        <div class="security-summary" aria-label="Administrator security summary">
                            <div class="security-stat"><span>Administrator accounts</span><strong>{{ $adminCount }}</strong></div>
                            <div class="security-stat"><span>Administrators with 2FA</span><strong>{{ $adminsWithTwoFactor }} of {{ $adminCount }}</strong></div>
                        </div>
                    </div>

                    <div class="settings-fields" style="margin-top:11px;">
                        <label class="setting-switch">
                            <span class="setting-switch-copy">
                                <span class="setting-switch-title">Require 2FA for client accounts</span>
                                <span class="setting-switch-description">Clients without 2FA must complete setup before using protected pages.</span>
                            </span>
                            <span class="setting-toggle">
                                <input type="hidden" name="require_2fa_clients" value="0">
                                <input type="checkbox" name="require_2fa_clients" value="1" @checked($clientTwoFactorRequired)>
                                <span class="setting-toggle-track" aria-hidden="true"></span>
                            </span>
                        </label>
                        <div class="form-grid">
                            <div class="setting-field form-col-6">
                                <label for="client_session_timeout">Client inactivity timeout</label>
                                <select id="client_session_timeout" name="client_session_timeout" required>
                                    @foreach([15 => '15 minutes', 30 => '30 minutes', 60 => '1 hour', 120 => '2 hours', 240 => '4 hours'] as $minutes => $label)
                                        <option value="{{ $minutes }}" @selected($clientSessionTimeout === $minutes)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="setting-field form-col-6">
                                <label for="max_active_client_sessions">Maximum active devices</label>
                                <select id="max_active_client_sessions" name="max_active_client_sessions" required>
                                    @foreach([1, 2, 3, 5] as $count)
                                        <option value="{{ $count }}" @selected($maxClientSessions === $count)>{{ $count }} device{{ $count === 1 ? '' : 's' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @if($adminCount > $adminsWithTwoFactor)
                        <p class="security-note">{{ $adminCount - $adminsWithTwoFactor }} administrator account(s) still need to configure two-factor authentication.</p>
                    @endif
                </section>
            </div>
        </form>

        <section class="settings-status" aria-label="Configuration status">
            <div class="delivery-row"><span>Mail connection</span><strong>{{ strtoupper($mailDriver) }}</strong></div>
            <div class="delivery-row"><span>Support recipient</span><strong>{{ $systemSettings['support_email'] ?? 'Not set' }}</strong></div>
            <div class="delivery-row"><span>Last saved</span><strong>{{ $lastUpdated }}</strong></div>
            <form method="POST" action="{{ route('admin.settings.test-email') }}">
                @csrf
                <button type="submit" class="btn-ghost">Send Test Email</button>
            </form>
        </section>

        <p class="settings-footnote">Mail-server passwords and credentials stay protected in the server environment and are never displayed on this page.</p>
    </div>
@endsection
