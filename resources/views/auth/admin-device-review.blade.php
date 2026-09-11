@extends('layouts.auth')

@section('title', 'Review Administrator Sign-In')

@section('content')
<style>
    .review-details { display: grid; gap: 9px; margin: 18px 0; padding: 14px; border: 1px solid #d9e2e7; border-radius: 8px; background: #f7fafb; }
    .review-row { display: flex; justify-content: space-between; gap: 18px; font-size: 13px; }
    .review-row span { color: var(--muted); }
    .review-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .deny-button { min-height: 44px; padding: 10px 14px; color: #8d2018; border: 1px solid #e0b5b1; border-radius: 7px; background: #fff7f6; cursor: pointer; font-weight: 750; }
    @media (max-width: 420px) { .review-actions { grid-template-columns: 1fr; } .review-row { align-items:flex-start; flex-direction:column; gap:2px; } }
</style>

<div class="auth-heading">
    <h2>Is this your sign-in?</h2>
    <p>Only approve this request if you recognize the device, location, and time.</p>
</div>

<div class="review-details">
    <div class="review-row"><span>Status</span><strong>{{ $challenge->approved_at ? 'Approved' : ($challenge->denied_at ? 'Denied' : ($challenge->expires_at->isPast() ? 'Expired' : 'Waiting')) }}</strong></div>
    <div class="review-row"><span>Device</span><strong>{{ $challenge->device_name ?: 'Unknown device' }}</strong></div>
    <div class="review-row"><span>IP address</span><strong>{{ $challenge->ip_address ?: 'Unavailable' }}</strong></div>
    <div class="review-row"><span>Requested</span><strong>{{ $challenge->created_at->format('M d, Y h:i A') }}</strong></div>
</div>

@if($challenge->isPending())
    <div class="review-actions">
        <form method="POST" action="{{ route('admin.device.approve', $challenge->public_id) }}">@csrf<button type="submit" class="primary-button">Yes, Approve</button></form>
        <form method="POST" action="{{ route('admin.device.deny', $challenge->public_id) }}" onsubmit="return confirm('Deny this sign-in and end the pending session?');">@csrf<button type="submit" class="deny-button">No, Deny Sign-In</button></form>
    </div>
@else
    <div class="alert {{ $challenge->approved_at ? 'success' : '' }}">This sign-in request has already been resolved.</div>
@endif

<div class="back-home"><a href="{{ route('admin.dashboard') }}">Return to administrator dashboard</a></div>
@endsection
