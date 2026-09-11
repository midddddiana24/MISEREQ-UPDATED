@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Codes - Two-Factor Authentication</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #D1884F;
            --primary-dark: #B87540;
            --secondary: #2C3E50;
            --success: #27ae60;
            --warning: #f39c12;
            --light: #F8F9FA;
            --white: #FFFFFF;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #FFF8F0 0%, #FFEEDD 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary-dark);
            transform: translateX(-5px);
        }

        .recovery-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: var(--secondary);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .warning-box {
            background: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            color: #666;
            line-height: 1.6;
        }

        .warning-box strong {
            color: var(--secondary);
            display: block;
            margin-bottom: 0.5rem;
        }

        .recovery-codes-list {
            background: var(--light);
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .recovery-codes-list h3 {
            color: var(--secondary);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .codes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .recovery-code {
            background: white;
            border: 1px dashed #ddd;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--secondary);
            text-align: center;
            font-size: 0.95rem;
            word-break: break-all;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 150px;
            padding: 0.9rem 1.2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(209, 136, 79, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(209, 136, 79, 0.4);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--secondary);
            border: 2px solid #e5e5e5;
        }

        .btn-secondary:hover {
            background: #ebebeb;
            border-color: var(--secondary);
        }

        .info-box {
            background: rgba(52, 152, 219, 0.1);
            border-left: 4px solid #3498db;
            padding: 1.2rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .info-box strong {
            color: var(--secondary);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-group-item {
            flex: 1;
            min-width: 150px;
        }

        @media (max-width: 600px) {
            .recovery-card {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.4rem;
            }

            .codes-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                min-width: auto;
            }

            .button-group {
                flex-direction: column;
            }

            .btn-group-item {
                min-width: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="{{ route('user.settings') }}" class="back-link">
            <span>←</span>
            <span>Back to Settings</span>
        </a>

        <div class="recovery-card">
            <h1>🔐 Recovery Codes</h1>
            <p class="subtitle">Keep these codes safe - they can help you regain access to your account</p>

            <div class="warning-box">
                <strong>⚠ Important Security Notice</strong>
                These recovery codes are the last way to access your account if you lose access to your authenticator app. Store them in a safe place:
                <br>• Write them down on paper
                <br>• Store them in a password manager
                <br>• Keep multiple backups
                <br>• Never share them with anyone
            </div>

            <div class="recovery-codes-list">
                <h3>Your Recovery Codes</h3>
                <div class="codes-grid">
                    @foreach($recoveryCodes as $code)
                    <div class="recovery-code">{{ $code }}</div>
                    @endforeach
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="copyAllCodes()">📋 Copy All Codes</button>
                <button class="btn btn-primary" onclick="window.print()">🖨 Print Codes</button>
            </div>

            <div class="info-box">
                <strong>How to use recovery codes:</strong> If you can't access your authenticator app, use one of these recovery codes instead of your 2FA code when logging in. Each code can only be used once. After you use all codes, you'll need to set up 2FA again.
            </div>

            <div class="button-group">
                <div class="btn-group-item">
                    <a href="{{ route('user.settings') }}" class="btn btn-secondary">Done</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyAllCodes() {
            const codesJson = '<?php echo json_encode($recoveryCodes); ?>';
            const codes = JSON.parse(codesJson);
            const text = codes.join('\n');

            navigator.clipboard.writeText(text).then(() => {
                alert('All recovery codes copied to clipboard!');
            }).catch(() => {
                alert('Failed to copy to clipboard.');
            });
        }
    </script>
</body>

</html>
@endsection