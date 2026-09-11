@php
    $publicWvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
        ? asset('images/wvsu-janiuay-logo.png')
        : asset('images/wvsu-logo.png');
    $publicBagongPilipinasLogo = asset('images/Bagong_Pilipinas_logo.png');
@endphp

<style>
    .public-skip-link {
        position: fixed;
        z-index: 9999;
        top: 8px;
        left: 8px;
        padding: 9px 13px;
        color: #123f63;
        border-radius: 6px;
        background: #fff;
        font: 700 13px/1.3 "Segoe UI", Arial, sans-serif;
        transform: translateY(-150%);
    }

    .public-skip-link:focus { transform: translateY(0); }

    .public-header {
        position: relative;
        z-index: 5;
        color: #183446;
        border-top: 4px solid #123f63;
        border-bottom: 1px solid #d8e2e7;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 1px 3px rgba(25, 55, 74, .05);
    }

    .public-header .public-header-inner {
        width: min(100% - 40px, 1140px);
        min-height: 78px;
        margin: 0 auto;
        padding: 9px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .public-header .public-brand {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 13px;
        color: inherit;
        text-decoration: none;
    }

    .public-header .public-identity {
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .public-header .public-wvsu-mark {
        width: 54px;
        height: 54px;
        flex: 0 0 auto;
        object-fit: contain;
    }

    .public-header .public-brand-copy { min-width: 0; }
    .public-header .public-brand-copy small {
        display: block;
        color: #627783;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .public-header .public-brand-copy strong {
        display: block;
        margin-top: 2px;
        color: #123f63;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(17px, 2vw, 21px);
        line-height: 1.15;
    }

    .public-header .public-brand-copy span {
        display: block;
        margin-top: 3px;
        color: #64757f;
        font-size: 11px;
        font-weight: 650;
    }

    .public-header .public-national-mark {
        width: 55px;
        height: 55px;
        flex: 0 0 auto;
        object-fit: contain;
    }

    .public-header .public-header-actions {
        position: absolute;
        right: 0;
        flex: 0 0 auto;
    }

    .public-header .public-header-actions form { margin: 0; }
    .public-header .public-logout {
        min-height: 38px;
        padding: 8px 12px;
        color: #174f75;
        border: 1px solid #bfd0da;
        border-radius: 6px;
        background: #fff;
        font: 700 12px/1.2 "Segoe UI", Arial, sans-serif;
        cursor: pointer;
    }
    .public-header .public-logout:hover { color: #fff; border-color: #174f75; background: #174f75; }

    @media (max-width: 600px) {
        .public-header .public-header-inner {
            width: calc(100% - 24px);
            min-height: 68px;
            padding: 7px 0;
        }
        .public-header .public-brand { gap: 8px; }
        .public-header .public-identity { gap: 7px; }
        .public-header .public-wvsu-mark { width: 42px; height: 42px; }
        .public-header .public-national-mark { width: 40px; height: 40px; }
        .public-header .public-logout { min-height: 34px; padding: 6px 8px; font-size: 10px; }
        .public-header .public-brand-copy small,
        .public-header .public-brand-copy span { display: none; }
        .public-header .public-brand-copy strong { max-width: 150px; overflow: hidden; font-size: 14px; text-overflow: ellipsis; white-space: nowrap; }
        .public-header.has-session-action .public-header-inner { min-height: 108px; padding-bottom: 45px; }
        .public-header.has-session-action .public-header-actions { right: 50%; bottom: 7px; transform: translateX(50%); }
    }

    @media (max-width: 360px) {
        .public-header .public-header-inner { width: calc(100% - 16px); min-height: 60px; }
        .public-header .public-wvsu-mark { width: 36px; height: 36px; }
        .public-header .public-national-mark { width: 34px; height: 34px; }
        .public-header .public-brand-copy strong { max-width: 135px; font-size: 12px; }
    }
</style>

<a href="#mainContent" class="public-skip-link">Skip to main content</a>
<header class="site-header public-header @auth has-session-action @endauth" role="banner">
    <div class="header-inner public-header-inner">
        <div class="public-identity">
            <a class="public-brand" href="{{ route('home') }}" aria-label="WVSU MIS Service Portal home">
                <img class="public-wvsu-mark" src="{{ $publicWvsuLogo }}" alt="West Visayas State University seal">
                <span class="public-brand-copy">
                    <small>Republic of the Philippines</small>
                    <strong>West Visayas State University</strong>
                    <span>Janiuay Campus &middot; Management Information Systems Office</span>
                </span>
            </a>
            <img class="public-national-mark" src="{{ $publicBagongPilipinasLogo }}" alt="Bagong Pilipinas logo">
        </div>
        @auth
        <div class="public-header-actions">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="public-logout" type="submit">Log out</button>
            </form>
        </div>
        @endauth
    </div>
</header>
