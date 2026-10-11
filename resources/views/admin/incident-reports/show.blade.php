@extends('admin.layout')

@section('title', 'Incident Report Review')
@section('page-title', 'Incident Report Review')
@section('page-subtitle', 'Review the incident details, status, and MIS follow-up.')
@section('body-class', 'record-review-page')

@section('header-actions')
    <a href="{{ route('admin.incident-reports.edit', $report) }}" class="btn-primary">Edit Report</a>
    <a href="{{ route('admin.incident-reports.index') }}" class="btn-ghost">Back to Incidents</a>
@endsection

@section('head')
    @include('admin.partials.record-review-styles')
@endsection

@section('content')
    @php
        $pdfUrl = route('admin.incident-reports.pdf', ['incidentReport' => $report, 'v' => $report->updated_at?->timestamp]);
        $selectedNature = collect($report->nature_of_incident ?? [])->map(fn ($value) => $natureOptions[$value] ?? str($value)->replace('_', ' ')->title());
    @endphp

    <div class="review-workspace">
        <main class="review-main">
            <section class="review-card review-hero" aria-labelledby="incident-review-title">
                <div class="review-hero-top">
                    <div>
                        <span class="review-kicker">{{ $report->document_number }}</span>
                        <h2 id="incident-review-title">{{ $report->reported_by }}</h2>
                        <p>{{ $report->department ?: 'No department provided' }}</p>
                    </div>
                    <div class="review-status"><small>Current status</small><span class="status-badge {{ $report->stage_status }}">{{ str($report->stage_status)->title() }}</span></div>
                </div>
            </section>

            <section class="review-card" aria-labelledby="incident-details-title">
                <div class="review-card-head"><div><h3 id="incident-details-title">Report Details</h3><p>The submitted incident and receipt information.</p></div></div>
                <dl class="review-facts">
                    <div><dt>Incident date</dt><dd>{{ $report->date_of_incident?->format('M d, Y') ?: 'Not recorded' }}</dd></div>
                    <div><dt>Incident time</dt><dd>{{ $report->time_of_incident ?: 'Not recorded' }}</dd></div>
                    <div><dt>Received by</dt><dd>{{ $report->received_by ?: 'Not recorded' }}</dd></div>
                    <div><dt>Received date</dt><dd>{{ $report->received_date?->format('M d, Y') ?: 'Not recorded' }}</dd></div>
                    <div><dt>Client account</dt><dd>{{ $report->user?->full_name ?: 'Standalone incident record' }}</dd></div>
                    <div><dt>Linked request</dt><dd>@if($report->serviceRequest)<a href="{{ route('admin.service-requests.show', $report->serviceRequest) }}">{{ $report->serviceRequest->control_number }}</a>@else Not linked @endif</dd></div>
                </dl>
            </section>

            <section class="review-card" aria-labelledby="incident-nature-title">
                <div class="review-card-head"><div><h3 id="incident-nature-title">Nature of Incident</h3><p>All categories recorded for this report.</p></div></div>
                <div class="review-tags">
                    @forelse($selectedNature as $label)<span class="review-tag">{{ $label }}</span>@empty<p class="review-empty">No categories selected.</p>@endforelse
                </div>
                @if($report->nature_others)<p class="review-copy" style="margin-top:12px;"><strong>Other category:</strong> {{ $report->nature_others }}</p>@endif
            </section>

            <section class="review-card" aria-labelledby="incident-narrative-title">
                <div class="review-card-head"><h3 id="incident-narrative-title">Incident Narrative</h3></div>
                <p class="review-copy">{{ $report->details_of_incident ?: 'No incident details recorded.' }}</p>
            </section>

            <section class="review-card" aria-labelledby="incident-followup-title">
                <div class="review-card-head"><h3 id="incident-followup-title">MIS Action and Resolution</h3></div>
                <dl class="review-facts">
                    <div><dt>Action plan</dt><dd>{{ $report->action_plan ?: 'Not recorded' }}</dd></div>
                    <div><dt>Remarks</dt><dd>{{ $report->remarks ?: 'Not recorded' }}</dd></div>
                </dl>
            </section>
        </main>

        <aside class="review-sidebar" aria-label="Incident review actions">
            <section class="review-card">
                <div class="review-card-head"><h3>Official PDF</h3></div>
                <p class="review-empty">Open the complete incident form in a new tab when you need to check or print it.</p>
                <div class="review-links" style="margin-top:12px;"><a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn-ghost">Open PDF</a></div>
            </section>
            <section class="review-card">
                <div class="review-card-head"><h3>Record Actions</h3></div>
                <div class="review-sidebar-actions">
                    <a href="{{ route('admin.incident-reports.edit', $report) }}" class="btn-primary">Edit Report</a>
                    <form method="POST" action="{{ route('admin.incident-reports.destroy', $report) }}" onsubmit="return confirm('Archive this incident report?');">@csrf @method('DELETE')<input type="hidden" name="archive_reason" value="Archived from the incident review by an administrator."><button type="submit" class="btn-danger">Archive Record</button></form>
                </div>
            </section>
        </aside>
    </div>
@endsection
