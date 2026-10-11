@extends('admin.layout')

@section('title', 'Request Review')
@section('page-title', 'Request Review')
@section('page-subtitle', 'Review the request details, current status, and MIS actions.')
@section('body-class', 'service-review-page')

@section('header-actions')
<a href="{{ route('admin.service-requests.edit', $serviceRequest) }}" class="btn-primary">Edit Request</a>
<a href="{{ route('admin.requests.index') }}" class="btn-ghost">Back to Requests</a>
@endsection

@section('head')
    @include('admin.partials.record-review-styles')
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

<div class="review-workspace">
    <main class="review-main">
        <section class="review-card review-hero" aria-labelledby="request-review-title">
            <div class="review-hero-top">
                <div>
                    <span class="review-kicker">{{ $serviceRequest->control_number }}</span>
                    <h2 id="request-review-title">{{ $serviceRequest->requester_name ?? $serviceRequest->user?->full_name ?? 'Unknown requester' }}</h2>
                    <p>{{ $serviceRequest->requester_office ?? $serviceRequest->office ?? 'No office provided' }}@if($serviceRequest->is_admin_created && !$serviceRequest->user_id) &middot; Walk-in record @endif</p>
                </div>
                <div class="review-status">
                    <small>Current status</small>
                    <span class="status-badge {{ $serviceRequest->admin_stage }}">{{ str($serviceRequest->admin_stage)->title() }}</span>
                    @if($serviceRequest->status !== $serviceRequest->admin_stage)<p>Workflow: {{ $serviceRequest->status_label }}</p>@endif
                </div>
            </div>
        </section>

        <section class="review-card" aria-labelledby="request-details-title">
            <div class="review-card-head"><div><h3 id="request-details-title">Request Details</h3><p>Requester, location, and assignment information.</p></div></div>
            <dl class="review-facts">
                <div><dt>Date requested</dt><dd>{{ $serviceRequest->date_of_request_formatted }}</dd></div>
                <div><dt>Time requested</dt><dd>{{ $serviceRequest->time_of_request ? substr((string) $serviceRequest->time_of_request, 0, 5) : 'Not recorded' }}</dd></div>
                <div><dt>Service location</dt><dd>{{ $serviceRequest->office_location ?: ($serviceRequest->office ?: 'Not recorded') }}</dd></div>
                <div><dt>Property / asset no.</dt><dd>{{ $serviceRequest->property_no ?: ($serviceRequest->ict_property_no ?: 'Not recorded') }}</dd></div>
                <div><dt>Contact</dt><dd>{{ $serviceRequest->phone ?: 'Not recorded' }}</dd></div>
                <div><dt>Assigned technician</dt><dd>{{ $serviceRequest->assignedTechnician?->full_name ?: 'Unassigned' }}</dd></div>
            </dl>
        </section>

        <section class="review-card" aria-labelledby="request-services-title">
            <div class="review-card-head"><h3 id="request-services-title">Requested MIS Services</h3></div>
            <div class="review-tags">@forelse(collect($serviceRequest->services ?? [])->filter() as $service)<span class="review-tag">{{ str($service)->replace('_', ' ')->title() }}</span>@empty<p class="review-empty">No service options recorded.</p>@endforelse</div>
        </section>

        <section class="review-card" aria-labelledby="request-description-title">
            <div class="review-card-head"><h3 id="request-description-title">Description and Resolution</h3></div>
            <dl class="review-facts">
                <div><dt>Request details</dt><dd>{{ $serviceRequest->description ?: 'No additional details recorded.' }}</dd></div>
                <div><dt>Resolution notes</dt><dd>{{ $serviceRequest->comments ?: 'No resolution recorded yet.' }}</dd></div>
            </dl>
        </section>

    </main>

    <aside class="review-sidebar" aria-label="MIS request controls">
        <section class="review-card" aria-labelledby="request-pdf-title">
            <div class="review-card-head"><h3 id="request-pdf-title">Official PDF</h3></div>
            <p class="review-empty">Open the complete service request form in a new tab when you need to check or print it.</p>
            <div class="review-links" style="margin-top:12px;"><a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn-ghost">Open PDF</a><a href="{{ route('admin.service-requests.download-pdf', $serviceRequest) }}" class="btn-ghost">Download</a></div>
        </section>

        @if($serviceRequest->office_location || $serviceRequest->network_others || $serviceRequest->ict_others || $serviceRequest->mis_others)
        <section class="review-card">
            <div class="review-card-head"><h2>Requester service details</h2></div>
            @if($serviceRequest->office_location)<p><strong>Location:</strong> {{ $serviceRequest->office_location }}</p>@endif
            @if($serviceRequest->network_others)<p><strong>Other network service:</strong> {{ $serviceRequest->network_others }}</p>@endif
            @if($serviceRequest->ict_others)<p><strong>Other ICT equipment service:</strong> {{ $serviceRequest->ict_others }}</p>@endif
            @if($serviceRequest->mis_others)<p><strong>Other MIS software service:</strong> {{ $serviceRequest->mis_others }}</p>@endif
        </section>
        @endif

        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h2>Related MIS Record</h2>
                    <p>Start the appropriate official record with requester details already filled in.</p>
                </div>
            </div>
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
        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h2>Connected Records</h2>
                    <p>Open the incident and maintenance records created from this request.</p>
                </div>
            </div>
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
        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h2>{{ $needsTriage ? 'Review and Assign' : 'MIS Assignment' }}</h2>
                    <p>Record only the information needed to process this request.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.service-requests.triage', $serviceRequest) }}" class="pdf-control-form">@csrf
                <input type="hidden" name="version" value="{{ $serviceRequest->version ?? 0 }}">
                <div class="field"><label for="assigned_technician_id">Technician</label><select id="assigned_technician_id" name="assigned_technician_id" @required($needsTriage)>
                        <option value="">Unassigned</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) old('assigned_technician_id', $serviceRequest->assigned_technician_id) === (string) $technician->id)>{{ $technician->full_name }} - {{ $technician->active_assignments_count }} active</option>@endforeach
                    </select></div>
                <div class="field"><label for="technician_notes">Work instructions</label><textarea id="technician_notes" name="technician_notes">{{ old('technician_notes', $serviceRequest->technician_notes) }}</textarea></div>
                @if($canStartProcessing)<input type="hidden" name="start_processing" value="1">@endif
                <div class="form-actions"><button class="btn-primary">{{ $assignmentButton }}</button></div>
            </form>
        </section>
        @endif

        @if($canFinishWork)
        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h3>Complete Work</h3>
                    <p>Add the resolution that will appear in the service record.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.service-requests.mark-completed', $serviceRequest) }}" class="pdf-control-form">@csrf<input type="hidden" name="version" value="{{ $serviceRequest->version ?? 0 }}">
                <div class="field"><label for="comments">Resolution notes</label><textarea id="comments" name="comments" required>{{ old('comments', $serviceRequest->comments) }}</textarea></div>
                <div class="form-actions"><button class="btn-primary">Complete Request</button></div>
            </form>
        </section>
        @elseif(in_array($serviceRequest->status, ['reopened', 'escalated'], true) && $serviceRequest->assigned_technician_id)
        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h3>Resume Work First</h3>
                    <p>Use “Resume Work” above to return this request to In Progress before recording completion.</p>
                </div>
            </div>
        </section>
        @elseif(in_array($serviceRequest->status, ['awaiting_confirmation', 'completed'], true))
        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h3>Final Confirmation</h3>
                    <p>Close the request after the completed work is confirmed.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.service-requests.close', $serviceRequest) }}">@csrf<input type="hidden" name="version" value="{{ $serviceRequest->version ?? 0 }}"><input type="hidden" name="reason" value="Resolution confirmation recorded by administrator."><button class="btn-primary">Confirm and Close</button></form>
        </section>
        @elseif(!in_array($serviceRequest->status, \App\Models\ServiceRequest::OPEN_STATUSES, true))
        <section class="review-card">
            <div class="review-card-head">
                <div>
                    <h3>{{ $displayStatus }}</h3>
                    <p>This request does not require another MIS action.</p>
                </div>
            </div>
            <p class="pdf-status-note">Use Open PDF for the complete official record.</p>
        </section>
        @endif
    </aside>

</div>
@endsection
