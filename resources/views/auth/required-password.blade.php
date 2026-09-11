@extends('layouts.auth')

@section('title', 'Change Required Password')

@section('content')
<div class="auth-heading">
    <h2>Change your password</h2>
    <p>The MIS Office requires a new password before this account can continue.</p>
</div>

<form method="POST" action="{{ route('password.required.update') }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="current_password">Current password</label>
        <div class="input-wrap"><input id="current_password" type="password" name="current_password" required autocomplete="current-password" style="padding-left:12px;"></div>
        @error('current_password')<div class="error-message">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="password">New password</label>
        <div class="input-wrap"><input id="password" type="password" name="password" required autocomplete="new-password" style="padding-left:12px;"></div>
        @error('password')<div class="error-message">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirm new password</label>
        <div class="input-wrap"><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" style="padding-left:12px;"></div>
    </div>
    <button type="submit" class="primary-button">Change Password and Continue</button>
</form>
@endsection
