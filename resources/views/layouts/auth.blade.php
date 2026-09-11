<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#174f75">
    <title>@yield('title', 'Account') - WVSU MIS Service Portal</title>
    @php
        $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
            ? asset('images/wvsu-janiuay-logo.png')
            : asset('images/wvsu-logo.png');
        $bagongPilipinasLogo = asset('images/Bagong_Pilipinas_logo.png');
    @endphp
    <style>
        :root {
            --brand: #174f75;
            --brand-dark: #103a58;
            --brand-soft: #eef5f8;
            --page: #f4f7f8;
            --surface: #ffffff;
            --ink: #1f303a;
            --muted: #64747e;
            --line: #d9e2e7;
            --danger: #a52218;
            --success: #17623b;
            --focus: #e5b827;
        }

        * { box-sizing: border-box; }
        html { min-width: 320px; }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background: var(--page);
            font-family: "Segoe UI", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }

        body::before {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image:
                url("{{ $wvsuLogo }}"),
                url("{{ $bagongPilipinasLogo }}");
            background-position:
                left 5vw center,
                right 5vw center;
            background-size:
                min(31vw, 390px) auto,
                min(27vw, 340px) auto;
            background-repeat: no-repeat;
            content: "";
            opacity: .075;
            pointer-events: none;
        }

        a { color: var(--brand); }
        button, input, select { font: inherit; }
        .site-header { z-index: 1; }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible {
            outline: 3px solid rgba(229, 184, 39, .62);
            outline-offset: 2px;
        }

        .auth-page {
            width: min(100% - 32px, 760px);
            min-height: calc(100vh - 80px);
            margin: 0 auto;
            padding: 34px 0 46px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .auth-content {
            width: 100%;
            max-width: 720px;
            padding: clamp(24px, 4vw, 36px);
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 12px 34px rgba(27, 60, 80, .08);
        }

        .auth-content.compact { max-width: 470px; }

        .auth-heading {
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e6ecef;
        }

        .auth-heading h2 {
            margin: 0 0 7px;
            color: var(--brand-dark);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(24px, 4vw, 28px);
            line-height: 1.2;
        }

        .auth-heading p {
            max-width: 590px;
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .alert {
            margin-bottom: 18px;
            padding: 11px 13px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border: 1px solid #edc7c3;
            border-radius: 7px;
            background: #fff7f6;
            color: #822119;
            font-size: 13px;
            line-height: 1.45;
        }

        .alert.success { border-color: #b8dcc7; background: #f2faf5; color: var(--success); }
        .alert svg { width: 17px; flex: 0 0 auto; margin-top: 1px; }

        .form-section {
            margin: 25px 0 15px;
            padding-bottom: 7px;
            color: var(--brand-dark);
            border-bottom: 1px solid #e6ecef;
            font-size: 12px;
            font-weight: 750;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .form-section:first-child { margin-top: 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }
        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
            color: #344955;
            font-size: 13px;
            font-weight: 700;
        }

        .optional { color: #7b8991; font-size: 11px; font-weight: 400; }
        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            width: 17px;
            height: 17px;
            color: #788993;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .input-wrap input,
        .input-wrap select {
            width: 100%;
            min-height: 44px;
            padding: 10px 40px;
            color: var(--ink);
            border: 1px solid #c8d4db;
            border-radius: 7px;
            outline: none;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .input-wrap select { appearance: none; cursor: pointer; }
        .input-wrap input::placeholder { color: #919ea6; }
        .input-wrap input:focus,
        .input-wrap select:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(23, 79, 117, .11); }
        .form-group.error .input-wrap input,
        .form-group.error .input-wrap select { border-color: #cc655d; background: #fffafa; }

        .select-arrow {
            position: absolute;
            right: 13px;
            top: 50%;
            width: 14px;
            color: #71808b;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .password-toggle {
            position: absolute;
            right: 5px;
            top: 50%;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            padding: 0;
            color: #61737e;
            border: 0;
            border-radius: 5px;
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle:hover { color: var(--brand); background: var(--brand-soft); }
        .password-toggle svg { width: 17px; }
        .error-message { margin-top: 5px; color: var(--danger); font-size: 12px; line-height: 1.4; }
        .password-hint { margin: -6px 0 17px; color: #687983; font-size: 12px; line-height: 1.5; }

        .form-options {
            margin: 2px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            font-size: 13px;
        }

        .check-label { display: inline-flex; align-items: center; gap: 8px; color: #4f626e; cursor: pointer; }
        .check-label input { width: 16px; height: 16px; margin: 0; accent-color: var(--brand); }
        .text-link { font-weight: 700; text-decoration: none; }
        .text-link:hover { text-decoration: underline; }

        .primary-button {
            width: 100%;
            min-height: 46px;
            padding: 11px 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: #fff;
            border: 1px solid var(--brand);
            border-radius: 7px;
            background: var(--brand);
            cursor: pointer;
            font-size: 14px;
            font-weight: 750;
            transition: background .15s ease, border-color .15s ease;
        }

        .primary-button:hover { border-color: var(--brand-dark); background: var(--brand-dark); }
        .primary-button svg { width: 17px; }
        .auth-footer { margin: 20px 0 0; color: var(--muted); font-size: 13px; line-height: 1.5; text-align: center; }
        .back-home { margin-top: 14px; display: flex; justify-content: center; }
        .back-home a { display: inline-flex; align-items: center; gap: 5px; color: #64747e; font-size: 12px; font-weight: 700; text-decoration: none; }
        .back-home a:hover { color: var(--brand-dark); }
        .back-home svg { width: 14px; }

        @media (max-width: 640px) {
            body { background: var(--page); }
            body::before {
                background-position:
                    left -72px top 110px,
                    right -64px bottom 28px;
                background-size:
                    235px auto,
                    205px auto;
                opacity: .06;
            }
            .auth-page { width: min(100% - 24px, 760px); padding: 20px 0 30px; }
            .auth-content { padding: 23px 18px; border-radius: 10px; box-shadow: 0 8px 22px rgba(27, 60, 80, .07); }
            .auth-heading { margin-bottom: 20px; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
        }

        @media (max-width: 380px) {
            body::before {
                background-position:
                    left -88px top 100px,
                    right -78px bottom 20px;
                opacity: .05;
            }
            .auth-page { width: calc(100% - 16px); padding-top: 12px; }
            .auth-content { padding: 20px 14px; }
            .form-options { align-items: flex-start; gap: 10px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; }
        }
    </style>
</head>
<body>
    @include('partials.public-header')

    <main class="auth-page" id="mainContent">
        <section class="auth-content @yield('content-class', 'compact')" aria-label="@yield('title', 'Account')">
            @yield('content')
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-password-toggle'));
                if (!input) return;
                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                button.setAttribute('aria-pressed', showing ? 'false' : 'true');
            });
        });
    </script>
    @include('partials.submission-feedback')
    @stack('scripts')
</body>
</html>
