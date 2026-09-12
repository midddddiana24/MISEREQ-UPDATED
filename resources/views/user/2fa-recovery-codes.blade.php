<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Codes - MIS Service Request System</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .recovery-page {
            width: min(100%, 1040px);
            margin: 0 auto;
            padding: 22px 20px 48px;
        }

        .recovery-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            align-items: start;
            gap: 16px;
        }

        .recovery-panel,
        .help-panel {
            padding: 22px;
            border: 1px solid #dce3e8;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(25, 55, 74, .045);
        }

        .notice {
            margin-bottom: 20px;
            padding: 12px 14px;
            color: #6f531b;
            border: 1px solid #ead6a5;
            border-radius: 6px;
            background: #fff9e9;
            font-size: 13px;
            line-height: 1.5;
        }

        .codes-heading {
            margin: 0 0 5px;
            color: #243b4a;
            font-size: 17px;
        }

        .codes-copy {
            margin: 0 0 17px;
            color: #687985;
            font-size: 12px;
            line-height: 1.5;
        }

        .recovery-codes-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .recovery-code {
            padding: 12px;
            color: #185982;
            border: 1px dashed #8eb4cc;
            border-radius: 6px;
            background: #f5fafe;
            font: 700 15px/1.3 Consolas, monospace;
            letter-spacing: .08em;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .empty-state {
            grid-column: 1 / -1;
            padding: 24px 16px;
            color: #647581;
            border: 1px solid #e1e8ec;
            border-radius: 7px;
            background: #fafcfd;
            font-size: 13px;
            line-height: 1.6;
            text-align: center;
        }

        .button-group {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .help-panel h3 {
            margin: 0 0 12px;
            color: #243b4a;
            font-size: 15px;
        }

        .help-panel ol {
            margin: 0;
            padding-left: 20px;
            color: #60717d;
            font-size: 12px;
            line-height: 1.65;
        }

        .help-panel li+li {
            margin-top: 8px;
        }

        @media (max-width: 780px) {
            .recovery-layout {
                grid-template-columns: 1fr;
            }

            .help-panel {
                grid-row: 1;
            }
        }

        @media (max-width: 520px) {
            .recovery-page {
                padding: 14px 10px 36px;
            }

            .recovery-panel,
            .help-panel {
                padding: 16px 14px;
            }

            .recovery-codes-list {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group>* {
                width: 100%;
                justify-content: center;
            }
        }

        @media print {

            .site-header,
            .nav-shell,
            .client-page-heading,
            .help-panel,
            .notice,
            .button-group {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .recovery-page {
                width: 100%;
                padding: 0;
            }

            .recovery-layout {
                display: block;
            }

            .recovery-panel {
                border: 0;
                box-shadow: none;
            }
        }
    </style>
    @include('user.partials.theme')
</head>

<body>
    @php
    $isAdmin = auth()->user()?->isAdmin() ?? false;
    $settingsRoute = $isAdmin ? route('admin.settings') : route('user.settings');
    @endphp
    @include('partials.header')

    <main class="recovery-page" id="mainContent" tabindex="-1">
        @unless($isAdmin)
        @include('user.partials.navigation')
        @endunless

        <section class="client-page-heading" aria-labelledby="recoveryTitle">
            <div>
                <span class="eyebrow">Account Protection</span>
                <h2 id="recoveryTitle">Recovery Codes</h2>
                <p>Use these one-time codes only when your authenticator app is unavailable.</p>
            </div>
            <a href="{{ $settingsRoute }}" class="button secondary">Back to Settings</a>
        </section>

        <div class="recovery-layout">
            <section class="recovery-panel" aria-labelledby="codesTitle">
                <div class="notice"><strong>Keep these private.</strong> Each code can be used once, and anyone who has one may be able to access your account.</div>
                <h3 class="codes-heading" id="codesTitle">Your recovery codes</h3>
                <p class="codes-copy">Save a copy now. For security, generated codes are not shown again after you leave this page.</p>

                <div class="recovery-codes-list">
                    @forelse($recoveryCodes as $code)
                    <div class="recovery-code">{{ $code }}</div>
                    @empty
                    <div class="empty-state">You have {{ $remainingCodeCount ?? 0 }} unused recovery code(s). The codes are only displayed immediately after generation. Generate a new set from Security Settings if you no longer have your saved copy.</div>
                    @endforelse
                </div>

                <div class="button-group">
                    @if(count($recoveryCodes))
                    <button type="button" onclick="window.print()" class="button">Print Codes</button>
                    <button type="button" onclick="downloadCodes()" class="button secondary">Download Codes</button>
                    @endif
                    <a href="{{ $settingsRoute }}" class="button secondary">Done</a>
                </div>
            </section>

            <aside class="help-panel" aria-labelledby="storageTitle">
                <h3 id="storageTitle">Store them safely</h3>
                <ol>
                    <li>Save them in a password manager, encrypted file, or secure offline location.</li>
                    <li>Use only one code when you cannot access your authenticator.</li>
                    <li>Never send a code by message or share it with another person.</li>
                </ol>
            </aside>
        </div>
    </main>

    <script>
        function downloadCodes() {
            const codes = @json($recoveryCodes);
            const content = 'MIS Service Request - 2FA Recovery Codes\n' +
                'Generated: ' + new Date().toLocaleString() + '\n\n' +
                'Keep these codes safe and never share them.\n\n' +
                codes.join('\n') + '\n\nUser: {{ auth()->user()->email }}';
            const link = document.createElement('a');
            link.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(content);
            link.download = '2fa-recovery-codes-' + new Date().toISOString().split('T')[0] + '.txt';
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    </script>
</body>

</html>