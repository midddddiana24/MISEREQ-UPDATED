@extends('layouts.auth')

@section('title', 'Set Up Two-Factor Authentication')
@section('content-class', '')

@section('content')
<style>
    .setup-intro { margin-bottom: 22px; text-align: left; }
    .setup-intro h1 { margin: 0 0 7px; color: var(--brand-dark); font: 750 clamp(28px, 3.2vw, 34px)/1.2 "Segoe UI", Arial, sans-serif; letter-spacing: -.03em; }
    .setup-intro p { max-width: 590px; margin: 0; color: var(--muted); font-size: 16px; line-height: 1.55; }
    .setup-progress { margin: 0 0 22px; padding: 0; display: grid; grid-template-columns: repeat(3, 1fr); list-style: none; }
    .setup-progress li { position: relative; color: #415b6d; font-size: 14px; font-weight: 700; text-align: center; }
    .setup-progress li::before { content: attr(data-step); position: relative; z-index: 1; width: 28px; height: 28px; margin: 0 auto 7px; display: grid; place-items: center; color: #fff; border-radius: 50%; background: var(--brand); }
    .setup-progress li:not(:last-child)::after { content: ""; position: absolute; top: 13px; left: calc(50% + 18px); width: calc(100% - 36px); border-top: 1px solid #cbd8e0; }
    .setup-grid { display: grid; grid-template-columns: 230px minmax(0, 1fr); align-items: start; gap: 24px; }
    .setup-grid > * { min-width: 0; }
    .qr-panel { padding: 14px; border: 1px solid var(--line); border-radius: 8px; background: #f8fbfd; text-align: center; }
    .qr-panel img { width: 200px; height: 200px; max-width: 100%; display: block; margin: auto; border: 6px solid #fff; }
    .qr-panel strong { display: block; margin-top: 10px; color: var(--ink); font-size: 13px; }
    .setup-copy { margin: 0 0 16px; color: var(--muted); font-size: 13px; line-height: 1.55; }
    .manual-key { margin-bottom: 17px; }
    .manual-key span { display: block; margin-bottom: 6px; color: #52636f; font-size: 12px; font-weight: 700; }
    .manual-key code { padding: 10px 11px; display: block; color: var(--brand-dark); border: 1px solid #cbdbe5; border-radius: 6px; background: var(--brand-soft); font: 700 13px/1.45 Consolas, monospace; overflow-wrap: anywhere; text-align: center; }
    .setup-note { margin-top: 20px; padding: 11px 13px; color: #6f531b; border: 1px solid #ead6a5; border-radius: 6px; background: #fff9e9; font-size: 12px; line-height: 1.5; }
    .field-error { display: block; margin-top: 5px; color: var(--danger); font-size: 12px; }
    .setup-grid .form-group > input { width: 100%; min-height: 44px; padding: 10px 12px; color: var(--ink); border: 1px solid #cbd6de; border-radius: 6px; outline: none; }
    .setup-grid .form-group > input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(31, 111, 168, .11); }
    .setup-copy { font-size: 16px; }
    .setup-note { font-size: 14px; }
    .qr-panel img { height: auto; aspect-ratio: 1; }
    @media (max-width: 820px) {
        .setup-grid { grid-template-columns: 1fr; gap: 18px; }
        .qr-panel { width: 230px; max-width: 100%; margin: auto; }
        .setup-progress li { font-size: 12px; padding: 0 3px; }
    }
</style>

<div class="setup-intro">
    <h1>Protect Your Account</h1>
    <p>Connect an authenticator app to add a secure verification step when you sign in.</p>
</div>

<ol class="setup-progress" aria-label="Setup steps">
    <li data-step="1">Scan</li>
    <li data-step="2">Enter code</li>
    <li data-step="3">Save recovery codes</li>
</ol>

<div class="setup-grid">
    <div class="qr-panel">
        <img src="{{ $qrCodeInline }}" width="200" height="200" alt="QR code for your authenticator app">
        <strong>Scan with your authenticator app</strong>
    </div>

    <div>
        <p class="setup-copy">Open Google Authenticator, Microsoft Authenticator, Authy, or another compatible app. Scan the QR code, then enter the six-digit code it generates.</p>

        <div class="manual-key">
            <span>Cannot scan? Enter this setup key manually:</span>
            <code>{{ $secret }}</code>
        </div>

        <form method="POST" action="{{ route('2fa.setup.store') }}">
            @csrf
            <div class="form-group">
                <label for="current_password">Current password</label>
                <div class="input-wrap">
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    <button type="button" class="password-toggle" data-password-toggle="current_password" aria-label="Show password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="2.7"/></svg>
                    </button>
                </div>
                @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="verification_code">Six-digit authenticator code</label>
                <input type="text" id="verification_code" name="verification_code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" placeholder="000000" required style="text-align:center; letter-spacing:.24em; font-size:18px; font-weight:700;">
                @error('verification_code')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="primary-button">Verify and Enable 2FA</button>
        </form>
    </div>
</div>

<div class="setup-note"><strong>Before you finish:</strong> recovery codes will be shown after setup. Save them somewhere private so you can sign in if your phone is unavailable.</div>
@endsection

@push('scripts')
<script>
    const setupCode = document.getElementById('verification_code');
    setupCode?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });
</script>
@endpush
