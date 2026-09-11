@extends('admin.layout')

@section('title', 'Add User')

@section('page-title', 'Create User Account')

@section('page-subtitle', 'Provision a new user, technician, or admin account using the existing validation and storage rules.')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ route('admin.users.store') }}" class="form-grid">
            @csrf

            <div class="field form-col-6">
                <label for="first_name">First Name</label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required>
                @error('first_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-6">
                <label for="last_name">Last Name</label>
                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required>
                @error('last_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="office">Office or Department</label>
                <input id="office" type="text" name="office" value="{{ old('office') }}" required>
                @error('office') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required>
                @error('username') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    @foreach(['user' => 'School User', 'admin' => 'Administrator'] as $value => $label)
                        <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                @error('password') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-8">
                <label>Password Guidance</label>
                <div class="info-item">
                    <div class="value">Use at least 8 characters with both letters and numbers.</div>
                </div>
            </div>

            <div class="form-col-12 form-actions">
                <button type="submit" class="btn-primary">Create User</button>
                <a href="{{ route('admin.users.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection
