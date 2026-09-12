@extends('layouts.auth')

@section('title', 'Login')
@section('brand-title', 'Campus support, right when you need it.')
@section('brand-copy', 'Sign in to submit new requests, follow their progress, and stay connected with the MIS support team.')

@section('content')
<div class="auth-heading">
    <h1>Welcome back</h1>
    <p>Use your university service portal account to continue.</p>
</div>

@if (session('status'))
<div class="alert success" role="status">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M20 6 9 17l-5-5" />
    </svg>
    <span>{{ session('status') }}</span>
</div>
@endif

@foreach (['error', 'warning'] as $messageType)
@if (session($messageType))
<div class="alert" role="alert">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 8v4m0 4h.01" />
    </svg>
    <span>{{ session($messageType) }}</span>
</div>
@endif
@endforeach

@if ($errors->has('login'))
<div class="alert" role="alert">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 8v4m0 4h.01" />
    </svg>
    <span>{{ $errors->first('login') }}</span>
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group @error('username') error @enderror">
        <label for="username">Username or email</label>
        <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M20 21a8 8 0 0 0-16 0" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Enter your username or email">
        </div>
        @error('username')<div class="error-message">{{ $message }}</div>@enderror
    </div>

    <div class="form-group @error('password') error @enderror">
        <label for="password">Password</label>
        <div class="input-wrap">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect width="18" height="11" x="3" y="11" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M2.1 12a10.5 10.5 0 0 1 19.8 0 10.5 10.5 0 0 1-19.8 0Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </button>
        </div>
        @error('password')<div class="error-message">{{ $message }}</div>@enderror
    </div>

    <div class="form-options">
        <label class="check-label" for="remember">
            <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>Keep me signed in</span>
        </label>
        <a href="{{ route('password.request') }}" class="text-link">Forgot password?</a>
    </div>

    <button type="submit" class="primary-button">
        <span>Sign in</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M5 12h14m-6-6 6 6-6 6" />
        </svg>
    </button>
</form>

<p class="auth-footer">Need a campus service account? <a href="{{ route('register') }}" class="text-link">Create an account</a></p>
<div class="back-home"><a href="{{ route('home') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m15 18-6-6 6-6" />
        </svg> Back to home</a></div>
@endsection