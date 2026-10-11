@extends('layouts.auth')

@section('title', 'Verify Your Sign-In')

@section('content')
<style>
    .verify-mark { width: 48px; height: 48px; margin: 0 auto 14px; display: grid; place-items: center; color: var(--brand-dark); border: 1px solid #c9dce8; border-radius: 50%; background: var(--brand-soft); }
    .verify-mark svg { width: 23px; height: 23px; }
    .code-field { text-align: center; letter-spacing: .28em; font-size: 20px; font-weight: 700; }
    .form-group > input { width: 100%; min-height: 48px; padding: 10px 12px; color: var(--ink); border: 1px solid #cbd6de; border-radius: 6px; outline: none; }
    .form-group > input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(31, 111, 168, .11); }
    .field-error { display: block; margin-top: 6px; color: var(--danger); font-size: 12px; }
    .verify-switch { margin-top: 17px; padding-top: 16px; border-top: 1px solid #e7edf1; color: var(--muted); font-size: 13px; text-align: center; }
    .link-button { min-height: 44px; padding: 8px; color: var(--brand); border: 0; background: transparent; cursor: pointer; font: inherit; font-weight: 700; text-decoration: underline; }
    #recovery_code { font-size: 16px; letter-spacing: .06em; }
    .recovery-panel[hidden], .authenticator-panel[hidden] { display: none; }
    .recovery-note { margin-bottom: 16px; padding: 10px 12px; color: #66501d; border: 1px solid #e6d5a8; border-radius: 6px; background: #fff9e9; font-size: 12px; line-height: 1.5; }
</style>

<div class="auth-heading">
    <div class="verify-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l7 3v5c0 4.6-2.9 8.1-7 10-4.1-1.9-7-5.4-7-10V6l7-3z"/><path d="M9.3 12l1.8 1.8 3.8-4"/></svg>
    </div>
    <h1>Verify Your Sign-In</h1>
    <p>Use the current code from your authenticator app to continue securely.</p>
</div>

<div id="authenticatorPanel" class="authenticator-panel" @if(old('is_recovery')) hidden @endif>
    <form method="POST" action="{{ route('2fa.verify.store') }}" autocomplete="off">
        @csrf
        <div class="form-group">
            <label for="verification_code">Six-digit authenticator code</label>
            <input class="code-field" type="text" id="verification_code" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" placeholder="000000" required data-desktop-autofocus>
            @error('code')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="primary-button">Verify and Continue</button>
    </form>
    <div class="verify-switch">Cannot use your authenticator? <button type="button" class="link-button" id="showRecovery">Use a recovery code</button></div>
</div>

<div id="recoveryPanel" class="recovery-panel" @if(!old('is_recovery')) hidden @endif>
    <div class="recovery-note">Enter one of the recovery codes you saved when two-factor authentication was enabled. Each code works only once.</div>
    <form method="POST" action="{{ route('2fa.verify.store') }}" autocomplete="off">
        @csrf
        <input type="hidden" name="is_recovery" value="true">
        <div class="form-group">
            <label for="recovery_code">Recovery code</label>
            <input class="code-field" type="text" id="recovery_code" name="code" maxlength="30" autocomplete="off" placeholder="Enter recovery code" required>
            @error('code')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="primary-button">Verify Recovery Code</button>
    </form>
    <div class="verify-switch"><button type="button" class="link-button" id="showAuthenticator">Back to authenticator code</button></div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const authPanel = document.getElementById('authenticatorPanel');
        const recoveryPanel = document.getElementById('recoveryPanel');
        const code = document.getElementById('verification_code');
        document.getElementById('showRecovery')?.addEventListener('click', () => {
            authPanel.hidden = true;
            recoveryPanel.hidden = false;
            document.getElementById('recovery_code')?.focus();
        });
        document.getElementById('showAuthenticator')?.addEventListener('click', () => {
            recoveryPanel.hidden = true;
            authPanel.hidden = false;
            code?.focus();
        });
        code?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    })();
</script>
@endpush
