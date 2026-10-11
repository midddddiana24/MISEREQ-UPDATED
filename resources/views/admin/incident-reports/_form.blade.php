@php
    $report = $report ?? null;
    $prefillServiceRequest = $prefillServiceRequest ?? null;
    $prefillOwner = $prefillServiceRequest?->user;
    $selectedNature = old('nature_of_incident', $report?->nature_of_incident ?? []);
    $receivedByValue = old(
        'received_by',
        $report?->received_by ?: ($misStaffOptions->count() === 1 ? $misStaffOptions->first() : '')
    );
@endphp

<style>
    .incident-editor-panel { padding: 18px; }
    .incident-editor-panel > .section-heading {
        max-width: 1280px;
        margin: 0 auto 14px;
        padding: 0 2px 12px;
        border-bottom: 1px solid var(--line, #d8dee8);
    }
    .incident-editor-panel > .section-heading h2 { margin: 0; font-size: 20px; }
    .incident-editor-panel > .section-heading p { margin: 3px 0 0; }
    .incident-sheet {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        max-width: 1040px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .07);
    }
    .incident-sheet-header {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 72px minmax(240px, 1fr) minmax(230px, .8fr);
        align-items: stretch;
        border-bottom: 1px solid var(--line, #d8dee8);
    }
    .incident-sheet-logo {
        display: grid;
        place-items: center;
        padding: 10px;
        border-right: 1px solid var(--line, #d8dee8);
        background: #fafbfc;
    }
    .incident-sheet-logo img { width: 42px; height: 42px; object-fit: contain; }
    .incident-sheet-title { padding: 12px 16px; }
    .incident-sheet-title span {
        display: block;
        margin-bottom: 3px;
        color: var(--ink-500, #64748b);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .incident-sheet-title h3 { margin: 0; font-size: 18px; line-height: 1.25; }
    .incident-sheet-title p { margin: 5px 0 0; color: var(--ink-500, #64748b); font-size: 12px; }
    .incident-sheet-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-left: 1px solid var(--line, #d8dee8);
    }
    .incident-sheet-meta div { padding: 8px 10px; border-bottom: 1px solid var(--line, #d8dee8); }
    .incident-sheet-meta div:nth-last-child(-n+2) { border-bottom: 0; }
    .incident-sheet-meta strong, .incident-sheet-meta span { display: block; }
    .incident-sheet-meta strong { color: var(--ink-500, #64748b); font-size: 10px; text-transform: uppercase; }
    .incident-sheet-meta span { margin-top: 2px; font-size: 12px; font-weight: 700; }
    .incident-form-section { min-width: 0; padding: 15px 17px; border-bottom: 1px solid var(--line, #d8dee8); }
    .incident-form-section:last-child { border-bottom: 0; }
    .incident-reporter, .incident-mis-section { border-left: 1px solid var(--line, #d8dee8); }
    .incident-nature, .incident-signoff { grid-column: 1 / -1; }
    .incident-section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
    }
    .incident-section-heading h4 { margin: 0; font-size: 15px; line-height: 1.3; }
    .incident-section-heading p { margin: 4px 0 0; color: var(--ink-500, #64748b); font-size: 12px; line-height: 1.45; }
    .incident-section-tag {
        flex: none;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eef6ff;
        color: #1d4f91;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .incident-fields {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        column-gap: 16px;
        row-gap: 14px;
    }
    .incident-fields .field { min-width: 0; align-content: start; gap: 6px; }
    .incident-fields .field label { font-size: 13px; line-height: 1.35; }
    .incident-fields .span-3 { grid-column: span 3; }
    .incident-fields .span-4 { grid-column: span 4; }
    .incident-fields .span-6 { grid-column: span 6; }
    .incident-fields .span-8 { grid-column: span 8; }
    .incident-fields .span-12 { grid-column: 1 / -1; }
    .incident-workflow .span-3 { grid-column: span 6; }
    .incident-workflow { background: #f8fafc; }
    .incident-workflow .field label { color: #334155; font-weight: 750; }
    .incident-status-field select { border-color: #8db4df; background: #f5faff; font-weight: 750; }
    .incident-sheet input:not([type="checkbox"]),
    .incident-sheet select {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 42px;
        padding: 9px 11px;
        font-size: 14px;
    }
    .incident-sheet textarea { width: 100%; min-height: 76px; padding: 10px 11px; font-size: 14px; line-height: 1.5; resize: vertical; }
    .incident-sheet .incident-long-text { min-height: 105px; }
    .incident-nature-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
    .incident-nature-option {
        display: grid;
        grid-template-columns: 18px minmax(0, 1fr) 18px;
        align-items: center;
        gap: 9px;
        padding: 7px 9px;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 9px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        line-height: 1.35;
        cursor: pointer;
    }
    .incident-nature-option span { grid-column: 2; text-align: center; }
    .incident-nature-option:has(input:checked) { border-color: #77a6d7; background: #f2f8ff; }
    .incident-nature-option input { width: 16px; height: 16px; margin: 0; flex: none; }
    .incident-mis-section { background: #fcfcfb; }
    .incident-signature-field { padding-top: 2px; }
    .incident-signature-field input { border-width: 0 0 1px; border-radius: 0; background: transparent; text-align: center; font-weight: 700; }
    .incident-signature-field small { display: block; margin-top: 5px; color: var(--ink-500, #64748b); text-align: center; }
    .requester-position-field { grid-column: 9 / span 4; }
    .incident-form-actions {
        position: sticky;
        z-index: 8;
        bottom: 12px;
        grid-column: 1 / -1;
        max-width: 1040px;
        width: 100%;
        margin: 2px auto 0;
        padding: 12px;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 12px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .12);
        backdrop-filter: blur(8px);
    }
    .incident-edit-form {
        grid-template-columns: minmax(0, 1fr);
    }
    .incident-edit-form .incident-sheet,
    .incident-edit-form .incident-form-actions {
        width: 100%;
        max-width: 1280px;
    }
    @media (min-width: 1200px) {
        .incident-edit-form .incident-sheet {
            grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
        }
        .incident-edit-form .incident-form-section { padding: 14px 16px; }
        .incident-edit-form .incident-section-heading { margin-bottom: 10px; }
        .incident-edit-form .incident-fields {
            column-gap: 16px;
            row-gap: 14px;
        }
        .incident-edit-form .incident-nature-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .incident-edit-form .incident-nature-option {
            min-height: 38px;
            padding: 6px 8px;
        }
        .incident-edit-form .incident-sheet textarea {
            min-height: 68px;
        }
        .incident-edit-form .incident-sheet .incident-long-text {
            min-height: 88px;
        }
        .incident-edit-form .incident-signoff .span-4,
        .incident-edit-form .requester-position-field {
            grid-column: span 3;
        }
    }
    @media (max-width: 1120px) {
        .incident-sheet { display: block; }
        .incident-sheet-header { grid-template-columns: 64px 1fr; }
        .incident-sheet-meta { grid-column: 1 / -1; border-top: 1px solid var(--line, #d8dee8); border-left: 0; }
        .incident-reporter, .incident-mis-section { border-left: 0; }
        .incident-fields .span-3, .incident-fields .span-4 { grid-column: span 6; }
        .incident-fields .span-8 { grid-column: 1 / -1; }
        .incident-nature-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .requester-position-field { grid-column: span 6; }
    }
    @media (max-width: 600px) {
        .incident-editor-panel { padding: 10px; }
        .incident-editor-panel > .section-heading { margin-bottom: 10px; padding-bottom: 10px; }
        .incident-sheet { border-radius: 10px; }
        .incident-sheet-header { grid-template-columns: 56px 1fr; }
        .incident-sheet-logo { padding: 10px; }
        .incident-sheet-logo img { width: 38px; height: 38px; }
        .incident-sheet-title { padding: 12px; }
        .incident-sheet-title h3 { font-size: 15px; }
        .incident-sheet-meta { grid-template-columns: 1fr 1fr; }
        .incident-form-section { padding: 15px 13px; }
        .incident-section-heading { display: block; }
        .incident-section-tag { display: inline-block; margin-top: 8px; }
        .incident-fields .span-3, .incident-fields .span-4,
        .incident-fields .span-6, .incident-fields .span-8 { grid-column: 1 / -1; }
        .requester-position-field { grid-column: 1 / -1; }
        .incident-nature-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="incident-sheet">
    <header class="incident-sheet-header">
        <div class="incident-sheet-logo">
            <img src="{{ asset('images/wvsu-logo.png') }}" alt="WVSU logo">
        </div>
        <div class="incident-sheet-title">
            <span>West Visayas State University · MIS</span>
            <h3>ICT Related Incident Report Form</h3>
            <p>{{ $report?->document_number ?: 'Document number will be generated after saving' }}</p>
        </div>
        <div class="incident-sheet-meta" aria-label="Form metadata">
            @if(!empty($recordEditMode))
                <div><strong>{{ $report->document_number }}</strong><span>{{ \App\Models\IctIncidentReport::STATUS_OPTIONS[$report->stage_status] ?? ucfirst($report->stage_status) }}</span></div>
            @else
            <div><strong>Form No.</strong><span>WVSU-MIS-SOI-05-F01</span></div>
            <div><strong>Issue / Revision</strong><span>1 / 0</span></div>
            <div><strong>Effectivity</strong><span>July 6, 2022</span></div>
            <div><strong>Page</strong><span>1 of 1</span></div>
            @endif
        </div>
    </header>

    <section class="incident-form-section incident-workflow">
        <div class="incident-section-heading">
            <div><h4>Workflow and Status</h4><p>Assign ownership, acknowledge receipt, and update the case status.</p></div>
            <span class="incident-section-tag">MIS use</span>
        </div>
        <div class="incident-fields">
            <div class="field span-4">
                <label for="user_id">Record Owner</label>
                <select id="user_id" name="user_id">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (string) old('user_id', $report?->user_id ?? $prefillServiceRequest?->user_id) === (string) $user->id ? 'selected' : '' }}>{{ $user->full_name }}{{ $user->email ? ' · ' . $user->email : '' }}</option>
                    @endforeach
                </select>
                @error('user_id') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-4">
                <label for="service_request_id">Linked Service Request</label>
                <select id="service_request_id" name="service_request_id">
                    <option value="">No linked service request</option>
                    @foreach($serviceRequests as $serviceRequest)
                        <option value="{{ $serviceRequest->id }}" {{ (string) old('service_request_id', $report?->service_request_id ?? $prefillServiceRequest?->id) === (string) $serviceRequest->id ? 'selected' : '' }}>
                            {{ $serviceRequest->control_number }} &mdash; {{ $serviceRequest->user?->full_name ?? $serviceRequest->requester_name ?? 'Walk-in requester' }}
                        </option>
                    @endforeach
                </select>
                <small>Connect this incident to the originating client request when applicable.</small>
                @error('service_request_id') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-4 incident-status-field">
                <label for="status">Incident Status</label>
                <select id="status" name="status">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $report?->stage_status ?? 'pending') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-4">
                <label for="received_by">Received By <span aria-hidden="true">*</span></label>
                <input id="received_by" name="received_by" list="mis-staff-options" value="{{ $receivedByValue }}" placeholder="Enter or select MIS staff" required autocomplete="off">
                <datalist id="mis-staff-options">
                    @foreach($misStaffOptions as $staffName)
                        <option value="{{ $staffName }}"></option>
                    @endforeach
                </datalist>
                @error('received_by') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-3">
                <label for="received_date">Received Date</label>
                <input id="received_date" type="date" name="received_date" value="{{ old('received_date', optional($report?->received_date)->format('Y-m-d')) }}">
                @error('received_date') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-3">
                <label for="received_time">Received Time</label>
                <input id="received_time" type="time" name="received_time" value="{{ old('received_time', $report?->received_time ? substr($report->received_time, 0, 5) : '') }}">
                @error('received_time') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-3">
                <label for="date_of_incident">Incident Date</label>
                <input id="date_of_incident" type="date" name="date_of_incident" value="{{ old('date_of_incident', optional($report?->date_of_incident)->format('Y-m-d') ?: ($prefillServiceRequest ? now()->format('Y-m-d') : '')) }}" required>
                @error('date_of_incident') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field span-3">
                <label for="time_of_incident">Incident Time</label>
                <input id="time_of_incident" type="time" name="time_of_incident" value="{{ old('time_of_incident', $report?->time_of_incident ? substr($report->time_of_incident, 0, 5) : '') }}">
                @error('time_of_incident') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>
    </section>

    <section class="incident-form-section incident-reporter">
        <div class="incident-section-heading">
            <div><h4>Reporter Information</h4><p>Information submitted by the client. MIS may correct encoding errors when needed.</p></div>
            <span class="incident-section-tag">Client submitted</span>
        </div>
        <div class="incident-fields">
            <div class="field span-4"><label for="reported_by">Reported By</label><input id="reported_by" name="reported_by" value="{{ old('reported_by', $report?->reported_by ?? $prefillServiceRequest?->requester_name ?? $prefillOwner?->full_name) }}" required>@error('reported_by') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-4"><label for="id_number">ID Number</label><input id="id_number" name="id_number" value="{{ old('id_number', $report?->id_number) }}">@error('id_number') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-4"><label for="department">Department / Office</label><input id="department" name="department" value="{{ old('department', $report?->department ?? $prefillServiceRequest?->requester_office ?? $prefillServiceRequest?->office ?? $prefillOwner?->office) }}">@error('department') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-6"><label for="reporter_email">Email</label><input id="reporter_email" type="email" name="reporter_email" value="{{ old('reporter_email', $report?->reporter_email ?? $prefillOwner?->email) }}">@error('reporter_email') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-6"><label for="reporter_phone">Phone / Local</label><input id="reporter_phone" name="reporter_phone" value="{{ old('reporter_phone', $report?->reporter_phone ?? $prefillServiceRequest?->phone ?? $prefillOwner?->phone_local) }}">@error('reporter_phone') <div class="error-text">{{ $message }}</div> @enderror</div>
        </div>
    </section>

    <section class="incident-form-section incident-nature">
        <div class="incident-section-heading">
            <div><h4>Nature of Incident</h4><p>Select every classification that applies to the incident.</p></div>
        </div>
        <div class="incident-nature-grid">
            @foreach($natureOptions as $value => $label)
                <label class="incident-nature-option">
                    <input type="checkbox" name="nature_of_incident[]" value="{{ $value }}" {{ in_array($value, $selectedNature, true) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @error('nature_of_incident') <div class="error-text" style="margin-top:8px;">{{ $message }}</div> @enderror
        <div class="incident-fields" style="margin-top:14px;">
            <div class="field span-12"><label for="nature_others">If Others, Specify</label><input id="nature_others" name="nature_others" value="{{ old('nature_others', $report?->nature_others) }}">@error('nature_others') <div class="error-text">{{ $message }}</div> @enderror</div>
        </div>
    </section>

    <section class="incident-form-section incident-details">
        <div class="incident-section-heading">
            <div><h4>Details of Incident</h4><p>Review the client narrative before preparing the MIS response.</p></div>
            <span class="incident-section-tag">Client submitted</span>
        </div>
        <div class="field"><label for="details_of_incident">Incident Narrative</label><textarea id="details_of_incident" class="incident-long-text" name="details_of_incident">{{ old('details_of_incident', $report?->details_of_incident ?? $prefillServiceRequest?->description) }}</textarea>@error('details_of_incident') <div class="error-text">{{ $message }}</div> @enderror</div>
    </section>

    <section class="incident-form-section incident-mis-section">
        <div class="incident-section-heading">
            <div><h4>MIS Action and Resolution</h4><p>Record the action taken, final remarks, and responsible signatories.</p></div>
            <span class="incident-section-tag">MIS completion</span>
        </div>
        <div class="incident-fields">
            <div class="field span-6"><label for="action_plan">Action Plan</label><textarea id="action_plan" name="action_plan">{{ old('action_plan', $report?->action_plan) }}</textarea>@error('action_plan') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-6"><label for="remarks">Remarks</label><textarea id="remarks" name="remarks">{{ old('remarks', $report?->remarks) }}</textarea>@error('remarks') <div class="error-text">{{ $message }}</div> @enderror</div>
        </div>
    </section>

    <section class="incident-form-section incident-signoff">
        <div class="incident-section-heading">
            <div><h4>Sign-Off</h4><p>Printed names and positions shown in the official incident PDF.</p></div>
        </div>
        <div class="incident-fields">
            <div class="field span-4 incident-signature-field"><label for="noted_by_name">MIS Head</label><input id="noted_by_name" name="noted_by_name" value="{{ old('noted_by_name', $report?->noted_by_name ?? 'JAY C. LIZA, PhD.') }}"><small>Printed name</small>@error('noted_by_name') <div class="error-text">{{ $message }}</div> @enderror<input type="hidden" name="noted_by_role" value="{{ old('noted_by_role', $report?->noted_by_role ?? 'MIS Head') }}"></div>
            <div class="field span-4 incident-signature-field"><label for="updo_director_name">UPDO Director</label><input id="updo_director_name" name="updo_director_name" value="{{ old('updo_director_name', $report?->updo_director_name) }}" placeholder="Enter printed name"><small>Printed name</small>@error('updo_director_name') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-4 incident-signature-field"><label for="acknowledged_by_name">Acknowledgment</label><input id="acknowledged_by_name" name="acknowledged_by_name" value="{{ old('acknowledged_by_name', $report?->acknowledged_by_name) }}"><small>Requester printed name</small>@error('acknowledged_by_name') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field span-4 requester-position-field"><label for="acknowledged_by_role">Requester Position</label><input id="acknowledged_by_role" name="acknowledged_by_role" value="{{ old('acknowledged_by_role', $report?->acknowledged_by_role) }}">@error('acknowledged_by_role') <div class="error-text">{{ $message }}</div> @enderror</div>
        </div>
    </section>
</div>
