@extends('admin.layout')

@section('title', 'Edit Incident Report')
@section('page-title', 'Edit Incident Report')
@section('page-subtitle', 'Correct incident details and MIS follow-up in one workspace.')

@section('header-actions')
    <a href="{{ route('admin.incident-reports.pdf', ['incidentReport' => $report, 'v' => $report->updated_at?->timestamp]) }}" class="btn-ghost" target="_blank" rel="noopener">Preview PDF</a>
    <a href="{{ route('admin.incident-reports.show', $report) }}" class="btn-ghost">Cancel</a>
    <button type="submit" form="incidentEditForm" class="btn-primary">Save Changes</button>
@endsection

@section('content')
    <section class="panel admin-record-edit incident-editor-panel">
        <form id="incidentEditForm" method="POST" action="{{ route('admin.incident-reports.update', $report) }}" class="form-grid incident-edit-form">
            @csrf
            @method('PUT')
            @include('admin.incident-reports._form', ['report' => $report, 'recordEditMode' => true])
            <footer class="admin-record-edit-footer">
                <p>Changes to this incident record also update its PDF. Check the linked request and status before saving.</p>
                <div><a href="{{ route('admin.incident-reports.show', $report) }}" class="btn-ghost">Cancel</a><button type="submit" class="btn-primary">Save Changes</button></div>
            </footer>
        </form>
    </section>
    @include('admin.partials.record-edit-styles')
@endsection
