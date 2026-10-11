@php
$publicWvsuLogo = asset('images/wvsu-logo.png');
$publicBagongPilipinasLogo = file_exists(public_path('images/bagong-pilipinas-logo-98.png'))
? asset('images/bagong-pilipinas-logo-98.png')
: asset('images/Bagong_Pilipinas_logo.png');
$publicBagongPilipinasLogoWebp = file_exists(public_path('images/bagong-pilipinas-logo-98.webp'))
? asset('images/bagong-pilipinas-logo-98.webp')
: null;
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

    .public-skip-link:focus {
        transform: translateY(0);
    }

    .public-skip-navigation { left: 180px; }

    .public-header {
        position: relative;
        z-index: 5;
        color: #183446;
        border-top: 4px solid #123f63;
        border-bottom: 1px solid #d8e2e7;
        background: #fff;
        box-shadow: 0 1px 3px rgba(25, 55, 74, .05);
    }

    .public-header .public-header-inner {
        width: min(100% - 40px, 1140px);
        min-height: 78px;
        margin: 0 auto;
        padding: 6px 0;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .public-header .public-brand {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: inherit;
        text-decoration: none;
    }

    .public-header .public-identity {
        min-width: 0;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .public-header .public-wvsu-mark {
        width: 56px;
        height: 56px;
        flex: 0 0 auto;
        object-fit: contain;
        border-radius: 50%;
    }

    .public-header .public-brand-copy {
        min-width: 0;
        text-align: center;
    }

    .public-header .public-brand-copy small {
        display: block;
        color: #415b6d;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0;
        line-height: 1.15;
    }

    .public-header .public-university-name {
        display: block;
        margin-top: 1px;
        margin-bottom: 0;
        color: #123f63;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(17px, 2vw, 23px);
        letter-spacing: -.02em;
        line-height: 1.08;
        text-transform: uppercase;
    }

    .public-header .public-brand-copy span {
        display: block;
        margin-top: 1px;
        color: #415b6d;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.15;
    }

    .public-header .public-brand-copy .public-system-name {
        color: #123f63;
        font-size: 15px;
        font-weight: 700;
    }

    .public-header .public-national-mark {
        width: 50px;
        height: 50px;
        flex: 0 0 auto;
        object-fit: contain;
    }

    .public-header .public-header-actions {
        position: absolute;
        right: 0;
        flex: 0 0 auto;
    }

    .public-header .public-header-actions form {
        margin: 0;
    }

    .public-header .public-logout {
        min-height: 44px;
        padding: 8px 12px;
        color: #174f75;
        border: 1px solid #bfd0da;
        border-radius: 6px;
        background: #fff;
        font: 700 13px/1.2 "Segoe UI", Arial, sans-serif;
        cursor: pointer;
    }

    .public-header .public-logout:hover {
        color: #fff;
        border-color: #174f75;
        background: #174f75;
    }

    @media (min-width: 601px) {
        .public-header.has-session-action .public-header-inner { gap: 16px; }
        .public-header.has-session-action .public-identity { flex: 1; width: auto; }
        .public-header.has-session-action .public-header-actions { position: static; }
    }

    @media (max-width: 860px) {
        .public-header.has-client-navigation .public-header-actions,
        .public-header.has-home-session .public-header-actions { display: none; }
    }

    @media (max-width: 600px) {
        .public-header .public-header-inner {
            width: calc(100% - 24px);
            min-height: 76px;
            padding: 6px 0;
        }

        .public-header .public-brand {
            gap: 8px;
        }

        .public-header .public-identity {
            gap: 7px;
        }

        .public-header .public-wvsu-mark {
            width: 40px;
            height: 40px;
        }

        .public-header .public-national-mark {
            width: 36px;
            height: 36px;
        }

        .public-header .public-logout {
            min-height: 44px;
            padding: 6px 8px;
            font-size: 12px;
        }

        .public-header .public-brand-copy small {
            font-size: 10px;
            line-height: 1.15;
        }

        .public-header .public-brand-copy span {
            font-size: 11px;
            line-height: 1.2;
        }

        .public-header .public-brand-copy .public-system-name { font-size: 13px; }

        .public-header .public-university-name {
            max-width: none;
            overflow-wrap: anywhere;
            font-size: 14px;
            line-height: 1.08;
            white-space: normal;
        }

        .public-header.has-session-action:not(.has-client-navigation):not(.has-home-session) .public-header-inner {
            min-height: 110px;
            padding-bottom: 56px;
        }

        .public-header.has-session-action:not(.has-client-navigation):not(.has-home-session) .public-header-actions {
            right: 50%;
            bottom: 7px;
            transform: translateX(50%);
        }
    }

    @media (max-width: 360px) {
        .public-header .public-header-inner {
            width: calc(100% - 16px);
            min-height: 60px;
        }

        .public-header .public-wvsu-mark {
            width: 36px;
            height: 36px;
        }

        .public-header .public-national-mark {
            width: 32px;
            height: 32px;
        }

        .public-header .public-university-name {
            max-width: none;
            font-size: 12px;
        }
    }
</style>

<a href="#mainContent" class="public-skip-link">Skip to main content</a>
<header class="site-header public-header @auth has-session-action @if(request()->routeIs('home')) has-home-session @endif @endauth @if($showClientSkipNavigation ?? false) has-client-navigation @endif" role="banner">
    @if($showClientSkipNavigation ?? false)
    <a href="#clientNavMenu" class="public-skip-link public-skip-navigation">Skip to navigation</a>
    @endif
    <div class="header-inner public-header-inner">
        <div class="public-identity">
            <a class="public-brand" href="{{ route('home') }}" aria-label="WVSU MIS Service Portal home">
                <img class="public-wvsu-mark" src="{{ $publicWvsuLogo }}" width="108" height="108" alt="West Visayas State University seal">
                <span class="public-brand-copy">
                    <small>Republic of the Philippines</small>
                    @if(auth()->check() && !request()->routeIs('home'))
                    <h1 class="public-university-name">West Visayas State University</h1>
                    @else
                    <strong class="public-university-name">West Visayas State University</strong>
                    @endif
                    <span>Janiuay Campus - Locsin Street, Janiuay, Iloilo</span>
                    <span class="public-system-name">MISEREQ System</span>
                </span>
            </a>
            <picture>
                @if($publicBagongPilipinasLogoWebp)<source srcset="{{ $publicBagongPilipinasLogoWebp }}" type="image/webp">@endif
                <img class="public-national-mark" src="{{ $publicBagongPilipinasLogo }}" width="98" height="92" alt="Bagong Pilipinas logo">
            </picture>
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
