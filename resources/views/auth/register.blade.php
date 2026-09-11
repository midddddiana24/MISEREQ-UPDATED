@extends('layouts.auth')

@section('title', 'Create Account')
@section('content-class', '')
@section('brand-title', 'Your direct line to MIS support.')
@section('brand-copy', 'Create your campus account once, then request assistance and follow every update from submission to resolution.')

@section('content')
    @php
        $offices = [
            'MIS Office', 'College of ICT', 'College of Engineering', 'College of Education',
            'Registrar Office', 'Finance Office', 'Student Affairs', 'Library',
            'Human Resources', 'Maintenance',
        ];
    @endphp

    <div class="auth-heading">
        <h2>Create your account</h2>
        <p>Enter your university details to create a secure MIS service portal account.</p>
    </div>

    @if ($errors->has('error'))
        <div class="alert" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            <span>{{ $errors->first('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-section">Personal information</div>
        <div class="form-grid">
            <div class="form-group @error('first_name') error @enderror">
                <label for="first_name">First name</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name" placeholder="First name">
                </div>
                @error('first_name')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            <div class="form-group @error('last_name') error @enderror">
                <label for="last_name">Last name</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" placeholder="Last name">
                </div>
                @error('last_name')<div class="error-message">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group @error('middle_initial') error @enderror">
                <label for="middle_initial">Middle initial <span class="optional">Optional</span></label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                    <input type="text" id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="3" placeholder="e.g. A.">
                </div>
                @error('middle_initial')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            <div class="form-group @error('office') error @enderror">
                <label for="office">Office / Department</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M6 21V7l6-4 6 4v14M9 10h.01M9 14h.01M15 10h.01M15 14h.01M10 21v-3h4v3"/></svg>
                    <select id="office" name="office" required>
                        <option value="">Select department</option>
                        @foreach ($offices as $office)
                            <option value="{{ $office }}" @selected(old('office') === $office)>{{ $office }}</option>
                        @endforeach
                    </select>
                    <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </div>
                @error('office')<div class="error-message">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-section">Account information</div>
        <div class="form-grid">
            <div class="form-group @error('email') error @enderror">
                <label for="email">Email address</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@wvsu.edu.ph">
                </div>
                @error('email')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            <div class="form-group @error('username') error @enderror">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="Choose a username">
                </div>
                @error('username')<div class="error-message">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group @error('password') error @enderror">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Create a password">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.1 12a10.5 10.5 0 0 1 19.8 0 10.5 10.5 0 0 1-19.8 0Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
                @error('password')<div class="error-message">{{ $message }}</div>@enderror
            </div>

            <div class="form-group @error('password_confirmation') error @enderror">
                <label for="password_confirmation">Confirm password</label>
                <div class="input-wrap">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your password">
                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.1 12a10.5 10.5 0 0 1 19.8 0 10.5 10.5 0 0 1-19.8 0Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
                @error('password_confirmation')<div class="error-message">{{ $message }}</div>@enderror
            </div>
        </div>
        <p class="password-hint">Use at least 12 characters with uppercase, lowercase, number, and special character.</p>

        <button type="submit" class="primary-button">
            <span>Create my account</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </button>
    </form>

    <p class="auth-footer">Already have an account? <a href="{{ route('login') }}" class="text-link">Sign in instead</a></p>
    <div class="back-home"><a href="{{ route('home') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg> Back to home</a></div>
@endsection
