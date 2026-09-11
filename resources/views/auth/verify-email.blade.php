@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    <div class="auth-heading">
        <h2>Verify your email</h2>
        <p>Before continuing, open the verification message sent to your registered email address and select the verification link.</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="alert success" role="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
            <span>A new verification link has been sent to your email.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="primary-button">Resend verification email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="back-home">
        @csrf
        <button type="submit" class="text-link" style="border:0;background:transparent;cursor:pointer;">Sign out and return to login</button>
    </form>
@endsection
