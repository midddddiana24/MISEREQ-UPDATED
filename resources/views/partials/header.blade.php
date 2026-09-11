@php
$user = auth()->user();
$wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
    ? asset('images/wvsu-janiuay-logo.png')
    : asset('images/wvsu-logo.png');
$bagongPilipinasLogo = asset('images/Bagong_Pilipinas_logo.png');
@endphp

<style>
    .site-header {
        position: relative;
        color: #183446;
        border-top: 4px solid #174f75;
        border-bottom: 1px solid #dce4e9;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 1px 3px rgba(25, 55, 74, .05);
    }

    .skip-link {
        position: fixed;
        z-index: 9999;
        top: 8px;
        left: 8px;
        padding: 10px 14px;
        color: #123f63;
        border-radius: 6px;
        background: #fff;
        font-weight: 700;
        transform: translateY(-150%);
    }

    .skip-link:focus { transform: translateY(0); }

    .site-header .header-inner {
        max-width: 1240px;
        min-height: 76px;
        margin: 0 auto;
        padding: 9px 20px;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .site-header .brand { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1 1 auto; }
    .site-header .brand-marks {
        display: flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
    }
    .site-header .brand-mark {
        width: 54px;
        height: 54px;
        object-fit: contain;
        padding: 2px;
        background: #fff;
    }
    .site-header .brand-mark.wvsu { border-radius: 50%; }
    .site-header .brand-mark.bagong { width: 49px; height: 49px; padding: 0; }

    .site-header .brand-copy { min-width: 0; line-height: 1.15; }
    .site-header .brand small { display: block; margin-bottom: 3px; color: #55707f; font-size: 9px; font-weight: 750; letter-spacing: .1em; text-transform: uppercase; }
    .site-header .brand h1 { margin: 0; color: #183446; font-family: Georgia, "Times New Roman", serif; font-size: clamp(17px, 2vw, 21px); line-height: 1.12; }
    .site-header .brand p { margin: 3px 0 0; color: #4e6674; font-size: 10px; font-weight: 650; letter-spacing: .02em; }
    .site-header .top-actions { width: auto; flex: 0 0 auto; display: flex; align-items: center; justify-content: flex-end; gap: 13px; margin-left: auto; }
    .site-header .top-actions form { width: auto; margin: 0; display: inline-flex; }
    .site-header .logout-button {
        min-height: 40px;
        padding: 8px 13px;
        color: #174f75;
        border: 1px solid #bfd0da;
        border-radius: 6px;
        background: #fff;
        font: 700 12px/1.2 "Segoe UI", sans-serif;
        cursor: pointer;
    }
    .site-header .logout-button:hover { color: #fff; border-color: #174f75; background: #174f75; }

    @media (max-width: 860px) {
        .site-header .header-inner {
            min-height: 68px;
            padding: 8px 14px;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
        }
        .site-header .brand { width: auto !important; flex: 1 1 auto; align-items: center !important; }
        .site-header .top-actions { width: auto !important; flex: 0 0 auto; justify-content: flex-end !important; margin-left: auto; }
        .site-header .top-actions form,
        .site-header .top-actions .button,
        .site-header .top-actions button { width: auto !important; }
    }

    @media (max-width: 640px) {
        .site-header .brand { min-width: 0 !important; flex: 1 1 0 !important; overflow: hidden; }
        .site-header .brand-copy { min-width: 0; overflow: hidden; }
        .site-header .brand-marks { gap: 4px; }
        .site-header .brand-mark { width: 42px; height: 42px; }
        .site-header .brand-mark.bagong { width: 38px; height: 38px; }
        .site-header .top-actions { flex: 0 0 auto !important; }
        .site-header .brand h1 { max-width: 190px; font-size: 15px; }
        .site-header .brand small { font-size: 9px; }
        .site-header .brand p { font-size: 10px; }
        .site-header .logout-button { min-width: 42px; padding: 8px 10px; }
        .site-header .top-actions form,
        .site-header .top-actions button { width: auto !important; }
    }

    @media (max-width: 420px) {
        .site-header .brand small { display: none; }
        .site-header .brand { gap: 7px; }
        .site-header .brand-mark { width: 36px; height: 36px; }
        .site-header .brand-mark.bagong { width: 32px; height: 32px; }
        .site-header .brand h1 { max-width: 145px; font-size: 12px; line-height: 1.12; }
        .site-header .brand p { display: none; }
        .site-header .logout-button { min-height: 34px; padding: 6px 7px; font-size: 9px; }
    }

    @media (max-width: 380px) {
        .site-header .header-inner { min-height: 58px; padding: 6px 8px; gap: 6px !important; }
        .site-header .brand { gap: 7px !important; }
        .site-header .brand-mark { width: 34px; height: 34px; }
        .site-header .brand-mark.bagong { width: 30px; height: 30px; }
        .site-header .brand h1 { max-width: 122px; font-size: 11px; line-height: 1.08; }
        .site-header .top-actions { gap: 6px; }
        .site-header .logout-button { min-width: 0; min-height: 34px; padding: 6px 7px; font-size: 10px; }
    }

    @media (max-width: 330px) {
        .site-header .brand p { display: none; }
        .site-header .brand h1 { font-size: 11px; }
        .site-header .logout-button { padding-inline: 7px; }
    }
</style>

<a href="#clientNavMenu" class="skip-link">Skip to navigation</a>
<header class="site-header" role="banner">
    <div class="header-inner">
        <div class="brand">
            <div class="brand-marks" aria-label="Institutional and national identity">
                <img class="brand-mark wvsu" src="{{ $wvsuLogo }}" alt="West Visayas State University seal">
                <img class="brand-mark bagong" src="{{ $bagongPilipinasLogo }}" alt="Bagong Pilipinas logo">
            </div>
            <div class="brand-copy">
                <small>REPUBLIC OF THE PHILIPPINES</small>
                <h1>West Visayas State University</h1>
                <p>Management Information System &middot; Service Portal</p>
            </div>
        </div>
        <div class="top-actions">
            @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">Log out</button>
            </form>
            @endauth
        </div>
    </div>
</header>
