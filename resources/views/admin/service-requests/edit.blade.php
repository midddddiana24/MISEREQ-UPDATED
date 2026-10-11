@extends('admin.layout')

@section('title', 'Edit Service Request')
@section('page-title', 'Edit Service Request')
@section('page-subtitle', 'Correct the official request details without changing its workflow or assignment.')

@section('header-actions')
    <a href="{{ route('admin.service-requests.show', $serviceRequest) }}" class="btn-ghost">Cancel</a>
    <button type="submit" form="admin-edit-request" class="btn-primary">Save Changes</button>
@endsection

@section('head')
    <style>
        .request-edit-sheet { width: 100%; max-width: 1380px; margin: 0 auto; overflow: hidden; }
        .request-edit-head { display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 14px 18px; border-bottom: 1px solid var(--line); background: #f8fafb; }
        .request-edit-head img { width: 56px; height: 56px; object-fit: contain; }
        .request-edit-title { text-align: center; }
        .request-edit-title strong { display: block; color: var(--ink-900); font-size: 17px; letter-spacing: .035em; }
        .request-edit-title span { display: block; margin-top: 3px; color: var(--ink-500); font-size: 12px; }
        .request-edit-code { padding: 7px 9px; border: 1px solid var(--line); border-radius: 7px; color: var(--ink-700); font-size: 11px; line-height: 1.45; text-align: right; }
        .request-edit-body { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(310px, .75fr); gap: 12px; padding: 14px; }
        .request-edit-requester { grid-column: 1 / -1; }
        .request-edit-details { display: grid; align-content: start; gap: 12px; }
        .request-edit-section { overflow: hidden; border: 1px solid var(--line); border-radius: 9px; background: #fff; }
        .request-edit-section h2 { margin: 0; padding: 8px 11px; border-bottom: 1px solid var(--line); background: #f4f7f9; color: var(--ink-900); font-size: 12px; font-weight: 750; letter-spacing: .05em; text-transform: uppercase; }
        .request-edit-fields { padding: 11px; }
        .request-edit-fields .field { gap: 4px; }
        .request-edit-fields .field label { font-size: 12px; }
        .request-edit-fields input, .request-edit-fields textarea { min-height: 39px; padding: 8px 9px; font-size: 13px; }
        .request-edit-fields textarea { min-height: 120px; resize: vertical; }
        .service-location-fields { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(150px, .65fr); gap: 12px; padding: 12px; }
        .service-location-fields .field { min-width: 0; gap: 5px; }
        .service-location-fields label { font-size: 12px; line-height: 1.35; }
        .service-location-fields input { width: 100%; min-width: 0; min-height: 40px; padding: 8px 10px; font-size: 13px; }
        .service-choice-groups { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; padding: 11px; }
        .service-choice-group { min-width: 0; padding: 9px; border: 1px solid var(--line); border-radius: 8px; background: #fbfcfd; }
        .service-choice-group legend { max-width: 100%; padding: 0 3px; color: var(--ink-800); font-size: 11px; font-weight: 750; line-height: 1.35; }
        .service-choice { display: grid; grid-template-columns: 17px minmax(0, 1fr); align-items: start; gap: 7px; margin-top: 6px; color: var(--ink-700); font-size: 11px; line-height: 1.35; cursor: pointer; }
        .service-choice input { width: 16px; height: 16px; min-height: 0; margin: 0; accent-color: var(--brand-700); }
        .request-edit-note { margin: 0; padding: 0 14px 14px; color: var(--ink-500); font-size: 11px; line-height: 1.45; }
        .request-edit-footer { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 12px; border: 1px solid var(--line); border-radius: 9px; background: #f8fafb; }
        .request-edit-footer .request-edit-note { padding: 0; }
        .request-edit-footer-actions { display: flex; flex: 0 0 auto; gap: 7px; }
        @media (max-width: 1180px) {
            .service-location-fields { grid-template-columns: 1fr; }
        }
        @media (max-width: 860px) {
            .request-edit-body { grid-template-columns: 1fr; }
            .request-edit-requester, .request-edit-footer { grid-column: 1; }
            .request-edit-fields textarea { min-height: 150px; }
        }
        @media (max-width: 680px) {
            .request-edit-head { grid-template-columns: 48px minmax(0, 1fr); padding: 12px; }
            .request-edit-head img { width: 44px; height: 44px; }
            .request-edit-title { text-align: left; }
            .request-edit-title strong { font-size: 14px; }
            .request-edit-code { grid-column: 1 / -1; text-align: left; }
            .request-edit-body { padding: 10px; }
            .service-choice-groups { grid-template-columns: 1fr; }
            .request-edit-footer { align-items: stretch; flex-direction: column; }
            .request-edit-footer-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        }
    </style>
@endsection

@section('content')
    @php
        $selectedServices = collect(old('services', $serviceRequest->services ?? []))->map(fn ($service) => (string) $service)->all();
        $formLogo = asset('images/wvsu-logo.png');
        $requestDate = old('date_of_request', $serviceRequest->date_of_request?->format('Y-m-d'));
        $requestTimeValue = $serviceRequest->time_of_request;
        $requestTime = old('time_of_request', $requestTimeValue ? substr((string) $requestTimeValue, 0, 5) : '');
    @endphp

    <section class="panel request-edit-sheet">
        <header class="request-edit-head">
            <img src="{{ $formLogo }}" alt="West Visayas State University logo">
            <div class="request-edit-title"><strong>MIS SERVICE REQUEST FORM</strong><span>West Visayas State University</span></div>
            <div class="request-edit-code"><strong>{{ $serviceRequest->control_number }}</strong><br>{{ $serviceRequest->status_label }}</div>
        </header>

        <form id="admin-edit-request" action="{{ route('admin.service-requests.update', $serviceRequest) }}" method="POST" class="request-edit-body">
            @csrf
            @method('PUT')

            <section class="request-edit-section request-edit-requester" aria-labelledby="requester-section-title">
                <h2 id="requester-section-title">Requester Information</h2>
                <div class="form-grid request-edit-fields">
                    <div class="field form-col-3"><label for="requester_name">Requester name</label><input id="requester_name" name="requester_name" value="{{ old('requester_name', $serviceRequest->requester_name) }}" required>@error('requester_name')<span class="error-text">{{ $message }}</span>@enderror</div>
                    <div class="field form-col-3"><label for="requester_designation">Designation</label><input id="requester_designation" name="requester_designation" value="{{ old('requester_designation', $serviceRequest->requester_designation) }}"></div>
                    <div class="field form-col-3"><label for="requester_office">Office / Unit / Department</label><input id="requester_office" name="requester_office" value="{{ old('requester_office', $serviceRequest->requester_office) }}"></div>
                    <div class="field form-col-3"><label for="unit_head_name">Unit / Department head</label><input id="unit_head_name" name="unit_head_name" value="{{ old('unit_head_name', $serviceRequest->unit_head_name) }}"></div>
                    <div class="field form-col-4"><label for="phone">Phone / Local</label><input id="phone" name="phone" value="{{ old('phone', $serviceRequest->phone) }}"></div>
                    <div class="field form-col-4"><label for="date_of_request">Date of request</label><input id="date_of_request" type="date" name="date_of_request" value="{{ $requestDate }}"></div>
                    <div class="field form-col-4"><label for="time_of_request">Time of request</label><input id="time_of_request" type="time" name="time_of_request" value="{{ $requestTime }}"></div>
                </div>
            </section>

            <section class="request-edit-section" aria-labelledby="service-section-title">
                <h2 id="service-section-title">Requested MIS Services</h2>
                <div class="service-choice-groups">
                    @foreach($serviceOptions as $group => $options)
                        <fieldset class="service-choice-group">
                            <legend>{{ $group }}</legend>
                            @foreach($options as $value => $label)
                                <label class="service-choice"><input type="checkbox" name="services[]" value="{{ $value }}" @checked(in_array($value, $selectedServices, true))><span>{{ $label }}</span></label>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
                @error('services')<p class="error-text request-edit-note">{{ $message }}</p>@enderror
            </section>

            <div class="request-edit-details">
                <section class="request-edit-section" aria-labelledby="location-section-title">
                    <h2 id="location-section-title">Service Location</h2>
                    <div class="service-location-fields">
                        <div class="field"><label for="office">Building, office, or room</label><input id="office" name="office" value="{{ old('office', $serviceRequest->office) }}"></div>
                        <div class="field"><label for="property_no">Property / Asset number</label><input id="property_no" name="property_no" value="{{ old('property_no', $serviceRequest->property_no ?? $serviceRequest->ict_property_no) }}"></div>
                    </div>
                </section>

                <section class="request-edit-section" aria-labelledby="description-section-title">
                    <h2 id="description-section-title">Description of Request</h2>
                    <div class="request-edit-fields">
                        <div class="field"><label for="description">Additional details <span style="color:var(--ink-500);font-weight:400;">(optional)</span></label><textarea id="description" name="description" placeholder="Add helpful details only if needed.">{{ old('description', $serviceRequest->description) }}</textarea>@error('description')<span class="error-text">{{ $message }}</span>@enderror</div>
                    </div>
                </section>
            </div>

            <footer class="request-edit-footer">
                <p class="request-edit-note">Saving refreshes the official PDF. Assignment, status, ownership, and control number stay unchanged.</p>
                <div class="request-edit-footer-actions"><a href="{{ route('admin.service-requests.show', $serviceRequest) }}" class="btn-ghost">Cancel</a><button type="submit" class="btn-primary">Save Changes</button></div>
            </footer>
        </form>
    </section>
@endsection
