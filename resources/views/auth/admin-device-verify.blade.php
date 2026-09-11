@extends('layouts.auth')

@section('title', 'Confirm Administrator Device')

@section('content')
<style>
    .device-mark { width: 50px; height: 50px; margin: 0 auto 14px; display: grid; place-items: center; color: var(--brand-dark); border: 1px solid #c9dce8; border-radius: 50%; background: var(--brand-soft); }
    .device-mark svg { width: 24px; height: 24px; }
    .device-summary { margin: 0 0 18px; padding: 12px 14px; border: 1px solid #d9e2e7; border-radius: 8px; background: #f7fafb; font-size: 13px; line-height: 1.55; }
    .code-input { width: 100%; min-height: 48px; padding: 10px 12px; text-align: center; letter-spacing: .28em; font-size: 20px; font-weight: 750; border: 1px solid #cbd6de; border-radius: 7px; outline: none; }
    .code-input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(23,79,117,.11); }
    .device-divider { display: flex; align-items: center; gap: 10px; margin: 18px 0; color: var(--muted); font-size: 12px; }
    .device-divider::before, .device-divider::after { height: 1px; flex: 1; background: #dfe7eb; content: ""; }
    .secondary-button { width: 100%; min-height: 44px; padding: 10px 14px; color: var(--brand-dark); border: 1px solid #b9cbd5; border-radius: 7px; background: #fff; cursor: pointer; font-weight: 700; }
</style>

<div class="auth-heading" style="text-align:center;">
    <div class="device-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2.5" width="14" height="19" rx="2"/><path d="M9 18h6"/><path d="M9.5 10.5l1.7 1.7 3.5-3.7"/></svg>
    </div>
    <h2>Confirm this administrator device</h2>
    <p>Administrator access is paused until this device is trusted.</p>
</div>

@if(session('admin_device_bootstrap'))
    <div class="device-summary">
        This is the first trusted device for this administrator account. Confirm it after completing password and two-factor verification.
    </div>
    <form method="POST" action="{{ route('admin.device.continue') }}">
        @csrf
        <button type="submit" class="primary-button">Trust This Device and Continue</button>
    </form>
@else
    <div class="device-summary">
        <strong>{{ $challenge?->device_name ?: 'New device' }}</strong><br>
        IP address: {{ $challenge?->ip_address ?: 'Unavailable' }}<br>
        Requested: {{ $challenge?->created_at?->format('M d, Y h:i A') }}
    </div>

    <p style="margin:0 0 14px;color:var(--muted);font-size:13px;line-height:1.55;">
        Approve this sign-in from a device already trusted for this administrator account, then select “Check Approval.”
    </p>
    <form method="POST" action="{{ route('admin.device.continue') }}">
        @csrf
        <button type="submit" class="secondary-button">Check Approval</button>
    </form>

    <div class="device-divider">or use the emailed code</div>

    <form method="POST" action="{{ route('admin.device.verify-code') }}" autocomplete="off">
        @csrf
        <div class="form-group">
            <label for="device_code">Six-digit verification code</label>
            <input class="code-input" id="device_code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" required>
            @error('code')<div class="error-message">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="primary-button">Verify and Trust Device</button>
    </form>
@endif

<div class="back-home">
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="link-button">Cancel and sign out</button></form>
</div>
@endsection
