<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#174f75">
    <title>WVSU MIS Service Portal</title>
    @php
        $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
            ? asset('images/wvsu-janiuay-logo.png')
            : asset('images/wvsu-logo.png');
        $bagongPilipinasLogo = asset('images/Bagong_Pilipinas_logo.png');
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $dashboardUrl = auth()->check()
            ? ($isAdmin ? route('admin.dashboard') : route('dashboard'))
            : route('login');
        $submitUrl = auth()->check() && !$isAdmin
            ? route('requests.create')
            : ($isAdmin ? route('admin.requests.index') : route('login'));
        $trackUrl = auth()->check()
            ? ($isAdmin ? route('admin.requests.index') : route('requests.index'))
            : route('login');
        $messagesUrl = auth()->check()
            ? ($isAdmin ? route('admin.messenger') : route('user.messenger'))
            : route('login');
    @endphp
    <style>
        :root {
            --navy: #123f63;
            --blue: #1d648f;
            --blue-soft: #edf5f8;
            --page: #f4f7f8;
            --surface: #ffffff;
            --ink: #20313b;
            --muted: #64757f;
            --line: #d8e2e7;
            --gold: #ddb528;
        }

        * { box-sizing: border-box; }
        html { min-width: 320px; }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
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
                left 4vw center,
                right 4vw center;
            background-size:
                min(29vw, 370px) auto,
                min(25vw, 320px) auto;
            background-repeat: no-repeat;
            content: "";
            opacity: .065;
            pointer-events: none;
        }

        a { color: inherit; text-decoration: none; }
        a:focus-visible { outline: 3px solid rgba(221, 181, 40, .72); outline-offset: 3px; }

        .site-header {
            position: relative;
            z-index: 1;
            border-top: 4px solid var(--navy);
            border-bottom: 1px solid var(--line);
            background: var(--surface);
        }

        .header-inner {
            width: min(100% - 40px, 1140px);
            min-height: 78px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .brand { min-width: 0; display: flex; align-items: center; justify-content: center; gap: 13px; }
        .brand-marks { display: flex; align-items: center; gap: 6px; flex: 0 0 auto; }
        .brand-marks img { width: 53px; height: 53px; object-fit: contain; }
        .brand-marks .national { width: 47px; height: 47px; }
        .brand-copy { min-width: 0; }
        .brand-copy small { display: block; color: #627783; font-size: 9px; font-weight: 750; letter-spacing: .11em; text-transform: uppercase; }
        .brand-copy strong { display: block; margin-top: 2px; color: var(--navy); font-family: Georgia, "Times New Roman", serif; font-size: clamp(17px, 2vw, 21px); line-height: 1.15; }
        .brand-copy span { display: block; margin-top: 3px; color: var(--muted); font-size: 11px; font-weight: 650; }

        main {
            width: min(100% - 40px, 1060px);
            margin: 0 auto;
            padding: clamp(30px, 5vw, 48px) 0 38px;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .page-heading { max-width: 820px; margin: 0 auto 32px; text-align: center; }
        .eyebrow { margin: 0 0 10px; color: var(--blue); font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        h1 { margin: 0; color: var(--navy); font-family: Georgia, "Times New Roman", serif; font-size: clamp(32px, 4.5vw, 44px); line-height: 1.12; }
        .page-heading > p:not(.eyebrow) { max-width: 700px; margin: 14px auto 0; color: var(--muted); font-size: 15px; line-height: 1.6; }
        .welcome-actions { margin-top: 22px; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }

        .services-panel { padding: 24px 0 8px; border-top: 1px solid var(--line); }
        .panel-heading { margin-bottom: 18px; text-align: center; }
        .panel-heading h2 { margin: 0; color: var(--ink); font-size: 18px; }
        .panel-heading p { margin: 5px 0 0; color: var(--muted); font-size: 13px; }
        .service-list { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }

        .service-link {
            min-height: 92px;
            padding: 6px 20px;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            align-items: start;
            gap: 12px;
            border-left: 1px solid var(--line);
        }

        .service-link:first-child { border-left: 0; }
        .service-link:hover .service-copy strong { color: var(--blue); }
        .service-icon { width: 42px; height: 42px; display: grid; place-items: center; color: var(--blue); border-radius: 50%; background: var(--blue-soft); }
        .service-icon svg { width: 20px; height: 20px; fill: currentColor; }
        .service-copy { min-width: 0; }
        .service-copy strong { display: block; color: var(--ink); font-size: 14px; }
        .service-copy span { display: block; margin-top: 4px; color: var(--muted); font-size: 12px; line-height: 1.45; }

        .button {
            min-height: 44px;
            padding: 10px 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--blue);
            border-radius: 7px;
            background: var(--blue);
            color: #fff;
            font-size: 14px;
            font-weight: 750;
        }

        .button:hover { border-color: var(--navy); background: var(--navy); }
        .button.secondary { color: var(--blue); border-color: #b9ccd7; background: #fff; }
        .button.secondary:hover { border-color: var(--blue); background: var(--blue-soft); }

        .campus-bar {
            margin-top: 14px;
            padding: 16px 4px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border-top: 1px solid var(--line);
            color: #536872;
            font-size: 12px;
            text-align: center;
        }

        .campus-bar strong { color: var(--navy); }
        footer { width: min(100% - 40px, 1060px); margin: 0 auto; padding: 17px 0 22px; border-top: 1px solid var(--line); color: #687985; font-size: 12px; text-align: center; position: relative; z-index: 1; }

        @media (max-width: 820px) {
            body::before {
                background-position:
                    left -78px top 132px,
                    right -70px bottom 50px;
                background-size:
                    270px auto,
                    235px auto;
                opacity: .06;
            }
            .service-list { grid-template-columns: 1fr 1fr; }
            .service-link:last-child { grid-column: 1 / -1; padding-top: 20px; border-top: 1px solid var(--line); border-left: 0; }
        }

        @media (max-width: 600px) {
            body::before {
                background-position:
                    left -92px top 115px,
                    right -82px bottom 28px;
                background-size:
                    230px auto,
                    200px auto;
                opacity: .05;
            }
            .header-inner { width: calc(100% - 24px); min-height: 68px; gap: 8px; }
            .brand { gap: 8px; }
            .brand-marks { gap: 3px; }
            .brand-marks img { width: 39px; height: 39px; }
            .brand-marks .national { width: 34px; height: 34px; }
            .brand-copy small, .brand-copy span { display: none; }
            .brand-copy strong { max-width: 175px; font-size: 14px; }
            main { width: calc(100% - 24px); padding: 27px 0 34px; }
            .page-heading { margin-bottom: 30px; }
            .eyebrow { font-size: 12px; }
            h1 { font-size: clamp(30px, 10vw, 38px); }
            .page-heading > p:not(.eyebrow) { margin-top: 12px; font-size: 14px; }
            .welcome-actions .button { flex: 1 1 140px; max-width: 210px; }
            .services-panel { padding: 24px 0 8px; }
            .service-list { grid-template-columns: 1fr; }
            .service-link,
            .service-link:first-child,
            .service-link:last-child {
                grid-column: auto;
                min-height: 0;
                padding: 16px 2px;
                border-top: 1px solid var(--line);
                border-left: 0;
            }
            .service-link:first-child { padding-top: 4px; border-top: 0; }
            .campus-bar { flex-direction: column; gap: 3px; }
            footer { width: calc(100% - 24px); }
        }

        @media (max-width: 360px) {
            body::before { opacity: .045; }
            .header-inner { width: calc(100% - 16px); }
            .brand-marks img { width: 33px; height: 33px; }
            .brand-marks .national { width: 29px; height: 29px; }
            .brand-copy strong { max-width: 122px; font-size: 12px; }
            main { width: calc(100% - 16px); }
            .services-panel { padding-inline: 4px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; }
        }
    </style>
</head>
<body>
    @include('partials.public-header')

    <main id="mainContent">
        <div class="page-heading">
            <p class="eyebrow">Official campus service portal</p>
            <h1>MIS Service Request System</h1>
            <p>Submit ICT concerns, monitor request progress, and communicate with the MIS Office using your university account.</p>

            <div class="welcome-actions">
                @auth
                    <a href="{{ $dashboardUrl }}" class="button">Open dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="button">Sign in</a>
                    <a href="{{ route('register') }}" class="button secondary">Create account</a>
                @endauth
            </div>
        </div>

        <section class="services-panel" aria-labelledby="servicesTitle">
            <div class="panel-heading">
                <h2 id="servicesTitle">Campus services</h2>
                <p>Choose what you need to do.</p>
            </div>

            <nav class="service-list" aria-label="MIS service actions">
                <a href="{{ $submitUrl }}" class="service-link">
                    <span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm6 4v4H7v2h4v4h2v-4h4v-2h-4V7h-2Z"/></svg></span>
                    <span class="service-copy"><strong>Submit Request</strong><span>Report an ICT concern to the MIS Office.</span></span>
                </a>
                <a href="{{ $trackUrl }}" class="service-link">
                    <span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 2h10l4 4v16H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm9 1.5V7h3.5L15 3.5ZM8 11v2h8v-2H8Zm0 4v2h6v-2H8Z"/></svg></span>
                    <span class="service-copy"><strong>Track Request</strong><span>Review its status and MIS updates.</span></span>
                </a>
                <a href="{{ $messagesUrl }}" class="service-link">
                    <span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 3h16a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H9l-5 4v-4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm2 5v2h12V8H6Zm0 4v2h8v-2H6Z"/></svg></span>
                    <span class="service-copy"><strong>Messages</strong><span>Communicate about an existing request.</span></span>
                </a>
            </nav>
        </section>

        <div class="campus-bar">
            <strong>For walk-in assistance,</strong>
            <span>visit the MIS Office during official campus office hours.</span>
        </div>
    </main>

    <footer>West Visayas State University · Janiuay Campus · Management Information Systems Office</footer>
</body>
</html>
