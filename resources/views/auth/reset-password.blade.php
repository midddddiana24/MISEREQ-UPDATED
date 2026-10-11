@extends('layouts.auth')

@section('title', 'Reset Password')
@section('brand-title', 'A fresh password, a secure account.')
@section('brand-copy', 'Choose a strong new password to restore access to your service requests and protect your campus account.')

@section('content')
    <div class="auth-heading">
        <h1>Set a new password</h1>
        <p>Enter your account email and choose a strong password you haven’t used before.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group @error('email') error @enderror">
            <label for="email">Email address</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email" placeholder="name@wvsu.edu.ph">
            </div>
            @error('email')<div class="error-message">{{ $message }}</div>@enderror
        </div>

        <div class="form-group @error('password') error @enderror">
            <label for="password">New password</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
                <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.1 12a10.5 10.5 0 0 1 19.8 0 10.5 10.5 0 0 1-19.8 0Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
            @error('password')<div class="error-message">{{ $message }}</div>@enderror
        </div>

        <div class="form-group @error('password_confirmation') error @enderror">
            <label for="password_confirmation">Confirm new password</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your new password">
                <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.1 12a10.5 10.5 0 0 1 19.8 0 10.5 10.5 0 0 1-19.8 0Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
            @error('password_confirmation')<div class="error-message">{{ $message }}</div>@enderror
        </div>
        <p class="password-hint">Use at least 12 characters with uppercase, lowercase, number, and special character.</p>

        <button type="submit" class="primary-button">
            <span>Update password</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
        </button>
    </form>

    <p class="auth-footer">Back at the login screen? <a href="{{ route('login') }}" class="text-link">Sign in</a></p>
@endsection
