@extends('admin.layout')

@section('title', 'Technician Profile')

@section('page-title', 'Technician Profile')

@section('page-subtitle', 'Detailed technician record for review before assignments, edits, or deletion.')

@section('header-actions')
    <a href="{{ route('admin.technicians.edit', $technician->id) }}" class="btn-primary">Edit Technician</a>
@endsection

@section('content')
    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $technician->full_name }}</h2>
                <p>{{ $technician->email }}</p>
            </div>
            <span class="status-badge {{ $technician->status }}">{{ str_replace('_', ' ', $technician->status) }}</span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">Job Assignment</span>
                <div class="value">{{ $technician->job_assignment ?? '-' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Specialization</span>
                <div class="value">{{ $technician->specialization ?? '-' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Phone</span>
                <div class="value">{{ $technician->phone ?? '-' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Current Workload</span>
                <div class="value">{{ $technician->current_workload ?? 0 }}</div>
            </div>
            <div class="info-item">
                <span class="label">Skills</span>
                <div class="value">
                    {{ is_array($technician->skills) ? implode(', ', $technician->skills) : ($technician->skills ?? '-') }}
                </div>
            </div>
            <div class="info-item">
                <span class="label">Created</span>
                <div class="value">{{ optional($technician->created_at)->format('M d, Y h:i A') ?? '-' }}</div>
            </div>
            <div class="info-item" style="grid-column: 1 / -1;">
                <span class="label">Notes</span>
                <div class="value">{{ $technician->notes ?: 'No notes recorded.' }}</div>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 22px;">
            <a href="{{ route('admin.technicians.index') }}" class="btn-ghost">Back to List</a>
            @if($technician->is_active)<form method="POST" action="{{ route('admin.technicians.destroy', $technician->id) }}" onsubmit="return confirm('Deactivate this technician? Historical assignments will be preserved.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Deactivate Technician</button>
            </form>@else<form method="POST" action="{{ route('admin.technicians.activate', $technician) }}">@csrf<button type="submit" class="btn-primary">Reactivate Technician</button></form>@endif
        </div>
    </section>
@endsection
