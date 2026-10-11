<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-visual">
    <meta name="theme-color" content="#103d60">
    <title>@yield('title', 'Account') - WVSU MIS Service Portal</title>
    <style>
        :root {
            --brand: #0b5e92;
            --brand-dark: #103d60;
            --brand-soft: #e9f3f8;
            --page: #f4f7f8;
            --surface: #ffffff;
            --ink: #19354a;
            --muted: #415b6d;
            --line: #c6d7e2;
            --danger: #a52218;
            --success: #17623b;
            --focus: #f4cc59;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            margin: 0;
            overflow-x: hidden;
            color: var(--ink);
            background: linear-gradient(112deg, #103d60 0%, #0b5e92 65%, #167cb1 100%);
            font-family: "Segoe UI", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }

        body::after {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: radial-gradient(circle at 88% 0%, transparent 0 18%, rgba(255, 255, 255, .055) 18% 25%, transparent 25% 32%, rgba(255, 255, 255, .035) 32% 39%, transparent 39%);
            content: "";
            pointer-events: none;
        }

        body::before {
            position: absolute;
            top: 50%;
            right: max(36px, calc((100% - 1080px) / 2));
            width: min(30vw, 340px);
            aspect-ratio: 1;
            z-index: 1;
            border-radius: 50%;
            background: url("{{ asset('images/janiuay2-1021x1024.jpg') }}") center / contain no-repeat;
            content: "";
            opacity: .52;
            transform: translateY(-50%);
            pointer-events: none;
        }

        a {
            color: var(--brand);
        }

        button,
        input,
        select {
            font: inherit;
        }

        .site-header {
            z-index: 2;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible {
            outline: 3px solid rgba(229, 184, 39, .62);
            outline-offset: 2px;
        }

        .auth-page {
            width: min(100% - 64px, 1080px);
            flex: 1;
            min-width: 0;
            margin: 0 auto;
            padding: 28px 0 46px;
            position: relative;
            z-index: 2;
        }

        .auth-stack {
            width: 100%;
            max-width: 720px;
        }

        .auth-stack.compact { max-width: 470px; }

        .auth-navigation {
            margin: 0 0 13px;
        }

        .auth-navigation a {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-navigation a:hover { text-decoration: underline; }
        .auth-navigation svg { width: 18px; height: 18px; }

        .auth-content {
            width: 100%;
            max-width: none;
            padding: clamp(24px, 4vw, 36px);
            border: 1px solid rgba(255, 255, 255, .82);
            border-top: 5px solid var(--focus);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 20px 50px rgba(4, 38, 83, .28), 0 3px 12px rgba(4, 38, 83, .14);
        }

        .auth-content.compact { max-width: none; }

        .auth-heading {
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e6ecef;
        }

        .auth-heading h1,
        .auth-heading h2 {
            margin: 0 0 7px;
            color: var(--brand-dark);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: clamp(28px, 3.2vw, 34px);
            font-weight: 750;
            letter-spacing: -.03em;
            line-height: 1.2;
        }

        .auth-heading p {
            max-width: 590px;
            margin: 0;
            color: var(--muted);
            font-size: 16px;
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

        .alert.success {
            border-color: #b8dcc7;
            background: #f2faf5;
            color: var(--success);
        }

        .alert svg {
            width: 17px;
            flex: 0 0 auto;
            margin-top: 1px;
        }

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

        .form-section:first-child {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
            gap: 0 18px;
        }

        .form-group {
            min-width: 0;
            margin-bottom: 16px;
        }

        .form-group label {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
            color: #344955;
            font-size: 15px;
            font-weight: 700;
        }

        .optional {
            color: var(--muted);
            font-size: 11px;
            font-weight: 400;
        }

        .input-wrap {
            position: relative;
            min-width: 0;
        }

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
            font-size: 16px;
            color: var(--ink);
            border: 1px solid #c8d4db;
            border-radius: 7px;
            outline: none;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .input-wrap select {
            appearance: none;
            cursor: pointer;
        }

        .input-wrap input::placeholder {
            color: #919ea6;
        }

        .input-wrap input:focus,
        .input-wrap select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(23, 79, 117, .11);
        }

        .form-group.error .input-wrap input,
        .form-group.error .input-wrap select {
            border-color: #cc655d;
            background: #fffafa;
        }

        .select-arrow {
            position: absolute;
            right: 13px;
            top: 50%;
            width: 14px;
            color: #586d78;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .password-toggle {
            position: absolute;
            right: 5px;
            top: 50%;
            width: 44px;
            height: 44px;
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

        .password-toggle:hover {
            color: var(--brand);
            background: var(--brand-soft);
        }

        .password-toggle svg {
            width: 17px;
        }

        .error-message {
            margin-top: 5px;
            color: var(--danger);
            font-size: 12px;
            line-height: 1.4;
        }

        .password-hint {
            margin: -6px 0 17px;
            color: #687983;
            font-size: 12px;
            line-height: 1.5;
        }

        .form-options {
            margin: 2px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            font-size: 13px;
        }

        .check-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f626e;
            cursor: pointer;
        }

        .check-label input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--brand);
        }

        .text-link {
            font-weight: 700;
            text-decoration: none;
        }

        .text-link:hover {
            text-decoration: underline;
        }

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
            font-size: 16px;
            font-weight: 750;
            transition: background .15s ease, border-color .15s ease;
        }

        .primary-button:hover {
            border-color: var(--brand-dark);
            background: var(--brand-dark);
        }

        .primary-button svg {
            width: 17px;
        }

        .auth-footer {
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
            text-align: center;
        }

        .back-home {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }

        .back-home a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #64747e;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .back-home a:hover {
            color: var(--brand-dark);
        }

        .back-home svg {
            width: 14px;
        }

        .auth-content { min-width: 0; overflow-wrap: anywhere; }
        .auth-heading p, .auth-footer, .form-options { font-size: 15px; line-height: 1.6; }
        .form-group label, .primary-button { font-size: 16px; }
        .password-hint, .error-message, .back-home a { font-size: 13px; line-height: 1.5; }
        .input-wrap input, .input-wrap select { min-width: 0; min-height: 48px; padding-right: 48px; }
        .input-wrap input:not(:has(~ .password-toggle)) { padding-right: 12px; }
        .input-wrap:not(:has(.input-icon)) input { padding-left: 12px; }
        input, select, button { max-width: 100%; }
        input { scroll-margin-block: 24px; }
        .form-options { flex-wrap: wrap; gap: 8px 16px; }
        .check-label, .form-options .text-link, .back-home a { min-height: 44px; display: inline-flex; align-items: center; }

        .compact-select-button {
            width: 100%; min-width: 0; min-height: 44px; padding: 10px 36px 10px 40px;
            border: 1px solid #c8d4db; border-radius: 7px; background: #fff;
            color: var(--ink); font-size: 16px; line-height: 1.4; text-align: left;
            cursor: pointer; overflow-wrap: anywhere;
        }
        .compact-select-list {
            max-height: 180px; max-height: min(180px, 30svh); overflow-y: auto;
            overscroll-behavior: contain; margin-top: 4px; padding: 4px;
            border: 1px solid #c8d4db; border-radius: 7px; background: #fff;
        }
        .compact-select-option {
            padding: 9px 10px; min-height: 40px; font-size: 14px; line-height: 1.5;
            border-radius: 4px; cursor: pointer; overflow-wrap: anywhere;
        }
        .compact-select-option[aria-selected="true"] { color: var(--brand-dark); font-weight: 700; }
        .compact-select-option.is-active, .compact-select-option:hover { background: var(--brand-soft); }
        .compact-select-button[aria-invalid="true"] { border-color: var(--danger); }
        .select-arrow { pointer-events: none; }

        @media (min-width: 901px) {
            body::before {
                top: 50%;
                right: auto;
                left: calc(50% + 235px);
                width: min(45vw, 680px);
                opacity: .3;
                transform: translate(-50%, -50%);
            }

            body:has(.auth-stack:not(.compact))::before {
                left: calc(50% + 360px);
            }

            .auth-page {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .auth-stack { margin: auto; }
        }

        @media (min-width: 901px) and (max-width: 1100px) {
            body:has(.auth-stack:not(.compact))::before {
                width: min(45vw, calc(100vw - 720px));
            }
        }

        @media (max-width: 760px) {
            body::before {
                top: 48%;
                right: 10px;
                bottom: auto;
                width: min(38vw, 280px);
                opacity: .42;
                transform: translateY(-50%);
            }

            .auth-page {
                width: min(100% - 32px, 720px);
                padding: 12px 0 24px;
            }

            .auth-content {
                padding: 20px 18px;
                border-radius: 10px;
                box-shadow: 0 14px 32px rgba(4, 38, 83, .24), 0 2px 8px rgba(4, 38, 83, .12);
            }

            .auth-heading {
                margin-bottom: 20px;
            }

            .auth-heading { padding-bottom: 14px; margin-bottom: 18px; }
            .auth-footer { margin-top: 16px; }
            .back-home { margin-top: 6px; }
        }

        @media (min-width: 601px) and (max-width: 900px) and (orientation: portrait) {
            body::before {
                top: min(38%, 420px);
                right: 0;
                bottom: auto;
                width: min(52vw, 420px);
                opacity: .3;
                transform: translateY(-50%);
            }

            body:has(.auth-stack:not(.compact))::before { top: 50%; }

            .auth-page { width: min(100% - 48px, 760px); }
            .auth-stack.compact { width: min(100%, 470px); }
            .auth-stack { margin-inline: auto; }
        }

        @media (max-width: 380px) {
            .auth-page {
                width: calc(100% - 16px);
                padding-top: 12px;
            }

            .auth-content {
                padding: 20px 14px;
            }

            .form-options {
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 600px) {
            .auth-stack.compact { max-width: 100%; }
            body::before {
                inset: 0;
                width: 100%;
                height: 100%;
                border-radius: 0;
                background-size: min(90vw, 380px) auto;
                background-position: right -45vw center;
                opacity: .2;
                transform: none;
            }
        }

        @media (max-height: 500px) and (orientation: landscape) {
            body::after {
                position: absolute;
                height: 100%;
            }

            body::before { top: 50%; right: -10vw; width: min(35vw, 190px); }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    @include('partials.public-header')

    <main class="auth-page" id="mainContent" tabindex="-1">
        <div class="auth-stack @yield('content-class', 'compact')">
            @unless(request()->routeIs('admin.device.*'))
                <nav class="auth-navigation" aria-label="Page navigation">
                    <a href="{{ route('home') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        Back to home
                    </a>
                </nav>
            @endunless
            <section class="auth-content @yield('content-class', 'compact')" aria-label="@yield('title', 'Account')">
                @yield('content')
            </section>
        </div>
    </main>

    <script>
        // Avoid opening the mobile keyboard or scrolling past the header on arrival.
        if (window.matchMedia('(min-width: 1025px) and (pointer: fine)').matches) {
            document.querySelector('[data-desktop-autofocus]')?.focus({ preventScroll: true });
        }
        document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
            button.addEventListener('click', function() {
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
