<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-visual">
        <meta name="theme-color" content="#103d60">
    <title>MISEREQ | WVSU Janiuay Campus</title>
    @php
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $dashboardUrl = auth()->check()
            ? ($isAdmin ? route('admin.dashboard') : route('dashboard'))
            : route('login');
    @endphp
    <style>
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 */
        :root {
            color-scheme: light;
            --color-ink: #19354a;
            --color-muted: #415b6d;
            --color-navy: #103d60;
            --color-blue: #0b5e92;
            --color-blue-bright: #167cb1;
            --color-paper: #f3f7f9;
            --color-white: #fff;
            --color-gold: #f4cc59;
            --color-gold-hover: #ffe084;
            --color-line: #c6d7e2;
            --color-on-blue-muted: #e6f1f7;
            --color-ring-line: rgba(255, 255, 255, .17);
            --color-ring-inner: rgba(255, 255, 255, .045);
            --color-ring-outer: rgba(255, 255, 255, .035);
            --color-seal-border: rgba(255, 255, 255, .9);
            --color-seal-shadow: rgba(0, 28, 50, .28);
            --color-mobile-text-shadow: rgba(0, 28, 50, .6);
            --font-ui: "Segoe UI", Arial, sans-serif;
        }

        * { box-sizing: border-box; }

        html {
            min-width: 320px;
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            min-height: 100svh;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            color: var(--color-ink);
            background: var(--color-paper);
            font-family: var(--font-ui);
            -webkit-font-smoothing: antialiased;
        }

        main {
            width: 100%;
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .welcome-layout {
            position: relative;
            isolation: isolate;
            flex: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(220px, .75fr);
            align-items: center;
            gap: clamp(28px, 5vw, 80px);
            overflow: hidden;
            padding: 68px max(32px, calc((100% - 1080px) / 2));
            background: linear-gradient(112deg, var(--color-navy) 0%, var(--color-blue) 65%, var(--color-blue-bright) 100%);
        }

        .welcome-layout::before {
            content: "";
            position: absolute;
            z-index: -1;
            width: min(60vw, 700px);
            aspect-ratio: 1;
            top: -55%;
            right: -11%;
            border: 1px solid var(--color-ring-line);
            border-radius: 50%;
            box-shadow: 0 0 0 88px var(--color-ring-inner), 0 0 0 180px var(--color-ring-outer);
            pointer-events: none;
        }

        .welcome-copy { min-width: 0; }

        .eyebrow {
            margin: 0 0 15px;
            color: var(--color-gold);
            font-size: 14px;
            font-weight: 750;
            letter-spacing: .045em;
        }

        h1 {
            max-width: 680px;
            margin: 0;
            color: var(--color-white);
            font-size: clamp(36px, 4.3vw, 57px);
            font-weight: 750;
            letter-spacing: -.045em;
            line-height: 1.08;
        }

        h1 span {
            display: block;
            margin-top: 10px;
            color: var(--color-white);
            font-size: clamp(25px, 2.45vw, 33px);
            font-weight: 650;
            letter-spacing: -.025em;
            line-height: 1.2;
        }

        .intro {
            max-width: 550px;
            margin: 23px 0 0;
            color: var(--color-on-blue-muted);
            font-size: 17px;
            line-height: 1.65;
        }

        .welcome-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-top: 30px;
        }

        .welcome-mobile-logout { display: none; }

        .button {
            min-height: 48px;
            min-width: 142px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 24px;
            border: 2px solid var(--color-gold);
            border-radius: 8px;
            color: var(--color-navy);
            background: var(--color-gold);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
            text-align: center;
            text-decoration: none;
            transition: background-color .15s ease, border-color .15s ease;
        }

        .button:hover { background: var(--color-gold-hover); border-color: var(--color-gold-hover); }

        .button.secondary {
            color: var(--color-white);
            border-color: var(--color-white);
            background: transparent;
        }

        .button.secondary:hover { color: var(--color-navy); background: var(--color-white); }

        .button:focus-visible {
            outline: 3px solid var(--color-white);
            outline-offset: 3px;
        }

        .button:active { transform: translateY(1px); }

        .seal-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
        }

        .welcome-seal {
            display: block;
            width: min(100%, 340px);
            height: auto;
            aspect-ratio: 1021 / 1024;
            object-fit: cover;
            border-radius: 50%;
            border: 10px solid var(--color-seal-border);
            box-shadow: 0 24px 60px var(--color-seal-shadow);
        }

        .quick-steps { display: none; }

        .walk-in {
            width: min(100% - 64px, 1080px);
            margin: 0 auto;
            padding: 21px 0;
            color: var(--color-muted);
            font-size: 15px;
            line-height: 1.5;
            text-align: center;
        }

        .walk-in::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 7px;
            border-radius: 50%;
            background: var(--color-blue);
            vertical-align: middle;
        }

        .walk-in strong { color: var(--color-navy); }

        footer {
            width: min(100% - 64px, 1080px);
            margin: 0 auto;
            padding: 12px 0 19px;
            border-top: 1px solid var(--color-line);
            color: var(--color-muted);
            font-size: 13px;
            line-height: 1.45;
            text-align: center;
        }

        @media (max-width: 900px) {
            .welcome-layout { grid-template-columns: minmax(0, 1.15fr) minmax(245px, .85fr); gap: 24px; padding: 60px 32px; }
            .eyebrow { font-size: 16px; }
            h1 { font-size: clamp(46px, 6vw, 54px); }
            h1 span { font-size: clamp(30px, 3.9vw, 35px); }
            .intro { font-size: 20px; line-height: 1.55; }
            .welcome-actions { margin-top: 34px; }
            .button { min-height: 54px; font-size: 18px; }
            .welcome-seal { width: min(100%, 310px); }
            .walk-in, footer { width: min(100% - 48px, 760px); }
        }

        @media (max-width: 860px) {
            .welcome-mobile-logout { display: block; }
            .welcome-mobile-logout button { cursor: pointer; font-family: inherit; }
        }

        @media (max-width: 640px) {
            .welcome-layout { display: flex; flex-direction: column; justify-content: center; align-items: stretch; padding: 45px 18px; }
            .welcome-layout::before { width: 400px; top: -240px; right: -220px; }
            .eyebrow { margin-bottom: 12px; font-size: 13px; }
            h1 { font-size: clamp(34px, 9vw, 42px); }
            h1 span { margin-top: 8px; font-size: clamp(23px, 6vw, 28px); }
            .intro { margin-top: 18px; font-size: 16px; line-height: 1.55; }
            .welcome-actions { margin-top: 24px; }
            .button { min-height: 48px; font-size: 16px; }
            .seal-wrap {
                position: absolute;
                z-index: -1;
                right: -45vw;
                top: 44%;
                width: min(90vw, 380px);
                transform: translateY(-50%);
                opacity: .20;
                pointer-events: none;
            }
            .welcome-seal { width: 100%; border-width: 0; box-shadow: none; }
            .eyebrow, h1, .intro { text-shadow: 0 1px 2px var(--color-mobile-text-shadow); }
            .walk-in { font-size: 14px; }
            .walk-in, footer { width: min(100% - 36px, 480px); }
        }

        @media (min-width: 901px) and (max-width: 1100px) {
            h1 { font-size: 50px; }
            h1 span { font-size: 32px; }
            .intro { font-size: 19px; }
            .welcome-seal { width: min(100%, 320px); }
        }

        @media (min-width: 641px) and (max-width: 900px) and (orientation: portrait) {
            .welcome-layout { grid-template-rows: 1fr auto; }
            .quick-steps {
                display: grid;
                grid-column: 1 / -1;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                width: 100%;
                margin: 0;
                padding: 20px 0 0;
                border-top: 1px solid var(--color-ring-line);
                list-style: none;
            }
            .quick-steps li { display: flex; align-items: baseline; gap: 9px; color: var(--color-white); font-size: 16px; font-weight: 600; }
            .quick-steps span { color: var(--color-gold); font-size: 14px; font-weight: 800; }
        }

        @media (max-width: 360px) {
            .welcome-actions { display: grid; }
            .button { width: 100%; }
        }

        @media (max-height: 500px) and (orientation: landscape) {
            .welcome-layout { padding-top: 28px; padding-bottom: 28px; }
            .welcome-seal { width: min(100%, 190px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .button { transition: none; }
        }
    </style>
</head>

<body>
    @include('partials.public-header')

    <main id="mainContent" tabindex="-1">
        <div class="welcome-layout">
            <div class="welcome-copy">
                <p class="eyebrow">WVSU Janiuay Campus · MIS Office</p>
                <h1>MISEREQ <span>MIS Service Request Portal</span></h1>
                <p class="intro">Submit ICT concerns, track request updates, and message the MIS Office in one place.</p>

                <div class="welcome-actions">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="button">Open dashboard</a>
                        <form class="welcome-mobile-logout" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="button secondary">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="button">Sign in</a>
                        <a href="{{ route('register') }}" class="button secondary">Create account</a>
                    @endauth
                </div>
            </div>

            <div class="seal-wrap">
                <img class="welcome-seal" src="{{ asset('images/janiuay2-1021x1024.jpg') }}" alt="" width="340" height="341">
            </div>

            <ol class="quick-steps">
                <li><span>01</span>Submit a request</li>
                <li><span>02</span>Track its status</li>
                <li><span>03</span>Message the MIS Office</li>
            </ol>
        </div>

        <p class="walk-in"><strong>Need walk-in assistance?</strong> Visit the MIS Office during official campus office hours.</p>
    </main>

    <footer>West Visayas State University · Janiuay Campus</footer>
</body>

</html>
