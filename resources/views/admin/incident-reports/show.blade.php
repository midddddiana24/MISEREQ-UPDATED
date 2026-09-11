@extends('admin.layout')

@section('title', 'Incident Report Review')
@section('page-title', 'Incident Report Review')
@section('page-subtitle', 'Preview the official incident form and open the MIS editing workspace when changes are needed.')
@section('body-class', 'record-review-page')

@section('header-actions')
    <a href="{{ route('admin.incident-reports.edit', $report) }}" class="btn-primary">Edit Report</a>
    <a href="{{ route('admin.incident-reports.index') }}" class="btn-ghost">Back to Incidents</a>
@endsection

@section('head')
    @include('admin.partials.record-pdf-review-styles')
@endsection

@section('content')
    @php
        $pdfUrl = route('admin.incident-reports.pdf', ['incidentReport' => $report, 'v' => $report->updated_at?->timestamp]);
        $statusClass = in_array($report->status, ['closed', 'resolved'], true) ? 'completed' : ($report->status === 'in_progress' ? 'processing' : 'pending');
        $selectedNature = collect($report->nature_of_incident ?? [])->map(fn ($value) => $natureOptions[$value] ?? str($value)->replace('_', ' ')->title());
    @endphp

    <div class="record-pdf-workspace">
        <aside class="record-pdf-sidebar" aria-label="Incident report summary and actions">
            <section class="record-pdf-card">
                <div class="record-pdf-heading">
                    <div><div class="record-pdf-id">{{ $report->document_number }}</div><div class="record-pdf-owner">{{ $report->reported_by }}</div><div class="record-pdf-meta">{{ $report->department ?: 'No department provided' }}</div></div>
                    <span class="status-badge {{ $statusClass }}">{{ str($report->status)->replace('_', ' ')->title() }}</span>
                </div>
                <div class="record-pdf-meta">{{ $report->user?->full_name ? 'Client record: '.$report->user->full_name : 'Standalone incident record' }}</div>
            </section>

            <section class="record-pdf-card">
                <div class="record-pdf-heading"><div><h2>Report Summary</h2><p>Important details at a glance.</p></div></div>
                <dl class="record-pdf-facts">
                    <div class="record-pdf-fact"><dt>Incident date</dt><dd>{{ optional($report->date_of_incident)->format('M d, Y') ?: '-' }}</dd></div>
                    <div class="record-pdf-fact"><dt>Incident time</dt><dd>{{ $report->time_of_incident ?: '-' }}</dd></div>
                    <div class="record-pdf-fact"><dt>Received by</dt><dd>{{ $report->received_by ?: '-' }}</dd></div>
                    @if($report->serviceRequest)<div class="record-pdf-fact"><dt>Service request</dt><dd><a href="{{ route('admin.service-requests.show', $report->serviceRequest) }}">{{ $report->serviceRequest->control_number }}</a></dd></div>@endif
                </dl>
            </section>

            <section class="record-pdf-card">
                <div class="record-pdf-heading"><div><h3>Nature of Incident</h3><p>Categories selected on this report.</p></div></div>
                <div class="record-pdf-tags">
                    @forelse($selectedNature as $label)<span class="record-pdf-tag">{{ $label }}</span>@empty<span class="record-pdf-meta">No categories selected.</span>@endforelse
                </div>
            </section>

            <section class="record-pdf-card">
                <div class="record-pdf-heading"><div><h3>Record Actions</h3><p>Edit the form or archive this record.</p></div></div>
                <div class="record-pdf-actions">
                    <a href="{{ route('admin.incident-reports.edit', $report) }}" class="btn-primary">Edit Report</a>
                    <form method="POST" action="{{ route('admin.incident-reports.destroy', $report) }}" onsubmit="return confirm('Archive this incident report?');">@csrf @method('DELETE')<input type="hidden" name="archive_reason" value="Archived from the incident review by an administrator."><button type="submit" class="btn-danger">Archive Record</button></form>
                </div>
            </section>
        </aside>

        <section class="record-pdf-preview" aria-labelledby="incident-pdf-title">
            <header class="record-pdf-preview-head"><strong id="incident-pdf-title">Official ICT Incident Report Form</strong><div class="record-pdf-preview-tools"><a href="{{ $pdfUrl }}" target="_blank" rel="noopener" data-pdf-action>Open PDF</a></div></header>
            <div class="record-pdf-loading" role="status">Preparing document preview...</div>
            <iframe class="record-pdf-frame" src="{{ $pdfUrl }}#toolbar=1&navpanes=0&view=FitH" title="ICT incident report PDF preview"></iframe>
            <div class="record-pdf-fallback">If the preview is unavailable, <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" data-pdf-action>open the PDF in a new tab</a>.</div>
        </section>
    </div>
@endsection
