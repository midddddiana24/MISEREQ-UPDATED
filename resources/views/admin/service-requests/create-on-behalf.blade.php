@extends('admin.layout')

@section('title', 'Walk-in Request')
@section('page-title', 'Add Walk-in Request')
@section('page-subtitle', 'Record a request received in person or by telephone.')

@section('header-actions')
    <a href="{{ route('admin.requests.index') }}" class="btn-ghost">Back to Requests</a>
@endsection

@section('head')
    <style>
        .walkin-sheet { max-width: 1380px; margin: 0 auto; padding: 0; overflow: hidden; }
        .walkin-document-head { display: grid; grid-template-columns: 72px 1fr auto; gap: 16px; align-items: center; padding: 18px 20px; border-bottom: 1px solid var(--line); background: #fbfcfd; }
        .walkin-document-head img { width: 64px; height: 64px; object-fit: contain; }
        .walkin-document-title { text-align: center; }
        .walkin-document-title strong { display: block; color: var(--ink-900); font-size: 19px; letter-spacing: .03em; }
        .walkin-document-title span { display: block; margin-top: 4px; color: var(--ink-500); font-size: 12px; }
        .walkin-form-code { padding: 8px 10px; border: 1px solid var(--line); border-radius: 7px; color: var(--ink-700); font-size: 11px; line-height: 1.45; text-align: right; }
        .walkin-body { padding: 18px 20px 20px; }
        .walkin-section { border: 1px solid var(--line); border-radius: 9px; overflow: hidden; margin-bottom: 14px; }
        .walkin-section-title { padding: 9px 12px; border-bottom: 1px solid var(--line); background: #f4f7f9; color: var(--ink-900); font-size: 12px; font-weight: 750; letter-spacing: .06em; text-transform: uppercase; }
        .walkin-fields { padding: 13px; }
        .walkin-fields .field textarea { min-height: 105px; }
        .walkin-fields #description { min-height: 76px; }
        .walkin-fields .form-col-2 { grid-column: span 2; }
        .walkin-service-groups { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 9px; padding: 13px; }
        .walkin-service-group { min-width: 0; padding: 9px; border: 1px solid var(--line); border-radius: 8px; background: #fbfcfd; }
        .walkin-service-group legend { max-width: 100%; padding: 0 3px; color: var(--ink-800); font-size: 11px; font-weight: 750; line-height: 1.35; }
        .walkin-service-choice { display: grid; grid-template-columns: 17px minmax(0, 1fr); align-items: start; gap: 7px; margin-top: 7px; color: var(--ink-700); font-size: 11px; line-height: 1.35; cursor: pointer; }
        .walkin-service-choice input { width: 16px; height: 16px; min-height: 0; margin: 0; accent-color: var(--brand-700); }
        .walkin-upload-note { margin-top: 5px; color: var(--ink-500); font-size: 11px; line-height: 1.4; }
        .walkin-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 2px; }
        .walkin-note { color: var(--ink-500); font-size: 12px; }
        @media (max-width: 1180px) { .walkin-service-groups { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 760px) {
            .walkin-document-head { grid-template-columns: 50px 1fr; padding: 14px; }
            .walkin-document-head img { width: 48px; height: 48px; }
            .walkin-document-title { text-align: left; }
            .walkin-document-title strong { font-size: 15px; }
            .walkin-form-code { grid-column: 1 / -1; text-align: left; }
            .walkin-body { padding: 12px; }
            .walkin-fields .form-col-2 { grid-column: span 12; }
            .walkin-service-groups { grid-template-columns: 1fr; }
            .walkin-footer { align-items: stretch; flex-direction: column; }
            .walkin-footer .form-actions { display: grid; grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 360px) { .walkin-footer .form-actions { grid-template-columns: 1fr; } }
    </style>
@endsection

@section('content')
    @php
        $formLogo = asset('images/wvsu-logo.png');
        $selectedServices = collect(old('services', []))->map(fn ($service) => (string) $service)->all();
    @endphp
    <section class="panel walkin-sheet">
        <header class="walkin-document-head">
            <img src="{{ $formLogo }}" alt="West Visayas State University logo">
            <div class="walkin-document-title"><strong>MIS SERVICE REQUEST FORM</strong><span>West Visayas State University</span></div>
            <div class="walkin-form-code"><strong>ACCOUNT-FREE WALK-IN</strong><br>{{ now()->format('M d, Y') }}</div>
        </header>

        <form action="{{ route('admin.service-requests.store-on-behalf') }}" method="POST" enctype="multipart/form-data" class="walkin-body">
            @csrf
            <section class="walkin-section" aria-labelledby="requester-information-title">
                <div class="walkin-section-title" id="requester-information-title">Requester Information</div>
                <div class="form-grid walkin-fields">
                    <div class="field form-col-6"><label for="requester_name">Requester name</label><input id="requester_name" name="requester_name" value="{{ old('requester_name') }}" required>@error('requester_name')<span class="error-text">{{ $message }}</span>@enderror</div>
                    <div class="field form-col-6"><label for="requester_office">Office / Unit / Department</label><input id="requester_office" name="requester_office" value="{{ old('requester_office') }}"></div>
                    <div class="field form-col-3"><label for="requester_designation">Designation</label><input id="requester_designation" name="requester_designation" value="{{ old('requester_designation') }}"></div>
                    <div class="field form-col-6"><label for="unit_head_name">Unit / Department head</label><input id="unit_head_name" name="unit_head_name" value="{{ old('unit_head_name') }}"></div>
                    <div class="field form-col-3"><label for="phone">Phone / Local</label><input id="phone" name="phone" value="{{ old('phone') }}"></div>
                </div>
            </section>

            <section class="walkin-section" aria-labelledby="service-information-title">
                <div class="walkin-section-title" id="service-information-title">Service Information</div>
                <div class="form-grid walkin-fields">
                    <div class="field form-col-4"><label for="office">Service location</label><input id="office" name="office" value="{{ old('office') }}" placeholder="Building, office, or room"></div>
                    <div class="field form-col-4"><label for="property_no">Property / Asset number</label><input id="property_no" name="property_no" value="{{ old('property_no') }}" placeholder="If applicable"></div>
                    <div class="field form-col-2"><label for="date_of_request">Date of request</label><input id="date_of_request" type="date" name="date_of_request" value="{{ old('date_of_request', now()->format('Y-m-d')) }}"></div>
                    <div class="field form-col-2"><label for="time_of_request">Time of request</label><input id="time_of_request" type="time" name="time_of_request" value="{{ old('time_of_request', now()->format('H:i')) }}"></div>
                </div>
            </section>

            <section class="walkin-section" aria-labelledby="requested-services-title">
                <div class="walkin-section-title" id="requested-services-title">Services Requested</div>
                <div class="walkin-service-groups">
                    @foreach($serviceOptions as $group => $options)
                        <fieldset class="walkin-service-group">
                            <legend>{{ $group }}</legend>
                            @foreach($options as $value => $label)
                                <label class="walkin-service-choice"><input type="checkbox" name="services[]" value="{{ $value }}" @checked(in_array($value, $selectedServices, true))><span>{{ $label }}</span></label>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
                @error('services')<p class="error-text" style="padding:0 13px 12px;">{{ $message }}</p>@enderror
            </section>

            <section class="walkin-section" aria-labelledby="concern-details-title">
                <div class="walkin-section-title" id="concern-details-title">Concern Details</div>
                <div class="form-grid walkin-fields">
                    <div class="field form-col-8"><label for="description">Description of request <span style="color:var(--ink-500);font-weight:400;">(optional)</span></label><textarea id="description" name="description" placeholder="Add helpful details only if needed.">{{ old('description') }}</textarea>@error('description')<span class="error-text">{{ $message }}</span>@enderror</div>
                    <div class="field form-col-4"><label for="attachments">Attachments (optional)</label><input id="attachments" type="file" name="attachments[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple><span class="walkin-upload-note">Up to 5 PDF, DOC, DOCX, JPG, or PNG files. Maximum {{ min(20, (int) \App\Models\Setting::get('max_upload_size', 10)) }} MB each.</span>@error('attachments.*')<span class="error-text">{{ $message }}</span>@enderror</div>
                </div>
            </section>

            <footer class="walkin-footer">
                <p class="walkin-note">A control number will be generated. This record will be managed by MIS and will not appear in a client account.</p>
                <div class="form-actions"><a href="{{ route('admin.requests.index') }}" class="btn-ghost">Cancel</a><button type="submit" class="btn-primary">Save Request</button></div>
            </footer>
        </form>
    </section>
@endsection
