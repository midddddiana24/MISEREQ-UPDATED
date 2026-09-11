@extends('admin.layout')

@section('title', 'Edit Incident Report')
@section('page-title', 'Edit Incident Report')
@section('page-subtitle', 'Update the submitted client record, its admin annotations, and its current incident status.')

@section('header-actions')
    <a href="{{ route('admin.incident-reports.pdf', ['incidentReport' => $report, 'v' => $report->updated_at?->timestamp]) }}" class="btn-ghost" target="_blank" rel="noopener">Preview PDF</a>
    <a href="{{ route('admin.incident-reports.show', $report) }}" class="btn-ghost">Back to Record</a>
    <button type="submit" form="incidentEditForm" class="btn-primary">Update Incident</button>
@endsection

@section('content')
    <section class="panel incident-editor-panel">
        <div class="section-heading">
            <div>
                <h2>{{ $report->document_number }}</h2>
                <p>{{ $report->reported_by }}{{ $report->user ? ' - ' . $report->user->full_name : '' }}</p>
            </div>
        </div>

        <form id="incidentEditForm" method="POST" action="{{ route('admin.incident-reports.update', $report) }}" class="form-grid incident-edit-form">
            @csrf
            @method('PUT')
            @include('admin.incident-reports._form', ['report' => $report])
        </form>
    </section>
@endsection
