@extends('admin.layout')

@section('title', 'Edit User')

@section('page-title', 'Edit User')

@section('page-subtitle', 'Update user identity and access role. Password recovery is handled securely from the user profile.')

@section('content')
    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $user->full_name }}</h2>
                <p>{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="field form-col-6">
                <label for="first_name">First Name</label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                @error('first_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-6">
                <label for="last_name">Last Name</label>
                <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                @error('last_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="office">Office or Department</label>
                <input id="office" type="text" name="office" value="{{ old('office', $user->office) }}" required>
                @error('office') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username', $user->username) }}" required>
                @error('username') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    @foreach(['user' => 'School User', 'admin' => 'Administrator'] as $value => $label)
                        <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-8">
                <label>Account Status</label>
                <div class="info-item">
                    <div class="value">
                        Email verified: {{ $user->email_verified_at ? 'Yes' : 'No' }}<br>
                        Password changes are completed by the user through a secure one-time reset link.
                    </div>
                </div>
            </div>

            <div class="form-col-12 form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn-ghost">Back to Profile</a>
            </div>
        </form>
    </section>
@endsection
