@extends('layouts.auth')

@section('title', 'Confirm Password')

@section('content')
    <div class="auth-heading">
        <h2>Confirm your password</h2>
        <p>This is a protected account action. Enter your current password to continue.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="form-group @error('password') error @enderror">
            <label for="password">Current password</label>
            <div class="input-wrap">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password" name="password" required autofocus autocomplete="current-password" placeholder="Enter your current password">
                <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2.1 12a10.5 10.5 0 0 1 19.8 0 10.5 10.5 0 0 1-19.8 0Z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
            @error('password')<div class="error-message">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="primary-button">Continue securely</button>
    </form>

    <div class="back-home"><a href="{{ route('user.settings') }}">Cancel and return to settings</a></div>
@endsection
