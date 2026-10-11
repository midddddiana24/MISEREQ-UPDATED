<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Settings - MIS Service Request System</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .settings-page {
            width: min(100%, 1240px);
            margin: 0 auto;
            padding: 22px 20px 48px;
        }

        .settings-alert {
            margin-bottom: 14px;
            padding: 11px 13px;
            border: 1px solid;
            border-radius: 7px;
            font-size: 13px;
        }

        .settings-alert.success {
            color: #17613f;
            border-color: #b9ddca;
            background: #edf9f2;
        }

        .settings-alert.error {
            color: #982f2f;
            border-color: #e5c0c0;
            background: #fff2f2;
        }

        .settings-policy { margin-bottom: 13px; padding: 11px 13px; border: 1px solid #d4e2eb; border-radius: 7px; color: #304d60; background: #f3f8fb; font-size: 12px; line-height: 1.5; }
        .settings-policy strong { color: #173d57; }

        .security-overview {
            margin-bottom: 16px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            overflow: hidden;
            border: 1px solid #dce3e8;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(25, 55, 74, .045);
        }

        .security-metric {
            min-width: 0;
            padding: 15px 18px;
            border-right: 1px solid #e3e9ed;
        }

        .security-metric:last-child {
            border-right: 0;
        }

        .security-metric span {
            display: block;
            margin-bottom: 5px;
            color: #647581;
            font-size: 11px;
            font-weight: 700;
        }

        .security-metric strong {
            display: block;
            overflow: hidden;
            color: #243b4a;
            font-size: 15px;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .settings-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(300px, .75fr);
            align-items: start;
            gap: 16px;
        }

        .settings-column {
            display: grid;
            gap: 16px;
            min-width: 0;
        }

        .settings-card {
            min-width: 0;
            padding: 21px;
            border: 1px solid #dce3e8;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(25, 55, 74, .045);
        }

        .settings-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e7edf1;
        }

        .settings-card-head h2 {
            margin: 0;
            color: #243b4a;
            font-size: 17px;
            line-height: 1.3;
        }

        .settings-card-head p {
            margin: 4px 0 0;
            color: #687985;
            font-size: 12px;
            line-height: 1.5;
        }

        .status-badge {
            flex: 0 0 auto;
            padding: 5px 9px;
            border-radius: 999px;
            color: #17613f;
            background: #e9f7ef;
            font-size: 10px;
            font-weight: 800;
        }

        .status-badge.off {
            color: #805b16;
            background: #fff4d8;
        }

        .session-list,
        .activity-list,
        .preference-list {
            display: grid;
            gap: 9px;
        }

        .session-row,
        .activity-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 14px;
            padding: 12px 13px;
            border: 1px solid #e1e8ec;
            border-radius: 7px;
            background: #fafcfd;
        }

        .session-main,
        .activity-main {
            min-width: 0;
        }

        .session-name,
        .activity-title {
            display: block;
            color: #304653;
            font-size: 13px;
            font-weight: 700;
        }

        .session-meta,
        .activity-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 12px;
            margin-top: 4px;
            color: #586d78;
            font-size: 11px;
            line-height: 1.4;
        }

        .session-current {
            color: #17613f;
            font-weight: 700;
        }

        .compact-form {
            display: grid;
            gap: 12px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .form-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .field {
            min-width: 0;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            color: #405562;
            font-size: 12px;
            font-weight: 700;
        }

        .field input {
            width: 100%;
            min-height: 42px;
            padding: 9px 11px;
            border: 1px solid #cbd7df;
            border-radius: 6px;
            background: #fff;
            font: inherit;
            font-size: 13px;
        }

        .field input:focus {
            border-color: #5791b8;
            outline: 3px solid rgba(31, 111, 168, .14);
        }

        .field-help {
            display: block;
            color: #687985;
            font-size: 11px;
            line-height: 1.5;
        }

        .field-error {
            display: block;
            margin-top: 5px;
            color: #a33131;
            font-size: 11px;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .button-danger {
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid #ddbcbc;
            border-radius: 6px;
            color: #963434;
            background: #fff;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .button-danger:hover {
            background: #fff2f2;
        }

        .preference {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            min-height: 60px;
            padding: 11px 12px;
            border-bottom: 1px solid #e4eaee;
            cursor: pointer;
        }

        .preference:first-child {
            padding-top: 2px;
        }

        .preference:last-child {
            border-bottom: 0;
        }

        .preference strong {
            display: block;
            color: #304653;
            font-size: 13px;
        }

        .preference small {
            display: block;
            margin-top: 2px;
            color: #586d78;
            font-size: 11px;
            line-height: 1.4;
        }

        .preference input {
            width: 18px;
            height: 18px;
            accent-color: #236d9e;
        }

        .twofa-setup {
            margin-top: 15px;
            padding: 16px;
            border: 1px solid #c9dce8;
            border-radius: 8px;
            background: #f5fafe;
        }

        .twofa-setup[hidden] {
            display: none;
        }

        .twofa-setup-grid {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            align-items: start;
            gap: 18px;
        }

        .twofa-qr {
            width: 200px;
            height: 200px;
            max-width: 100%;
            padding: 6px;
            border: 1px solid #d9e2e8;
            border-radius: 7px;
            background: #fff;
        }

        .secret-code {
            padding: 10px;
            border: 1px solid #d9e2e8;
            border-radius: 6px;
            color: #294152;
            background: #fff;
            font: 700 12px/1.45 Consolas, monospace;
            overflow-wrap: anywhere;
            text-align: center;
        }

        .activity-status {
            padding: 4px 7px;
            border-radius: 999px;
            color: #17613f;
            background: #e9f7ef;
            font-size: 10px;
            font-weight: 700;
        }

        .activity-status.failed {
            color: #963434;
            background: #fff0f0;
        }

        .empty-state {
            padding: 20px 12px;
            color: #586d78;
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }

        @media (max-width: 1020px) {
            .settings-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .settings-page {
                padding: 16px 12px 38px;
            }

            .security-overview {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .security-metric:nth-child(2) {
                border-right: 0;
            }

            .security-metric:nth-child(-n+2) {
                border-bottom: 1px solid #e3e9ed;
            }

            .form-grid,
            .form-grid.two {
                grid-template-columns: 1fr;
            }

            .twofa-setup-grid {
                grid-template-columns: 1fr;
            }

            .twofa-qr {
                width: 200px;
                height: auto;
                margin: auto;
            }
        }

        @media (max-width: 430px) {
            .settings-page {
                padding: 12px 8px 32px;
            }

            .security-overview {
                grid-template-columns: 1fr;
            }

            .security-metric,
            .security-metric:nth-child(2) {
                border-right: 0;
                border-bottom: 1px solid #e3e9ed;
            }

            .security-metric:last-child {
                border-bottom: 0;
            }

            .settings-card {
                padding: 16px 14px;
            }

            .settings-card-head {
                align-items: flex-start;
            }

            .session-row,
            .activity-row {
                align-items: flex-start;
                grid-template-columns: 1fr;
            }

            .form-actions>.button,
            .form-actions>.button-danger {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    @include('user.partials.theme')
    <style>
        .settings-heading-action {
            width: auto !important;
            min-width: max-content;
            min-height: 44px !important;
            padding: 10px 12px !important;
            display: inline-flex !important;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-style: solid;
            border-width: 1px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
            white-space: nowrap;
        }

        .settings-card-head .settings-heading-action {
            align-self: center;
        }

        @media (max-width: 560px) {
            #settingsProfileAction {
                width: 100% !important;
                min-width: 0;
            }

            .settings-card-head .settings-heading-action {
                width: auto !important;
                min-width: max-content;
            }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main class="settings-page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        <section class="client-page-heading" aria-labelledby="settingsTitle">
            <div>
                <span class="eyebrow">Account Protection</span>
                <h2 id="settingsTitle">Security &amp; Preferences</h2>
                <p>Manage your password, two-factor authentication, signed-in devices, and notifications.</p>
            </div>
            <a id="settingsProfileAction" href="{{ route('user.profile') }}" class="button secondary settings-heading-action">View Profile</a>
        </section>

        @if(session('success'))<div class="settings-alert success" role="status">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="settings-alert error" role="alert">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="settings-alert error" role="alert">Please review the highlighted fields below.</div>@endif

        <section class="security-overview" aria-label="Account security summary">
            <div class="security-metric"><span>Two-factor authentication</span><strong>{{ $twoFactorEnabled ? 'Enabled' : 'Not enabled' }}</strong></div>
            <div class="security-metric"><span>Active devices</span><strong>{{ $sessions->count() }}</strong></div>
            <div class="security-metric"><span>Password last changed</span><strong>{{ $passwordChangedAt ? \Illuminate\Support\Carbon::parse($passwordChangedAt)->format('M d, Y') : 'Not recorded' }}</strong></div>
            <div class="security-metric"><span>Recovery codes remaining</span><strong>{{ $twoFactorEnabled ? $remainingRecoveryCodeCount : 'Not applicable' }}</strong></div>
        </section>

        <div class="settings-layout">
            <div class="settings-column">
                <section class="settings-card" aria-labelledby="sessionsTitle">
                    <div class="settings-card-head">
                        <div>
                            <h2 id="sessionsTitle">Signed-in devices</h2>
                            <p>Review active sessions and remove devices you no longer use.</p>
                        </div>
                    </div>
                    <div class="session-list">
                        @forelse($sessions as $session)
                        <article class="session-row">
                            <div class="session-main">
                                <span class="session-name">{{ $session->device_name ?: 'Unknown device' }} @if($session->is_current)<span class="session-current">· Current device</span>@endif</span>
                                <span class="session-meta"><span>{{ $session->ip_address ?: 'Unknown IP' }}</span><span>Active {{ $session->last_activity_at->diffForHumans() }}</span></span>
                            </div>
                            @unless($session->is_current)
                            <form method="POST" action="{{ route('settings.sessions.destroy', $session) }}">
                                @csrf @method('DELETE')
                                <label class="sr-only" for="session_password_{{ $session->id }}">Current password for {{ $session->device_name ?: 'this device' }}</label>
                                <input id="session_password_{{ $session->id }}" type="password" name="current_password" autocomplete="current-password" placeholder="Current password" required>
                                <button type="submit" class="button-danger">Sign out</button>
                            </form>
                            @endunless
                        </article>
                        @empty
                        <div class="empty-state">Your current device will appear after the next page refresh.</div>
                        @endforelse
                    </div>
                    @if($sessions->where('is_current', false)->count())
                    <form method="POST" action="{{ route('settings.sessions.logout-others') }}" class="compact-form" style="margin-top:10px;">
                        @csrf
                        <div class="form-grid two">
                            <div class="field">
                                <label for="sessions_current_password">Current password</label>
                                <input id="sessions_current_password" type="password" name="current_password" autocomplete="current-password" required>
                            </div>
                            <div class="form-actions" style="align-items:end;">
                                <button type="submit" class="button-danger">Sign Out All Other Devices</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </section>

                <section class="settings-card" aria-labelledby="passwordTitle">
                    <div class="settings-card-head">
                        <div>
                            <h2 id="passwordTitle">Change password</h2>
                            <p>Updating your password automatically signs out your other devices.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('settings.updatePassword') }}" class="compact-form">
                        @csrf
                        <div class="form-grid">
                            <div class="field">
                                <label for="current_password">Current password</label>
                                <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                                @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="field">
                                <label for="password">New password</label>
                                <input id="password" type="password" name="password" autocomplete="new-password" required>
                                @error('password')<span class="field-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Confirm password</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                            </div>
                        </div>
                        <span class="field-help">Use at least 12 characters with uppercase, lowercase, number, and symbol.</span>
                        <div class="form-actions"><button type="submit" class="button">Update Password</button></div>
                    </form>
                </section>

                <section class="settings-card" aria-labelledby="twoFactorTitle">
                    <div class="settings-card-head">
                        <div>
                            <h2 id="twoFactorTitle">Two-factor authentication</h2>
                            <p>Use an authenticator app and one-time recovery codes.</p>
                        </div>
                        <span class="status-badge {{ $twoFactorEnabled ? '' : 'off' }}">{{ $twoFactorEnabled ? 'Enabled' : 'Recommended' }}</span>
                    </div>

                    @if(!$twoFactorEnabled)
                    <form id="beginTwoFactorForm" class="compact-form" data-setup-url="{{ route('settings.2fa.setup') }}">
                        <div class="form-grid two">
                            <div class="field">
                                <label for="twofa_setup_password">Current password</label>
                                <input id="twofa_setup_password" type="password" name="current_password" autocomplete="current-password" required>
                                <span class="field-error" id="twofaSetupError"></span>
                            </div>
                            <div class="form-actions" style="align-items:end;"><button type="submit" class="button">Start Secure Setup</button></div>
                        </div>
                    </form>
                    <div class="twofa-setup" id="twoFactorSetup" hidden>
                        <div class="twofa-setup-grid">
                            <img id="twoFactorQr" class="twofa-qr" alt="Authenticator QR code">
                            <div>
                                <div class="field"><label>Manual setup key</label>
                                    <div class="secret-code" id="twoFactorSecret"></div>
                                </div>
                                <form method="POST" action="{{ route('settings.2fa.enable') }}" class="compact-form" style="margin-top:9px;">
                                    @csrf
                                    <div class="field">
                                        <label for="verification_code">Six-digit authenticator code</label>
                                        <input id="verification_code" name="verification_code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
                                        @error('verification_code')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-actions"><button type="submit" class="button">Verify and Enable</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="form-actions">
                        <a href="{{ route('settings.2fa.recovery-codes') }}" class="button secondary">Recovery Codes</a>
                    </div>
                    <div class="form-grid two" style="margin-top:11px;">
                        <form method="POST" action="{{ route('settings.2fa.recovery-codes.regenerate') }}" class="compact-form">
                            @csrf
                            <div class="field"><label for="recovery_password">Password</label><input id="recovery_password" type="password" name="current_password" autocomplete="current-password" required></div>
                            <div class="field"><label for="recovery_code">Authenticator code</label><input id="recovery_code" name="verification_code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required></div>
                            <button type="submit" class="button secondary">Generate New Codes</button>
                        </form>
                        <form method="POST" action="{{ route('settings.2fa.disable') }}" class="compact-form">
                            @csrf
                            <div class="field"><label for="disable_password">Password</label><input id="disable_password" type="password" name="current_password" autocomplete="current-password" required></div>
                            <div class="field"><label for="disable_code">Authenticator code</label><input id="disable_code" name="verification_code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required></div>
                            <button type="submit" class="button-danger">Disable 2FA</button>
                        </form>
                    </div>
                    @endif
                </section>
            </div>

            <div class="settings-column">
                <section class="settings-card" aria-labelledby="preferencesTitle">
                    <div class="settings-card-head">
                        <div>
                            <h2 id="preferencesTitle">Notification preferences</h2>
                            <p>Choose which updates may also be delivered by email.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('settings.updateNotifications') }}" class="compact-form">
                        @csrf
                        <div class="preference-list">
                            @foreach([
                            'notify_email_enabled' => ['Email delivery', 'Master control for optional account emails.'],
                            'notify_request_updates' => ['Request updates', 'Status changes and new messages from MIS.'],
                            'notify_request_reminders' => ['Request reminders', 'Reminders when your response or confirmation is needed.'],
                            'notify_security_alerts' => ['Optional security emails', 'Account locks, 2FA setup, and session activity. Password changes, 2FA removal, new devices, and email changes still send security emails.'],
                            ] as $key => [$label, $description])
                            <label class="preference">
                                <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
                                <input type="checkbox" name="{{ $key }}" value="1" @checked($user->{$key})>
                            </label>
                            @endforeach
                        </div>
                        <div class="form-actions"><button type="submit" class="button">Save Preferences</button></div>
                    </form>
                </section>

                <section class="settings-card" aria-labelledby="activityTitle">
                    <div class="settings-card-head">
                        <div>
                            <h2 id="activityTitle">Recent sign-in activity</h2>
                            <p>Review the latest successful and unsuccessful attempts.</p>
                        </div>
                        <a href="{{ route('user.security') }}" class="button secondary settings-heading-action">View All</a>
                    </div>
                    <div class="settings-policy"><strong>Sign-in protection:</strong> {{ $loginLockThreshold }} incorrect passwords within {{ $loginFailureWindowMinutes }} minutes temporarily lock your account for {{ $loginLockMinutes }} minutes. If locked, wait for the time shown on the sign-in page or contact the MIS Office.</div>
                    <div class="activity-list">
                        @forelse($loginAttempts as $attempt)
                        <article class="activity-row">
                            <div class="activity-main">
                                <span class="activity-title">{{ $attempt->device_info ?: 'Unknown device' }}</span>
                                <span class="activity-meta"><span>{{ $attempt->ip_address }}</span><span>{{ $attempt->created_at->diffForHumans() }}</span></span>
                            </div>
                            <span class="activity-status {{ $attempt->success ? '' : 'failed' }}">{{ $attempt->success ? 'Successful' : 'Failed' }}</span>
                        </article>
                        @empty
                        <div class="empty-state">No sign-in activity is available yet.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>
        (() => {
            const form = document.getElementById('beginTwoFactorForm');
            if (!form) return;
            const panel = document.getElementById('twoFactorSetup');
            const qr = document.getElementById('twoFactorQr');
            const secret = document.getElementById('twoFactorSecret');
            const error = document.getElementById('twofaSetupError');
            const button = form.querySelector('button');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            form.addEventListener('submit', async event => {
                event.preventDefault();
                error.textContent = '';
                button.disabled = true;
                button.textContent = 'Preparing...';
                try {
                    const response = await fetch(form.dataset.setupUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: new FormData(form)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        const message = data.error || Object.values(data.errors || {}).flat()[0] || 'Unable to start setup.';
                        throw new Error(message);
                    }
                    qr.src = data.qr_code;
                    secret.textContent = data.secret;
                    panel.hidden = false;
                    panel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                } catch (exception) {
                    error.textContent = exception.message;
                } finally {
                    button.disabled = false;
                    button.textContent = 'Start Secure Setup';
                }
            });
        })();
    </script>
</body>

</html>
