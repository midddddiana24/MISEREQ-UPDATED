@extends('admin.layout')

@section('title', 'Preventive Maintenance Review')
@section('page-title', 'Preventive Maintenance Review')
@section('page-subtitle', 'Preview the official maintenance checklist and open the MIS editing workspace when changes are needed.')
@section('body-class', 'record-review-page')

@section('header-actions')
    <a href="{{ route('admin.pm-checklists.edit', $checklist) }}" class="btn-primary">Edit Checklist</a>
    <a href="{{ route('admin.pm-checklists.index') }}" class="btn-ghost">Back to Maintenance</a>
@endsection

@section('head')
    @include('admin.partials.record-pdf-review-styles')
@endsection

@section('content')
    @php
        $pdfUrl = route('admin.pm-checklists.pdf', ['pmChecklist' => $checklist, 'v' => $checklist->updated_at?->timestamp]);
        $statusClass = $checklist->status === 'completed' ? 'completed' : ($checklist->status === 'needs_action' ? 'rejected' : 'pending');
        $selectedChecks = collect($checklist->hardware_checks ?? [])->map(fn ($value) => $hardwareOptions[$value] ?? $value)
            ->merge(collect($checklist->software_checks ?? [])->map(fn ($value) => $softwareOptions[$value] ?? $value))
            ->merge(collect($checklist->other_ict_checks ?? [])->map(fn ($value) => $otherIctCheckOptions[$value] ?? $value))
            ->merge(collect($checklist->info_systems_checks ?? [])->map(fn ($value) => $infoSystemsOptions[$value] ?? $value));
    @endphp

    <div class="record-pdf-workspace">
        <aside class="record-pdf-sidebar" aria-label="Preventive maintenance summary and actions">
            <section class="record-pdf-card">
                <div class="record-pdf-heading">
                    <div><div class="record-pdf-id">{{ $checklist->control_number }}</div><div class="record-pdf-owner">{{ $checklist->system_owner }}</div><div class="record-pdf-meta">{{ $checklist->office_department ?: 'No office provided' }}</div></div>
                    <span class="status-badge {{ $statusClass }}">{{ str($checklist->status)->replace('_', ' ')->title() }}</span>
                </div>
                <div class="record-pdf-meta">{{ $checklist->user?->full_name ? 'Client record: '.$checklist->user->full_name : 'Standalone maintenance record' }}</div>
            </section>

            <section class="record-pdf-card">
                <div class="record-pdf-heading"><div><h2>Checklist Summary</h2><p>Important details at a glance.</p></div></div>
                <dl class="record-pdf-facts">
                    <div class="record-pdf-fact"><dt>Inspection date</dt><dd>{{ optional($checklist->date_inspected)->format('M d, Y') ?: '-' }}</dd></div>
                    <div class="record-pdf-fact"><dt>ICT type</dt><dd>{{ str($checklist->ict_type ?: '-')->replace('_', ' ')->title() }}</dd></div>
                    <div class="record-pdf-fact"><dt>Property no.</dt><dd>{{ $checklist->property_number ?: '-' }}</dd></div>
                    @if($checklist->serviceRequest)<div class="record-pdf-fact"><dt>Service request</dt><dd><a href="{{ route('admin.service-requests.show', $checklist->serviceRequest) }}">{{ $checklist->serviceRequest->control_number }}</a></dd></div>@endif
                </dl>
            </section>

            <section class="record-pdf-card">
                <div class="record-pdf-heading"><div><h3>Selected Checks</h3><p>{{ $selectedChecks->count() }} checklist {{ \Illuminate\Support\Str::plural('item', $selectedChecks->count()) }} recorded.</p></div></div>
                <div class="record-pdf-tags">
                    @forelse($selectedChecks->take(8) as $label)<span class="record-pdf-tag">{{ $label }}</span>@empty<span class="record-pdf-meta">No checklist items selected.</span>@endforelse
                    @if($selectedChecks->count() > 8)<span class="record-pdf-tag">+{{ $selectedChecks->count() - 8 }} more</span>@endif
                </div>
            </section>

            <section class="record-pdf-card">
                <div class="record-pdf-heading"><div><h3>Record Actions</h3><p>Edit the checklist or archive this record.</p></div></div>
                <div class="record-pdf-actions">
                    <a href="{{ route('admin.pm-checklists.edit', $checklist) }}" class="btn-primary">Edit Checklist</a>
                    <form method="POST" action="{{ route('admin.pm-checklists.destroy', $checklist) }}" onsubmit="return confirm('Archive this preventive maintenance checklist?');">@csrf @method('DELETE')<input type="hidden" name="archive_reason" value="Archived from the maintenance review by an administrator."><button type="submit" class="btn-danger">Archive Record</button></form>
                </div>
            </section>
        </aside>

        <section class="record-pdf-preview" aria-labelledby="maintenance-pdf-title">
            <header class="record-pdf-preview-head"><strong id="maintenance-pdf-title">Official Preventive Maintenance Checklist</strong><div class="record-pdf-preview-tools"><a href="{{ $pdfUrl }}" target="_blank" rel="noopener" data-pdf-action>Open PDF</a></div></header>
            <div class="record-pdf-loading" role="status">Preparing document preview...</div>
            <iframe class="record-pdf-frame" src="{{ $pdfUrl }}#toolbar=1&navpanes=0&view=FitH" title="Preventive maintenance checklist PDF preview"></iframe>
            <div class="record-pdf-fallback">If the preview is unavailable, <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" data-pdf-action>open the PDF in a new tab</a>.</div>
        </section>
    </div>
@endsection
