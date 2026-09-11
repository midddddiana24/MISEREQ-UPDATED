@extends('admin.layout')

@section('title', 'Edit PM Checklist')
@section('page-title', 'Edit PM Checklist')
@section('page-subtitle', 'Update preventive maintenance details, selected checks, and admin review fields from the admin panel.')

@section('header-actions')
    <a href="{{ route('admin.pm-checklists.pdf', ['pmChecklist' => $checklist, 'v' => $checklist->updated_at?->timestamp]) }}" class="btn-ghost" target="_blank" rel="noopener">Preview PDF</a>
    <a href="{{ route('admin.pm-checklists.show', $checklist) }}" class="btn-ghost">Back to Record</a>
    <button type="submit" form="pmChecklistEditForm" class="btn-primary">Save Changes</button>
@endsection

@section('content')
    <section class="panel pm-editor-panel">
        <div class="section-heading">
            <div>
                <h2>{{ $checklist->control_number }}</h2>
                <p>{{ $checklist->system_owner }}{{ $checklist->user ? ' - ' . $checklist->user->full_name : '' }}</p>
            </div>
        </div>

        <form id="pmChecklistEditForm" method="POST" action="{{ route('admin.pm-checklists.update', $checklist) }}" class="form-grid pm-edit-form">
            @csrf
            @method('PUT')
            @include('admin.pm-checklists._form', ['checklist' => $checklist])
        </form>
    </section>
@endsection
