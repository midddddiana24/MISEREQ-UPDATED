@extends('admin.layout')

@section('title', 'Add Technician')

@section('page-title', 'Add Technician')

@section('page-subtitle', 'Create a technician profile using the same fields the current admin workflow already expects.')

@section('content')
    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Technician Details</h2>
                <p>Required fields are marked by the validation rules already defined in the controller.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.technicians.store') }}" class="form-grid">
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

            <div class="field form-col-6">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-6">
                <label for="phone">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}">
                @error('phone') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-6">
                <label for="job_assignment">Job Assignment</label>
                <select id="job_assignment" name="job_assignment" required>
                    <option value="">Select assignment</option>
                    @foreach(\App\Models\Technician::JOB_ASSIGNMENTS as $assignment)
                        <option value="{{ $assignment }}" @selected(old('job_assignment') === $assignment)>{{ $assignment }}</option>
                    @endforeach
                </select>
                <small>This assignment controls the automatic checkbox in the service request PDF.</small>
                @error('job_assignment') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-6">
                <label for="specialization">Specialization</label>
                <input id="specialization" type="text" name="specialization" value="{{ old('specialization') }}">
                @error('specialization') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="busy" {{ old('status') === 'busy' ? 'selected' : '' }}>Busy</option>
                    <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
                @error('status') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-8">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                @error('notes') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-col-12 form-actions">
                <button type="submit" class="btn-primary">Create Technician</button>
                <a href="{{ route('admin.technicians.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection
