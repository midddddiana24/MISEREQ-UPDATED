@php
    $checklist = $checklist ?? null;
    $prefillServiceRequest = $prefillServiceRequest ?? null;
    $prefillOwner = $prefillServiceRequest?->user;
    $hardwareAliases = [
        'avr_ups' => 'complete_components', 'power_supply' => 'check_power',
        'system_fan' => 'check_power', 'processor' => 'complete_components',
        'motherboard' => 'complete_components', 'memory' => 'complete_components',
        'video_card' => 'complete_components', 'storage' => 'complete_components',
        'monitor' => 'complete_components', 'keyboard_mouse' => 'complete_components',
        'cleanup' => 'remove_dust', 'network_connectivity' => 'check_network_connectivity',
    ];
    $softwareAliases = [
        'disk_cleanup' => 'delete_trash_junk', 'disk_defragmenter' => 'defragment_disk',
        'scandisk' => 'scan_for_viruses', 'antivirus' => 'scan_for_viruses',
        'remove_registry' => 'remove_unwanted_registry', 'productivity_tools' => 'complete_productivity_tools',
        'scan_viruses' => 'scan_for_viruses', 'delete_junk' => 'delete_trash_junk',
        'security_tools' => 'update_security_tools',
    ];
    $selectedHardware = collect(old('hardware_checks', $checklist?->hardware_checks ?? []))
        ->map(fn ($value) => $hardwareAliases[$value] ?? $value)->unique()->values()->all();
    $selectedSoftware = collect(old('software_checks', $checklist?->software_checks ?? []))
        ->map(fn ($value) => $softwareAliases[$value] ?? $value)->unique()->values()->all();
    $selectedOtherIctChecks = old('other_ict_checks', $checklist?->other_ict_checks ?? []);
    $selectedInfoSystems = old('info_systems_checks', $checklist?->info_systems_checks ?? []);
    $selectedIctType = old('ict_type', $checklist?->ict_type ?? 'computer');
    $selectedComputerType = old('computer_type', $checklist?->computer_type);
@endphp

<style>
    .pm-editor-panel { padding: 18px; }
    .pm-editor-panel > .section-heading {
        max-width: 1280px;
        margin: 0 auto 14px;
        padding: 0 2px 12px;
        border-bottom: 1px solid var(--line, #d8dee8);
    }
    .pm-editor-panel > .section-heading h2 { margin: 0; font-size: 20px; }
    .pm-editor-panel > .section-heading p { margin: 3px 0 0; }
    .pm-edit-form { grid-template-columns: minmax(0, 1fr); }
    .pm-sheet {
        grid-column: 1 / -1;
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .07);
    }
    .pm-sheet-header {
        display: grid;
        grid-template-columns: 72px minmax(250px, 1fr) minmax(300px, .82fr);
        border-bottom: 1px solid var(--line, #d8dee8);
    }
    .pm-sheet-logo {
        display: grid;
        place-items: center;
        padding: 10px;
        border-right: 1px solid var(--line, #d8dee8);
        background: #fafbfc;
    }
    .pm-sheet-logo img { width: 44px; height: 44px; object-fit: contain; }
    .pm-sheet-title { display: grid; align-content: center; padding: 12px 16px; }
    .pm-sheet-title span {
        margin-bottom: 3px;
        color: var(--ink-500, #64748b);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .pm-sheet-title h3 { margin: 0; font-size: 18px; line-height: 1.25; }
    .pm-sheet-title p { margin: 5px 0 0; color: var(--ink-500, #64748b); font-size: 12px; }
    .pm-sheet-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-left: 1px solid var(--line, #d8dee8);
    }
    .pm-sheet-meta div { padding: 8px 10px; border-bottom: 1px solid var(--line, #d8dee8); }
    .pm-sheet-meta div:nth-last-child(-n+2) { border-bottom: 0; }
    .pm-sheet-meta strong, .pm-sheet-meta span { display: block; }
    .pm-sheet-meta strong { color: var(--ink-500, #64748b); font-size: 10px; text-transform: uppercase; }
    .pm-sheet-meta span { margin-top: 2px; font-size: 12px; font-weight: 700; }
    .pm-source-incident {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 16px;
        border-bottom: 1px solid var(--line, #d8dee8);
        background: #f2f8ff;
        color: #1d4f91;
        font-size: 13px;
    }
    .pm-source-incident strong { color: var(--ink-900, #14202b); }
    .pm-source-incident a { font-weight: 750; text-decoration: underline; text-underline-offset: 2px; }
    .pm-form-section { padding: 15px 17px; border-bottom: 1px solid var(--line, #d8dee8); }
    .pm-form-section:last-child { border-bottom: 0; }
    .pm-section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
    }
    .pm-section-heading h4 { margin: 0; font-size: 15px; line-height: 1.3; }
    .pm-section-heading p { margin: 4px 0 0; color: var(--ink-500, #64748b); font-size: 12px; line-height: 1.45; }
    .pm-section-tag {
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
    .pm-fields {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        column-gap: 16px;
        row-gap: 14px;
    }
    .pm-fields .field { min-width: 0; align-content: start; gap: 6px; }
    .pm-fields .field label { font-size: 13px; line-height: 1.35; }
    .pm-span-3 { grid-column: span 3; }
    .pm-span-4 { grid-column: span 4; }
    .pm-span-6 { grid-column: span 6; }
    .pm-span-8 { grid-column: span 8; }
    .pm-span-12 { grid-column: 1 / -1; }
    .pm-sheet input:not([type="checkbox"]):not([type="radio"]),
    .pm-sheet select {
        width: 100%;
        min-width: 0;
        min-height: 42px;
        padding: 9px 11px;
        font-size: 14px;
    }
    .pm-sheet textarea {
        width: 100%;
        min-height: 86px;
        padding: 10px 11px;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
    }
    .pm-workflow { background: #f8fafc; }
    .pm-status-field select { border-color: #8db4df; background: #f5faff; font-weight: 750; }
    .pm-equipment-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(0, .88fr);
        border: 1px solid var(--line, #d8dee8);
        border-radius: 11px;
        overflow: hidden;
    }
    .pm-equipment-group { min-width: 0; min-inline-size: 0; margin: 0; padding: 13px 15px; border: 0; }
    .pm-equipment-group + .pm-equipment-group { border-left: 1px solid var(--line, #d8dee8); }
    .pm-equipment-title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 11px;
        font-size: 14px;
        font-weight: 750;
    }
    .pm-equipment-title input, .pm-radio-option input { width: 17px; height: 17px; margin: 0; accent-color: var(--brand-700, #2f6ea1); }
    .pm-radio-row { display: flex; justify-content: center; gap: 9px; margin-bottom: 12px; }
    .pm-radio-option {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 38px;
        padding: 7px 11px;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 9px;
        background: #fff;
        font-size: 13px;
        cursor: pointer;
    }
    .pm-radio-option:has(input:checked) { border-color: #77a6d7; background: #f2f8ff; }
    .pm-equipment-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pm-checklist-grid {
        display: grid;
        grid-template-columns: 1.08fr 1.16fr .88fr .88fr;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 11px;
        overflow: hidden;
    }
    .pm-check-column { min-width: 0; padding: 13px; background: #fff; }
    .pm-check-column + .pm-check-column { border-left: 1px solid var(--line, #d8dee8); }
    .pm-check-column h5 { margin: 0 0 10px; font-size: 13px; }
    .pm-check-column .field { gap: 5px; margin-bottom: 10px; }
    .pm-check-column .field label { font-size: 12px; }
    .pm-check-column input:not([type="checkbox"]) { min-height: 38px; padding: 7px 9px; font-size: 13px; }
    .pm-check-options { display: grid; gap: 7px; }
    .pm-check-option {
        display: grid;
        grid-template-columns: 17px minmax(0, 1fr);
        align-items: start;
        gap: 8px;
        padding: 7px 8px;
        border: 1px solid #e1e7ed;
        border-radius: 8px;
        background: #fafbfc;
        color: #334155;
        font-size: 12px;
        line-height: 1.35;
        cursor: pointer;
    }
    .pm-check-option:has(input:checked) { border-color: #77a6d7; background: #f2f8ff; }
    .pm-check-option input { width: 16px; height: 16px; margin: 0; accent-color: var(--brand-700, #2f6ea1); }
    .pm-findings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .pm-signoff-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .pm-signoff-card { min-width: 0; padding: 12px; border: 1px solid var(--line, #d8dee8); border-radius: 10px; background: #fafbfc; }
    .pm-signoff-card h5 { margin: 0 0 10px; font-size: 13px; text-align: center; }
    .pm-signoff-card .pm-fields { grid-template-columns: 1fr 1fr; gap: 10px; }
    .pm-signature-field { grid-column: span 1; align-content: start; }
    .pm-signature-field label { min-height: 18px; text-align: center; }
    .pm-signature-field input { border-width: 0 0 1px; border-radius: 0; background: transparent; text-align: center; font-weight: 700; }
    .pm-signature-field small { display: block; margin-top: 4px; color: var(--ink-500, #64748b); font-size: 11px; text-align: center; }
    @media (min-width: 1200px) {
        .pm-form-section { padding: 14px 16px; }
        .pm-sheet textarea { min-height: 76px; }
    }
    @media (max-width: 1120px) {
        .pm-sheet-header { grid-template-columns: 64px 1fr; }
        .pm-sheet-meta { grid-column: 1 / -1; border-top: 1px solid var(--line, #d8dee8); border-left: 0; }
        .pm-checklist-grid { grid-template-columns: 1fr 1fr; }
        .pm-check-column:nth-child(3) { border-left: 0; border-top: 1px solid var(--line, #d8dee8); }
        .pm-check-column:nth-child(4) { border-top: 1px solid var(--line, #d8dee8); }
        .pm-signoff-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 760px) {
        .pm-editor-panel { padding: 10px; }
        .pm-editor-panel > .section-heading { margin-bottom: 10px; padding-bottom: 10px; }
        .pm-fields .pm-span-3, .pm-fields .pm-span-4, .pm-fields .pm-span-6, .pm-fields .pm-span-8 { grid-column: span 6; }
        .pm-equipment-grid, .pm-findings-grid { grid-template-columns: 1fr; }
        .pm-equipment-group + .pm-equipment-group { border-left: 0; border-top: 1px solid var(--line, #d8dee8); }
    }
    @media (max-width: 600px) {
        .pm-sheet { border-radius: 10px; }
        .pm-sheet-header { grid-template-columns: 56px 1fr; }
        .pm-sheet-logo img { width: 38px; height: 38px; }
        .pm-sheet-title { padding: 12px; }
        .pm-sheet-title h3 { font-size: 15px; }
        .pm-sheet-meta { grid-template-columns: 1fr 1fr; }
        .pm-form-section { padding: 15px 13px; }
        .pm-section-heading { display: block; }
        .pm-section-tag { display: inline-block; margin-top: 8px; }
        .pm-fields .pm-span-3, .pm-fields .pm-span-4, .pm-fields .pm-span-6, .pm-fields .pm-span-8 { grid-column: 1 / -1; }
        .pm-equipment-fields, .pm-checklist-grid { grid-template-columns: 1fr; }
        .pm-check-column + .pm-check-column { border-left: 0; border-top: 1px solid var(--line, #d8dee8); }
        .pm-radio-row { flex-wrap: wrap; }
        .pm-source-incident { align-items: flex-start; flex-direction: column; }
        .pm-signoff-card .pm-fields { grid-template-columns: 1fr; }
    }
</style>

<div class="pm-sheet">
    <header class="pm-sheet-header">
        <div class="pm-sheet-logo"><img src="{{ asset('images/wvsu-logo.png') }}" alt="WVSU logo"></div>
        <div class="pm-sheet-title">
            <span>West Visayas State University &middot; MIS</span>
            <h3>Preventive Maintenance Checklist</h3>
            <p>{{ $checklist?->control_number ?: 'Control number will be generated after saving' }}</p>
        </div>
        <div class="pm-sheet-meta" aria-label="Form metadata">
            <div><strong>Form No.</strong><span>WVSU-MIS-SOI-03-F02</span></div>
            <div><strong>Issue / Revision</strong><span>1 / 1</span></div>
            <div><strong>Effectivity</strong><span>July 6, 2022</span></div>
            <div><strong>Page</strong><span>1 of 1</span></div>
        </div>
    </header>

    <section class="pm-form-section pm-workflow">
        <div class="pm-section-heading">
            <div><h4>Workflow and Record Ownership</h4><p>Assign the record and update its current maintenance status.</p></div>
            <span class="pm-section-tag">MIS use</span>
        </div>
        <div class="pm-fields">
            <div class="field pm-span-8">
                <label for="user_id">Record Owner</label>
                <select id="user_id" name="user_id">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id', $checklist?->user_id ?? $prefillServiceRequest?->user_id) === (string) $user->id)>{{ $user->full_name }}{{ $user->email ? ' - ' . $user->email : '' }}</option>
                    @endforeach
                </select>
                @error('user_id') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field pm-span-8">
                <label for="service_request_id">Linked Service Request</label>
                <select id="service_request_id" name="service_request_id">
                    <option value="">No linked service request</option>
                    @foreach($serviceRequests as $serviceRequest)
                        <option value="{{ $serviceRequest->id }}" @selected((string) old('service_request_id', $checklist?->service_request_id ?? $prefillServiceRequest?->id) === (string) $serviceRequest->id)>
                            {{ $serviceRequest->control_number }} &mdash; {{ $serviceRequest->user?->full_name ?? $serviceRequest->requester_name ?? 'Walk-in requester' }}
                        </option>
                    @endforeach
                </select>
                <small>Optional. Link this independent maintenance checklist to its originating service request.</small>
                @error('service_request_id') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="field pm-span-4 pm-status-field">
                <label for="status">Checklist Status</label>
                <select id="status" name="status">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $checklist?->status ?? 'pending') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>
    </section>

    <section class="pm-form-section">
        <div class="pm-section-heading">
            <div><h4>Maintenance Record Information</h4><p>Core details displayed at the top of the official checklist.</p></div>
        </div>
        <div class="pm-fields">
            <div class="field pm-span-3"><label for="property_number">Property Number</label><input id="property_number" name="property_number" value="{{ old('property_number', $checklist?->property_number ?? $prefillServiceRequest?->property_no ?? $prefillServiceRequest?->ict_property_no) }}">@error('property_number') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field pm-span-3"><label for="date_inspected">Date Inspected</label><input id="date_inspected" type="date" name="date_inspected" value="{{ old('date_inspected', optional($checklist?->date_inspected)->format('Y-m-d') ?: ($prefillServiceRequest ? now()->format('Y-m-d') : '')) }}" required>@error('date_inspected') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field pm-span-3"><label for="system_owner">End-user / System Owner</label><input id="system_owner" name="system_owner" value="{{ old('system_owner', $checklist?->system_owner ?? $prefillServiceRequest?->requester_name ?? $prefillOwner?->full_name) }}" required>@error('system_owner') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field pm-span-3"><label for="office_department">Office / Unit / Department</label><input id="office_department" name="office_department" value="{{ old('office_department', $checklist?->office_department ?? $prefillServiceRequest?->requester_office ?? $prefillServiceRequest?->office ?? $prefillOwner?->office) }}">@error('office_department') <div class="error-text">{{ $message }}</div> @enderror</div>
        </div>
    </section>

    <section class="pm-form-section">
        <div class="pm-section-heading">
            <div><h4>ICT Equipment Type</h4><p>Choose the applicable equipment classification and provide its details.</p></div>
        </div>
        <div class="pm-equipment-grid">
            <fieldset class="pm-equipment-group">
                <legend class="pm-equipment-title"><input type="radio" name="ict_type" value="computer" @checked($selectedIctType === 'computer')> Computer</legend>
                <div class="pm-radio-row">
                    <label class="pm-radio-option"><input type="radio" name="computer_type" value="desktop" @checked($selectedComputerType === 'desktop')><span>Desktop</span></label>
                    <label class="pm-radio-option"><input type="radio" name="computer_type" value="laptop" @checked($selectedComputerType === 'laptop')><span>Laptop</span></label>
                </div>
                @error('ict_type') <div class="error-text">{{ $message }}</div> @enderror
                @error('computer_type') <div class="error-text">{{ $message }}</div> @enderror
            </fieldset>
            <fieldset class="pm-equipment-group">
                <legend class="pm-equipment-title"><input type="radio" name="ict_type" value="other_ict" @checked($selectedIctType === 'other_ict')> Other ICT Property</legend>
                <div class="pm-equipment-fields">
                    <div class="field"><label for="other_ict_type">ICT Type</label><input id="other_ict_type" name="other_ict_type" value="{{ old('other_ict_type', $checklist?->other_ict_type) }}" placeholder="e.g. Printer">@error('other_ict_type') <div class="error-text">{{ $message }}</div> @enderror</div>
                    <div class="field"><label for="other_ict_specify">If Others, Specify</label><input id="other_ict_specify" name="other_ict_specify" value="{{ old('other_ict_specify', $checklist?->other_ict_specify) }}">@error('other_ict_specify') <div class="error-text">{{ $message }}</div> @enderror</div>
                </div>
            </fieldset>
        </div>
    </section>

    <section class="pm-form-section">
        <div class="pm-section-heading">
            <div><h4>Preventive Maintenance Checks</h4><p>Select every inspection or maintenance activity completed for this record.</p></div>
        </div>
        <div class="pm-checklist-grid">
            <div class="pm-check-column">
                <h5>1. Hardware</h5>
                <div class="pm-check-options">
                    @foreach($hardwareOptions as $value => $label)
                        <label class="pm-check-option"><input type="checkbox" name="hardware_checks[]" value="{{ $value }}" @checked(in_array($value, $selectedHardware, true))><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="pm-check-column">
                <h5>2. Software</h5>
                <div class="pm-check-options">
                    @foreach($softwareOptions as $value => $label)
                        <label class="pm-check-option"><input type="checkbox" name="software_checks[]" value="{{ $value }}" @checked(in_array($value, $selectedSoftware, true))><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="pm-check-column">
                <h5>Other ICT Property</h5>
                <div class="pm-check-options">
                    @foreach($otherIctCheckOptions as $value => $label)
                        <label class="pm-check-option"><input type="checkbox" name="other_ict_checks[]" value="{{ $value }}" @checked(in_array($value, $selectedOtherIctChecks, true))><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="pm-check-column">
                <h5>Information System / Software</h5>
                <div class="field"><label for="info_system_name">System Name</label><input id="info_system_name" name="info_system_name" value="{{ old('info_system_name', $checklist?->info_system_name) }}">@error('info_system_name') <div class="error-text">{{ $message }}</div> @enderror</div>
                <div class="pm-check-options">
                    @foreach($infoSystemsOptions as $value => $label)
                        <label class="pm-check-option"><input type="checkbox" name="info_systems_checks[]" value="{{ $value }}" @checked(in_array($value, $selectedInfoSystems, true))><span>{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="pm-form-section">
        <div class="pm-section-heading">
            <div><h4>Findings and Recommendations</h4><p>Document observed conditions and the corresponding maintenance advice.</p></div>
            <span class="pm-section-tag">Inspection result</span>
        </div>
        <div class="pm-findings-grid">
            <div class="field"><label for="findings">Findings</label><textarea id="findings" name="findings">{{ old('findings', $checklist?->findings) }}</textarea>@error('findings') <div class="error-text">{{ $message }}</div> @enderror</div>
            <div class="field"><label for="recommendations">Recommendations</label><textarea id="recommendations" name="recommendations">{{ old('recommendations', $checklist?->recommendations) }}</textarea>@error('recommendations') <div class="error-text">{{ $message }}</div> @enderror</div>
        </div>
    </section>

    <section class="pm-form-section">
        <div class="pm-section-heading">
            <div><h4>Sign-Off</h4><p>Printed names and roles shown in the official preventive maintenance PDF.</p></div>
        </div>
        <div class="pm-signoff-grid">
            <div class="pm-signoff-card">
                <h5>Inspected By</h5>
                <div class="pm-fields">
                    <div class="field pm-signature-field"><label for="inspected_by_name">Printed Name</label><input id="inspected_by_name" name="inspected_by_name" value="{{ old('inspected_by_name', $checklist?->inspected_by_name) }}">@error('inspected_by_name') <div class="error-text">{{ $message }}</div> @enderror</div>
                    <div class="field pm-signature-field"><label for="inspected_by_role">Position</label><input id="inspected_by_role" name="inspected_by_role" value="{{ old('inspected_by_role', $checklist?->inspected_by_role) }}">@error('inspected_by_role') <div class="error-text">{{ $message }}</div> @enderror</div>
                </div>
            </div>
            <div class="pm-signoff-card">
                <h5>Noted By</h5>
                <div class="pm-fields">
                    <div class="field pm-signature-field"><label for="noted_by_name">Printed Name</label><input id="noted_by_name" name="noted_by_name" value="{{ old('noted_by_name', $checklist?->noted_by_name) }}">@error('noted_by_name') <div class="error-text">{{ $message }}</div> @enderror</div>
                    <div class="field pm-signature-field"><label for="noted_by_role">Position</label><input id="noted_by_role" name="noted_by_role" value="{{ old('noted_by_role', $checklist?->noted_by_role) }}">@error('noted_by_role') <div class="error-text">{{ $message }}</div> @enderror</div>
                </div>
            </div>
            <div class="pm-signoff-card">
                <h5>Acknowledgment</h5>
                <div class="pm-fields">
                    <div class="field pm-signature-field"><label for="acknowledged_by_name">Printed Name</label><input id="acknowledged_by_name" name="acknowledged_by_name" value="{{ old('acknowledged_by_name', $checklist?->acknowledged_by_name) }}">@error('acknowledged_by_name') <div class="error-text">{{ $message }}</div> @enderror</div>
                    <div class="field pm-signature-field"><label for="acknowledged_by_role">Position</label><input id="acknowledged_by_role" name="acknowledged_by_role" value="{{ old('acknowledged_by_role', $checklist?->acknowledged_by_role) }}">@error('acknowledged_by_role') <div class="error-text">{{ $message }}</div> @enderror</div>
                </div>
            </div>
        </div>
    </section>
</div>
