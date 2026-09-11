@extends('admin.layout')

@section('title', 'Request Review')
@section('page-title', 'Request Review')
@section('page-subtitle', 'Preview the official service request form and record essential MIS actions.')
@section('body-class', 'service-review-page')

@section('header-actions')
    <a href="{{ route('admin.service-requests.edit', $serviceRequest) }}" class="btn-primary">Edit Request</a>
    <a href="{{ route('admin.requests.index') }}" class="btn-ghost">Back to Requests</a>
@endsection

@section('head')
    <style>
        .service-review-page .content-grid { width: 100%; max-width: 1720px; }
        .pdf-review-workspace { display: grid; grid-template-columns: minmax(0, 1fr) minmax(260px, 300px); gap: 12px; align-items: start; }
        .pdf-review-controls { position: sticky; top: 82px; grid-column: 2; grid-row: 1; display: grid; gap: 10px; }
        .pdf-control-card { padding: 14px; border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: var(--shadow); }
        .pdf-control-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 9px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid var(--line); }
        .pdf-control-heading h2, .pdf-control-heading h3 { margin: 0; color: var(--ink-900); font-size: 15px; }
        .pdf-control-heading p { margin: 3px 0 0; color: var(--ink-500); font-size: 11px; line-height: 1.4; }
        .pdf-request-id { display: block; color: var(--ink-500); font-size: 10px; font-weight: 750; letter-spacing: .04em; text-transform: uppercase; }
        .pdf-requester { margin: 5px 0 0; color: var(--ink-900); font-size: 14px; font-weight: 750; line-height: 1.35; }
        .pdf-request-meta { margin-top: 3px; color: var(--ink-500); font-size: 11px; line-height: 1.4; }
        .pdf-control-form { display: grid; gap: 9px; }
        .pdf-control-form .field { gap: 4px; }
        .pdf-control-form .field label { font-size: 12px; }
        .pdf-control-form .field input, .pdf-control-form .field select, .pdf-control-form .field textarea { min-height: 38px; padding: 7px 9px; font-size: 13px; }
        .pdf-control-form .field textarea { min-height: 58px; }
        .pdf-control-form .form-actions { margin-top: 0; }
        .pdf-status-note { margin: 8px 0 0; color: var(--ink-500); font-size: 11px; line-height: 1.45; }
        .pdf-related-list { display: grid; gap: 7px; }
        .pdf-related-link { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 9px; border: 1px solid var(--line); border-radius: 7px; color: var(--ink-700); font-size: 11px; font-weight: 700; text-decoration: none; background: var(--surface-subtle, #f8fafc); }
        .pdf-related-link:hover { border-color: var(--brand-700); color: var(--brand-900); }
        .pdf-related-link span:last-child { color: var(--ink-500); font-size: 10px; font-weight: 650; }
        .pdf-workflow-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
        .pdf-workflow-actions a { display: grid; place-items: center; min-height: 38px; padding: 7px 9px; border: 1px solid var(--line); border-radius: 7px; color: var(--ink-700); font-size: 11px; font-weight: 750; text-align: center; text-decoration: none; background: #fff; }
        .pdf-workflow-actions a:hover { border-color: var(--brand-700); color: var(--brand-900); background: #f5f9ff; }
        .pdf-preview-card { position: relative; grid-column: 1; grid-row: 1; min-width: 0; overflow: hidden; border: 1px solid #b8c2cc; border-radius: 10px; background: #525b65; box-shadow: 0 10px 26px rgba(15, 23, 42, .12); }
        .pdf-preview-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 42px; padding: 7px 11px; border-bottom: 1px solid var(--line); background: #fff; }
        .pdf-preview-head strong { color: var(--ink-900); font-size: 13px; }
        .pdf-preview-head span { color: var(--ink-500); font-size: 11px; }
        .pdf-preview-tools { display: flex; align-items: center; gap: 6px; }
        .pdf-preview-tools a { padding: 5px 8px; border: 1px solid var(--line); border-radius: 6px; color: var(--ink-700); font-size: 11px; font-weight: 700; text-decoration: none; background: #fff; }
        .pdf-preview-tools a:hover { border-color: var(--brand-700); color: var(--brand-900); }
        .pdf-preview-frame { display: block; width: 100%; height: calc(100vh - 178px); min-height: 700px; border: 0; background: #525b65; opacity: .18; transition: opacity .18s ease; }
        .pdf-preview-frame.is-ready { opacity: 1; }
        .pdf-preview-loading { position: absolute; z-index: 2; top: 58px; left: 50%; transform: translateX(-50%); padding: 8px 11px; border-radius: 7px; color: var(--ink-700); background: rgba(255,255,255,.96); box-shadow: 0 4px 16px rgba(15,23,42,.18); font-size: 11px; font-weight: 700; pointer-events: none; }
        .pdf-preview-loading[hidden] { display: none; }
        .pdf-preview-fallback { padding: 12px; color: var(--ink-700); font-size: 13px; text-align: center; background: #fff; }
        .pdf-preview-fallback a { color: var(--brand-900); font-weight: 750; text-decoration: underline; }
        @media (max-width: 1050px) {
            .pdf-review-workspace { grid-template-columns: 1fr; }
            .pdf-review-controls { position: static; grid-column: 1; grid-row: 1; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pdf-review-controls > :first-child { grid-column: 1 / -1; }
            .pdf-preview-card { grid-column: 1; grid-row: 2; }
            .pdf-preview-frame { height: 76vh; min-height: 620px; }
        }
        @media (max-width: 700px) {
            .pdf-review-controls { grid-template-columns: 1fr; }
            .pdf-review-controls > :first-child { grid-column: auto; }
            .pdf-preview-head { align-items: flex-start; flex-direction: column; }
            .pdf-preview-frame { height: 72vh; min-height: 520px; }
        }
        @media (max-width: 390px) { .pdf-preview-frame { min-height: 440px; } }
    </style>
@endsection

@section('content')
    @php
        $needsTriage = $serviceRequest->status !== 'awaiting_requester_info' && (
            in_array($serviceRequest->status, ['submitted', 'pending'], true)
            || (in_array($serviceRequest->status, \App\Models\ServiceRequest::OPEN_STATUSES, true) && !$serviceRequest->assigned_technician_id)
        );
        $canFinishWork = $serviceRequest->status === 'processing' && $serviceRequest->assigned_technician_id;
        $canStartProcessing = in_array($serviceRequest->status, ['submitted', 'pending', 'reopened', 'escalated'], true);
        $assignmentButton = $needsTriage
            ? 'Assign and Start'
            : ($canStartProcessing ? 'Resume Work' : 'Save Assignment');
        $displayStatus = $serviceRequest->status === 'escalated' ? 'Needs Review' : $serviceRequest->status_label;
        $serviceType = collect($serviceRequest->services ?? [])->filter()->first();
        $pdfUrl = route('admin.service-requests.pdf', ['id' => $serviceRequest->id, 'v' => $serviceRequest->updated_at?->timestamp]);
    @endphp

    @if($serviceRequest->status === 'escalated')
        <div class="alert alert-error" role="alert"><strong>This request needs review.</strong> {{ $serviceRequest->escalation_reason }}</div>
    @endif

    <div class="pdf-review-workspace">
        <aside class="pdf-review-controls" aria-label="MIS request controls">
            <section class="pdf-control-card">
                <div class="pdf-control-heading">
                    <div><span class="pdf-request-id">{{ $serviceRequest->control_number }}</span><div class="pdf-requester">{{ $serviceRequest->requester_name ?? $serviceRequest->user?->full_name ?? 'Unknown requester' }}</div><div class="pdf-request-meta">{{ $serviceRequest->requester_office ?? $serviceRequest->office ?? 'No office provided' }}</div></div>
                    <span class="status-badge {{ $serviceRequest->status }}">{{ $displayStatus }}</span>
                </div>
                <div class="pdf-request-meta">{{ $serviceType ? str($serviceType)->replace('_', ' ')->title() : 'MIS service request' }} &middot; {{ $serviceRequest->assignedTechnician?->full_name ?? 'Unassigned' }}@if($serviceRequest->is_admin_created && !$serviceRequest->user_id) &middot; Walk-in record @endif</div>
            </section>

            <section class="pdf-control-card">
                <div class="pdf-control-heading"><div><h2>Related MIS Record</h2><p>Start the appropriate official record with requester details already filled in.</p></div></div>
                <div class="pdf-workflow-actions">
                    @if($serviceRequest->incidentReports->isNotEmpty())
                        <a href="{{ route('admin.incident-reports.show', $serviceRequest->incidentReports->first()) }}">Open Incident</a>
                    @else
                        <a href="{{ route('admin.incident-reports.create', ['service_request_id' => $serviceRequest->id]) }}">Create Incident</a>
                    @endif
                    @if($serviceRequest->preventiveMaintenanceChecklists->isNotEmpty())
                        <a href="{{ route('admin.pm-checklists.show', $serviceRequest->preventiveMaintenanceChecklists->first()) }}">Open Maintenance</a>
                    @else
                        <a href="{{ route('admin.pm-checklists.create', ['service_request_id' => $serviceRequest->id]) }}">Create Maintenance</a>
                    @endif
                </div>
                <p class="pdf-status-note">Incident reports and preventive-maintenance checklists are separate records and can be prepared independently.</p>
            </section>

            @if($serviceRequest->incidentReports->isNotEmpty() || $serviceRequest->preventiveMaintenanceChecklists->isNotEmpty())
                <section class="pdf-control-card">
                    <div class="pdf-control-heading"><div><h2>Connected Records</h2><p>Open the incident and maintenance records created from this request.</p></div></div>
                    <div class="pdf-related-list">
                        @foreach($serviceRequest->incidentReports as $incident)
                            <a class="pdf-related-link" href="{{ route('admin.incident-reports.show', $incident) }}"><span>{{ $incident->document_number }}</span><span>{{ str($incident->status)->replace('_', ' ')->title() }}</span></a>
                        @endforeach
                        @foreach($serviceRequest->preventiveMaintenanceChecklists as $checklist)
                            <a class="pdf-related-link" href="{{ route('admin.pm-checklists.show', $checklist) }}"><span>{{ $checklist->control_number }}</span><span>{{ str($checklist->status)->replace('_', ' ')->title() }}</span></a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(in_array($serviceRequest->status, \App\Models\ServiceRequest::OPEN_STATUSES, true))
                <section class="pdf-control-card">
                    <div class="pdf-control-heading"><div><h2>{{ $needsTriage ? 'Review and Assign' : 'MIS Assignment' }}</h2><p>Record only the information needed to process this request.</p></div></div>
                    <form method="POST" action="{{ route('admin.service-requests.triage', $serviceRequest) }}" class="pdf-control-form">@csrf
                        <div class="field"><label for="assigned_technician_id">Technician</label><select id="assigned_technician_id" name="assigned_technician_id" @required($needsTriage)><option value="">Unassigned</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) old('assigned_technician_id', $serviceRequest->assigned_technician_id) === (string) $technician->id)>{{ $technician->full_name }} - {{ $technician->active_assignments_count }} active</option>@endforeach</select></div>
                        <div class="field"><label for="technician_notes">Work instructions</label><textarea id="technician_notes" name="technician_notes">{{ old('technician_notes', $serviceRequest->technician_notes) }}</textarea></div>
                        @if($canStartProcessing)<input type="hidden" name="start_processing" value="1">@endif
                        <div class="form-actions"><button class="btn-primary">{{ $assignmentButton }}</button></div>
                    </form>
                </section>
            @endif

            @if($canFinishWork)
                <section class="pdf-control-card">
                    <div class="pdf-control-heading"><div><h3>Complete Work</h3><p>Add the resolution that will appear in the service record.</p></div></div>
                    <form method="POST" action="{{ route('admin.service-requests.mark-completed', $serviceRequest) }}" class="pdf-control-form">@csrf<div class="field"><label for="comments">Resolution notes</label><textarea id="comments" name="comments" required>{{ old('comments', $serviceRequest->comments) }}</textarea></div><div class="form-actions"><button class="btn-primary">Complete Request</button></div></form>
                </section>
            @elseif(in_array($serviceRequest->status, ['reopened', 'escalated'], true) && $serviceRequest->assigned_technician_id)
                <section class="pdf-control-card">
                    <div class="pdf-control-heading"><div><h3>Resume Work First</h3><p>Use “Resume Work” above to return this request to In Progress before recording completion.</p></div></div>
                </section>
            @elseif(in_array($serviceRequest->status, ['awaiting_confirmation', 'completed'], true))
                <section class="pdf-control-card">
                    <div class="pdf-control-heading"><div><h3>Final Confirmation</h3><p>Close the request after the completed work is confirmed.</p></div></div>
                    <form method="POST" action="{{ route('admin.service-requests.close', $serviceRequest) }}">@csrf<input type="hidden" name="reason" value="Resolution confirmation recorded by administrator."><button class="btn-primary">Confirm and Close</button></form>
                </section>
            @elseif(!in_array($serviceRequest->status, \App\Models\ServiceRequest::OPEN_STATUSES, true))
                <section class="pdf-control-card"><div class="pdf-control-heading"><div><h3>{{ $displayStatus }}</h3><p>This request does not require another MIS action.</p></div></div><p class="pdf-status-note">Use the PDF preview for the complete official record.</p></section>
            @endif
        </aside>

        <section class="pdf-preview-card" aria-labelledby="pdf-preview-title">
            <header class="pdf-preview-head">
                <strong id="pdf-preview-title">Official MIS Service Request Form</strong>
                <div class="pdf-preview-tools"><a href="{{ $pdfUrl }}" target="_blank" rel="noopener" data-pdf-action>Open PDF</a><a href="{{ route('admin.service-requests.download-pdf', $serviceRequest) }}" data-pdf-action>Download</a></div>
            </header>
            <div class="pdf-preview-loading" role="status">Preparing document preview...</div>
            <iframe class="pdf-preview-frame" id="servicePdfFrame" src="{{ $pdfUrl }}#toolbar=1&navpanes=0&view=FitH" title="Service request PDF preview"></iframe>
            <div class="pdf-preview-fallback">If the preview is unavailable, <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" data-pdf-action>open the PDF in a new tab</a>.</div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const frame = document.getElementById('servicePdfFrame');
            const loading = document.querySelector('.pdf-preview-loading');
            frame?.addEventListener('load', () => {
                frame.classList.add('is-ready');
                if (loading) loading.hidden = true;
            }, { once: true });
            document.querySelectorAll('[data-pdf-action]').forEach(link => {
                link.addEventListener('click', () => {
                    const original = link.textContent;
                    link.textContent = 'Preparing PDF...';
                    link.setAttribute('aria-busy', 'true');
                    window.setTimeout(() => {
                        link.textContent = original;
                        link.removeAttribute('aria-busy');
                    }, 4500);
                });
            });
        })();
    </script>
@endsection
