@extends('admin.layout')

@section('title', 'Edit PM Checklist')
@section('page-title', 'Edit PM Checklist')
@section('page-subtitle', 'Update maintenance details and MIS review in one workspace.')

@section('header-actions')
    <a href="{{ route('admin.pm-checklists.pdf', ['pmChecklist' => $checklist, 'v' => $checklist->updated_at?->timestamp]) }}" class="btn-ghost" target="_blank" rel="noopener">Preview PDF</a>
    <a href="{{ route('admin.pm-checklists.show', $checklist) }}" class="btn-ghost">Cancel</a>
    <button type="submit" form="pmChecklistEditForm" class="btn-primary">Save Changes</button>
@endsection

@section('content')
    <section class="panel admin-record-edit pm-editor-panel">
        <form id="pmChecklistEditForm" method="POST" action="{{ route('admin.pm-checklists.update', $checklist) }}" class="form-grid pm-edit-form">
            @csrf
            @method('PUT')
            @include('admin.pm-checklists._form', ['checklist' => $checklist, 'recordEditMode' => true])
            <footer class="admin-record-edit-footer">
                <p>Changes to this checklist also update its PDF. Check the selected checks and status before saving.</p>
                <div><a href="{{ route('admin.pm-checklists.show', $checklist) }}" class="btn-ghost">Cancel</a><button type="submit" class="btn-primary">Save Changes</button></div>
            </footer>
        </form>
    </section>
    @include('admin.partials.record-edit-styles')
@endsection
