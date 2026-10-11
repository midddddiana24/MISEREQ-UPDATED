@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('brand-title', 'Let’s get you back into your account.')
@section('brand-copy', 'Request a secure password-reset link using the email address connected to your WVSU service desk account.')

@section('content')
    <div class="auth-heading">
        <h1>Forgot your password?</h1>
        <p>Enter your registered email and we’ll send a secure link to create a new password.</p>
    </div>

    @if (session('status'))
        <div class="alert success" role="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group @error('email') error @enderror">
            <label for="email">Registered email address</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required data-desktop-autofocus autocomplete="email" placeholder="name@wvsu.edu.ph">
            </div>
            @error('email')<div class="error-message">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="primary-button">
            <span>Send reset link</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
        </button>
    </form>

    <p class="auth-footer">Remembered your password? <a href="{{ route('login') }}" class="text-link">Return to sign in</a></p>
@endsection
