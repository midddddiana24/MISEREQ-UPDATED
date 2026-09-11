@extends('admin.layout')

@section('title', 'Edit Technician')

@section('page-title', 'Edit Technician')

@section('page-subtitle', 'Update technician assignment details, status, and notes while preserving the current backend validation and save behavior.')

@section('content')
    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $technician->full_name }}</h2>
                <p>{{ $technician->email }}{{ $technician->phone ? ' • ' . $technician->phone : '' }}</p>
            </div>
            <span class="status-badge {{ $technician->status }}">{{ str_replace('_', ' ', $technician->status) }}</span>
        </div>

        <form method="POST" action="{{ route('admin.technicians.update', $technician->id) }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="field form-col-6">
                <label>Name</label>
                <input type="text" value="{{ $technician->full_name }}" disabled>
            </div>

            <div class="field form-col-6">
                <label>Email</label>
                <input type="text" value="{{ $technician->email }}" disabled>
            </div>

            <div class="field form-col-6">
                <label for="job_assignment">Job Assignment</label>
                @php($currentAssignment = old('job_assignment', \App\Models\Technician::normalizeJobAssignment($technician->job_assignment, $technician->specialization)))
                <select id="job_assignment" name="job_assignment" required>
                    <option value="">Select assignment</option>
                    @foreach(\App\Models\Technician::JOB_ASSIGNMENTS as $assignment)
                        <option value="{{ $assignment }}" @selected($currentAssignment === $assignment)>{{ $assignment }}</option>
                    @endforeach
                </select>
                <small>This assignment controls the automatic checkbox in the service request PDF.</small>
                @error('job_assignment') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-6">
                <label for="specialization">Specialization</label>
                <input id="specialization" type="text" name="specialization" value="{{ old('specialization', $technician->specialization) }}">
                @error('specialization') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach(['available' => 'Available', 'busy' => 'Busy', 'on_leave' => 'On Leave'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $technician->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="phone">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $technician->phone) }}">
                @error('phone') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-4">
                <label for="skills">Skills</label>
                <input id="skills" type="text" name="skills" value="{{ old('skills', is_array($technician->skills) ? implode(', ', $technician->skills) : $technician->skills) }}">
                @error('skills') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="field form-col-12">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes">{{ old('notes', $technician->notes) }}</textarea>
                @error('notes') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-col-12 form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.technicians.show', $technician->id) }}" class="btn-ghost">Back to Profile</a>
            </div>
        </form>
    </section>
@endsection
