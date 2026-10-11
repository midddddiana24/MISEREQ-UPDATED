@extends('admin.layout')

@section('title', 'Preventive Maintenance Review')
@section('page-title', 'Preventive Maintenance Review')
@section('page-subtitle', 'Review the maintenance details, selected checks, and current status.')
@section('body-class', 'record-review-page')

@section('header-actions')
    <a href="{{ route('admin.pm-checklists.edit', $checklist) }}" class="btn-primary">Edit Checklist</a>
    <a href="{{ route('admin.pm-checklists.index') }}" class="btn-ghost">Back to Maintenance</a>
@endsection

@section('head')
    @include('admin.partials.record-review-styles')
@endsection

@section('content')
    @php
        $pdfUrl = route('admin.pm-checklists.pdf', ['pmChecklist' => $checklist, 'v' => $checklist->updated_at?->timestamp]);
        $selectedChecks = collect($checklist->hardware_checks ?? [])->map(fn ($value) => $hardwareOptions[$value] ?? $value)
            ->merge(collect($checklist->software_checks ?? [])->map(fn ($value) => $softwareOptions[$value] ?? $value))
            ->merge(collect($checklist->other_ict_checks ?? [])->map(fn ($value) => $otherIctCheckOptions[$value] ?? $value))
            ->merge(collect($checklist->info_systems_checks ?? [])->map(fn ($value) => $infoSystemsOptions[$value] ?? $value));
    @endphp

    <div class="review-workspace">
        <main class="review-main">
            <section class="review-card review-hero" aria-labelledby="maintenance-review-title">
                <div class="review-hero-top">
                    <div>
                        <span class="review-kicker">{{ $checklist->control_number }}</span>
                        <h2 id="maintenance-review-title">{{ $checklist->system_owner }}</h2>
                        <p>{{ $checklist->office_department ?: 'No office provided' }}</p>
                    </div>
                    <div class="review-status"><small>Current status</small><span class="status-badge {{ $checklist->stage_status }}">{{ str($checklist->stage_status)->title() }}</span></div>
                </div>
            </section>

            <section class="review-card" aria-labelledby="maintenance-details-title">
                <div class="review-card-head"><div><h3 id="maintenance-details-title">Checklist Details</h3><p>Equipment and inspection information.</p></div></div>
                <dl class="review-facts">
                    <div><dt>Inspection date</dt><dd>{{ $checklist->date_inspected?->format('M d, Y') ?: 'Not recorded' }}</dd></div>
                    <div><dt>Property number</dt><dd>{{ $checklist->property_number ?: 'Not recorded' }}</dd></div>
                    <div><dt>ICT type</dt><dd>{{ str($checklist->ict_type ?: 'Not recorded')->replace('_', ' ')->title() }}</dd></div>
                    <div><dt>Equipment detail</dt><dd>{{ $checklist->computer_type ? str($checklist->computer_type)->title() : ($checklist->other_ict_type ?: 'Not recorded') }}</dd></div>
                    <div><dt>Client account</dt><dd>{{ $checklist->user?->full_name ?: 'Standalone maintenance record' }}</dd></div>
                    <div><dt>Linked request</dt><dd>@if($checklist->serviceRequest)<a href="{{ route('admin.service-requests.show', $checklist->serviceRequest) }}">{{ $checklist->serviceRequest->control_number }}</a>@else Not linked @endif</dd></div>
                </dl>
            </section>

            <section class="review-card" aria-labelledby="maintenance-checks-title">
                <div class="review-card-head"><div><h3 id="maintenance-checks-title">Selected Checks</h3><p>{{ $selectedChecks->count() }} checklist {{ \Illuminate\Support\Str::plural('item', $selectedChecks->count()) }} recorded.</p></div></div>
                <div class="review-tags">
                    @forelse($selectedChecks as $label)<span class="review-tag">{{ $label }}</span>@empty<p class="review-empty">No checklist items selected.</p>@endforelse
                </div>
            </section>

            <section class="review-card" aria-labelledby="maintenance-findings-title">
                <div class="review-card-head"><h3 id="maintenance-findings-title">Findings and Recommendations</h3></div>
                <dl class="review-facts">
                    <div><dt>Findings</dt><dd>{{ $checklist->findings ?: 'Not recorded' }}</dd></div>
                    <div><dt>Recommendations</dt><dd>{{ $checklist->recommendations ?: 'Not recorded' }}</dd></div>
                </dl>
            </section>
        </main>

        <aside class="review-sidebar" aria-label="Maintenance review actions">
            <section class="review-card">
                <div class="review-card-head"><h3>Official PDF</h3></div>
                <p class="review-empty">Open the complete maintenance checklist in a new tab when you need to check or print it.</p>
                <div class="review-links" style="margin-top:12px;"><a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn-ghost">Open PDF</a></div>
            </section>
            <section class="review-card">
                <div class="review-card-head"><h3>Record Actions</h3></div>
                <div class="review-sidebar-actions">
                    <a href="{{ route('admin.pm-checklists.edit', $checklist) }}" class="btn-primary">Edit Checklist</a>
                    <form method="POST" action="{{ route('admin.pm-checklists.destroy', $checklist) }}" onsubmit="return confirm('Archive this preventive maintenance checklist?');">@csrf @method('DELETE')<input type="hidden" name="archive_reason" value="Archived from the maintenance review by an administrator."><button type="submit" class="btn-danger">Archive Record</button></form>
                </div>
            </section>
        </aside>
    </div>
@endsection
