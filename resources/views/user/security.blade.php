<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security & Login History - MIS Service Request System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 22px 20px 48px;
        }

        .nav-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-tabs a {
            padding: 8px 14px;
            color: #334155;
            font-weight: 700;
            text-decoration: none;
        }

        .security-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 16px;
        }

        .security-card {
            padding: 18px;
            border: 1px solid #d9e2e8;
            border-radius: 8px;
            background: #fff;
        }

        .security-card strong {
            display: block;
            margin-bottom: 6px;
            color: #243442;
            font-size: 15px;
        }

        .security-card p {
            color: #687784;
            font-size: 13px;
            line-height: 1.5;
        }

        .security-card.good {
            border-left: 3px solid #18794e;
        }

        .security-card.tip {
            border-left: 3px solid #d29a24;
        }

        .login-list {
            display: grid;
            gap: 10px;
        }

        .login-entry {
            padding: 15px;
            border: 1px solid #e0e7ec;
            border-radius: 7px;
            background: #fbfcfd;
        }

        .login-entry-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .login-time {
            color: #586d78;
            font-size: 12px;
        }

        .login-grid {
            display: grid;
            grid-template-columns: .8fr 1.4fr 1fr;
            gap: 14px;
        }

        .login-label {
            margin-bottom: 3px;
            color: #586d78;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .login-value {
            color: #3f505d;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .login-value.ip {
            font-family: Consolas, monospace;
        }

        .login-status {
            padding: 4px 8px;
            display: inline-flex;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .login-status.success {
            color: #18794e;
            background: #eaf7f0;
        }

        .login-status.failed {
            color: #b24242;
            background: #fceeee;
        }

        .recommendations {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .recommendation {
            padding: 14px;
            border: 1px solid #e0e7ec;
            border-radius: 7px;
            background: #f8fafc;
        }

        .recommendation strong {
            display: block;
            margin-bottom: 4px;
            color: #344754;
            font-size: 13px;
        }

        .recommendation p {
            color: #586d78;
            font-size: 12px;
            line-height: 1.5;
        }

        .empty-state {
            padding: 34px 20px;
            color: #586d78;
            text-align: center;
        }

        .panel+.panel {
            margin-top: 16px;
        }

        @media (max-width: 760px) {

            .security-summary,
            .login-grid {
                grid-template-columns: 1fr;
            }

            .recommendations {
                grid-template-columns: 1fr;
            }

            .login-entry-head {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
    @include('user.partials.theme')
</head>

<body>
    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        <section class="client-page-heading" aria-labelledby="securityTitle">
            <div>
                <span class="eyebrow">Account Protection</span>
                <h2 id="securityTitle">Security & Login History</h2>
                <p>Review recent sign-in activity and follow practical steps to keep your account secure.</p>
            </div>
            <a href="{{ route('user.profile') }}" class="button secondary">Back to Profile</a>
        </section>

        <section class="security-summary" aria-label="Account security status">
            <div class="security-card good"><strong>Secure password</strong>
                <p>Your account is password protected. Never share your credentials with anyone.</p>
            </div>
            <div class="security-card good"><strong>Active session</strong>
                <p>You are currently signed in through an authenticated and protected session.</p>
            </div>
            <div class="security-card tip"><strong>Review activity</strong>
                <p>Check the sign-in history below and report activity you do not recognize.</p>
            </div>
        </section>

        <section class="panel">
            <div class="section-header">
                <h1>Recent Login Activity</h1>
                <small>Showing up to the last 20 sign-in attempts for your account.</small>
            </div>

            @if($loginAttempts && $loginAttempts->count() > 0)
            <div class="login-list">
                @foreach($loginAttempts as $attempt)
                <article class="login-entry">
                    <div class="login-entry-head">
                        <span class="login-status {{ $attempt->success ? 'success' : 'failed' }}">{{ $attempt->success ? 'Successful login' : 'Failed attempt' }}</span>
                        <span class="login-time">{{ $attempt->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="login-grid">
                        <div>
                            <div class="login-label">IP Address</div>
                            <div class="login-value ip">{{ $attempt->ip_address ?? 'Unknown' }}</div>
                        </div>
                        <div>
                            <div class="login-label">Device Information</div>
                            <div class="login-value">{{ $attempt->device_info ?? 'Unknown device' }}</div>
                        </div>
                        <div>
                            <div class="login-label">Date & Time</div>
                            <div class="login-value">{{ $attempt->created_at->format('M d, Y · h:i A') }}</div>
                        </div>
                        @if(!$attempt->success && $attempt->failure_reason)
                        <div>
                            <div class="login-label">Failure Reason</div>
                            <div class="login-value">{{ $attempt->failure_reason }}</div>
                        </div>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
            @else
            <div class="empty-state">No login history is available yet.</div>
            @endif
        </section>

        <section class="panel">
            <div class="section-header">
                <h1>Security Recommendations</h1><small>Simple habits that help protect your university account.</small>
            </div>
            <div class="recommendations">
                <div class="recommendation"><strong>Use a strong password</strong>
                    <p>Combine uppercase and lowercase letters, numbers, and symbols.</p>
                </div>
                <div class="recommendation"><strong>Change it after suspicious activity</strong>
                    <p>Update your password if you suspect it was exposed, and never reuse an old one.</p>
                </div>
                <div class="recommendation"><strong>Monitor sign-in activity</strong>
                    <p>Review this page when you suspect unusual access to your account.</p>
                </div>
                <div class="recommendation"><strong>Sign out on shared devices</strong>
                    <p>Always log out after using a public or shared computer.</p>
                </div>
            </div>
        </section>
    </main>
</body>

</html>