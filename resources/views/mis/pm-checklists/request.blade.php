<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Preventive Maintenance - MISEREQ</title>
    @include('user.partials.theme')
    <style>
        body { margin: 0; }
        .pm-request-page { max-width: 1080px; margin: 0 auto; }
        .pm-request-shell { overflow: hidden; padding: 0 !important; }
        .pm-request-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin: 0; padding: 20px clamp(18px, 3vw, 28px); border-bottom: 1px solid var(--client-line); background: #fff; }
        .pm-request-heading h1 { margin: 0 0 5px; color: var(--client-ink-900); font-size: clamp(23px, 3vw, 29px); line-height: 1.2; }
        .pm-request-heading p, .pm-request-card p { margin: 0; color: var(--client-ink-700); font-size: 15px; line-height: 1.5; }
        .pm-request-heading a { display: inline-flex; align-items: center; min-height: 44px; padding: 8px 13px; border: 1px solid var(--client-line-strong); border-radius: 7px; color: var(--client-brand-700); background: #fff; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .pm-request-card { padding: 22px clamp(18px, 3vw, 28px); border-bottom: 1px solid var(--client-line); }
        .pm-request-card:last-child { border-bottom: 0; }
        .pm-request-card h2 { margin: 0 0 5px; color: var(--client-ink-900); font-size: 19px; }
        .pm-request-card > p { margin-bottom: 17px; }
        .pm-request-note { padding: 13px 15px; border-left: 3px solid var(--client-brand-700); border-radius: 5px; background: var(--client-brand-100); }
        .pm-request-meta { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 15px; color: var(--client-ink-700); font-size: 14px; }
        .pm-request-fields, .pm-request-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; }
        .pm-request-field, .pm-request-group { min-width: 0; }
        .pm-request-field label, .pm-request-group legend { display: block; margin-bottom: 7px; color: var(--client-ink-900); font-size: 15px; font-weight: 700; }
        .pm-request-field input, .pm-request-field textarea { box-sizing: border-box; width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid var(--client-line-strong); border-radius: 7px; background: #fff; color: var(--client-ink-900); font: inherit; }
        .pm-request-field textarea { min-height: 105px; resize: vertical; }
        .pm-request-field small { display: block; margin-top: 5px; color: var(--client-ink-700); font-size: 13px; }
        .pm-request-group { margin: 0; padding: 0; border: 0; }
        .pm-request-group legend { font-size: 17px; }
        .pm-request-group .pm-request-field { margin-bottom: 12px; }
        .pm-request-check-group { min-width: 0; padding: 0 12px 12px; border: 1px solid var(--client-line); border-radius: 8px; background: var(--client-surface-soft); }
        .pm-request-check-group summary { display: list-item; min-height: 44px; padding: 11px 5px; color: var(--client-ink-900); font-size: 16px; font-weight: 700; cursor: pointer; }
        .pm-request-check-group[open] summary { margin-bottom: 10px; border-bottom: 1px solid var(--client-line); }
        .pm-request-check-group .pm-request-choice + .pm-request-choice { margin-top: 7px; }
        .pm-request-check-group .pm-request-field { margin-bottom: 10px; }
        .pm-request-choices { display: flex; flex-wrap: wrap; gap: 10px; }
        .pm-request-choice { display: flex; align-items: flex-start; gap: 9px; min-height: 44px; box-sizing: border-box; padding: 10px 12px; border: 1px solid var(--client-line); border-radius: 8px; color: var(--client-ink-900); background: #f8fafc; font-size: 15px; line-height: 1.4; cursor: pointer; }
        .pm-request-options .pm-request-choice { width: 100%; }
        .pm-request-choice:has(input:checked) { border-color: var(--client-brand-700); background: var(--client-brand-100); }
        .pm-request-choice:hover { border-color: var(--client-brand-700); }
        .pm-request-choice input { flex: none; width: 18px; height: 18px; margin: 1px 0 0; accent-color: var(--client-brand-700); }
        .pm-request-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; }
        .pm-request-actions a, .pm-request-actions button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 18px; border-radius: 7px; font: inherit; font-weight: 700; text-decoration: none; cursor: pointer; }
        .pm-request-actions a { border: 1px solid var(--client-line-strong); color: var(--client-ink-700); background: #fff; }
        .pm-request-actions button { border: 1px solid var(--client-brand-900); color: #fff; background: var(--client-brand-900); }
        .pm-request-errors { margin: 16px 0; padding: 14px 18px; border: 1px solid var(--client-danger); border-radius: 7px; background: var(--client-danger-bg); color: #852b2b; }
        .pm-request-errors ul { margin: 6px 0 0; padding-left: 20px; }
        .pm-request-page :focus-visible { outline: 3px solid var(--client-accent); outline-offset: 2px; }
        @media (max-width: 700px) { .pm-request-fields, .pm-request-options { grid-template-columns: 1fr; } }
        @media (max-width: 520px) { .pm-request-heading { flex-direction: column; padding: 17px; } .pm-request-card { padding: 19px 17px; } .pm-request-actions a, .pm-request-actions button { flex: 1 1 100%; } .pm-request-meta { gap: 5px; flex-direction: column; } }
    </style>
</head>
<body>
    @include('partials.header')
    <main class="page pm-request-page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')
        <section class="panel pm-request-shell" aria-labelledby="pm-request-title">
        <div class="pm-request-heading">
            <div><h1 id="pm-request-title">Request Preventive Maintenance</h1><p>Preventive Maintenance Checklist &middot; West Visayas State University</p></div>
            <a href="{{ route('pm-checklists.index') }}">&larr; Back to Maintenance</a>
        </div>
        <div class="pm-request-card">
            <p class="pm-request-note">Your answers will be submitted as a pending checklist for MIS review. Select checks you want MIS to perform; MIS staff will verify the work and complete the inspection and approval fields.</p>
            <div class="pm-request-meta"><span>Form No. WVSU-MIS-SOI-03-F02</span><span>Issue / Revision: 1 / 1</span><span>Control number assigned on submission</span></div>
            @if($linkedRequest)<p style="margin-top:14px">Linked service request: <strong>{{ $linkedRequest->control_number }}</strong></p>@endif
        </div>
        @if($errors->any())<div class="pm-request-errors" role="alert"><strong>Please correct these details:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('pm-checklists.store') }}">
            @csrf
            @if($linkedRequest)<input type="hidden" name="service_request_id" value="{{ $linkedRequest->id }}">@endif
            <section class="pm-request-card" aria-labelledby="pm-record-title">
                <h2 id="pm-record-title">Maintenance record information</h2><p>Enter the details that identify the equipment and its owner.</p>
                <div class="pm-request-fields">
                    <div class="pm-request-field"><label for="property_number">Property number</label><input id="property_number" name="property_number" value="{{ old('property_number', $linkedRequest?->property_no ?? $linkedRequest?->ict_property_no) }}" maxlength="100"></div>
                    <div class="pm-request-field"><label for="date_inspected">Requested inspection date</label><input id="date_inspected" name="date_inspected" type="date" value="{{ old('date_inspected', now()->toDateString()) }}" required><small>MIS will confirm the actual visit date.</small></div>
                    <div class="pm-request-field"><label for="system_owner">End-user / system owner</label><input id="system_owner" name="system_owner" value="{{ old('system_owner', $linkedRequest?->requester_name ?? auth()->user()->full_name) }}" maxlength="255" required></div>
                    <div class="pm-request-field"><label for="office_department">Office / unit / department</label><input id="office_department" name="office_department" value="{{ old('office_department', $linkedRequest?->requester_office ?? auth()->user()->office) }}" maxlength="255"></div>
                </div>
            </section>
            <section class="pm-request-card" aria-labelledby="pm-equipment-title">
                <h2 id="pm-equipment-title">ICT equipment type</h2><p>Choose one type and provide the matching details.</p>
                <div class="pm-request-fields">
                    <fieldset class="pm-request-group"><legend>Equipment</legend><div class="pm-request-choices">
                        <label class="pm-request-choice"><input type="radio" name="ict_type" value="computer" @checked(old('ict_type', 'computer') === 'computer') required>Computer</label>
                        <label class="pm-request-choice"><input type="radio" name="ict_type" value="other_ict" @checked(old('ict_type') === 'other_ict')>Other ICT property</label>
                    </div></fieldset>
                    <fieldset class="pm-request-group" id="pm-computer-details"><legend>Computer type</legend><div class="pm-request-choices">
                        <label class="pm-request-choice"><input type="radio" name="computer_type" value="desktop" @checked(old('computer_type') === 'desktop')>Desktop</label>
                        <label class="pm-request-choice"><input type="radio" name="computer_type" value="laptop" @checked(old('computer_type') === 'laptop')>Laptop</label>
                    </div></fieldset>
                    <div class="pm-request-field" id="pm-other-type-field"><label for="other_ict_type">Other ICT property type</label><input id="other_ict_type" name="other_ict_type" value="{{ old('other_ict_type') }}" maxlength="100" placeholder="For example, printer or router"></div>
                    <div class="pm-request-field" id="pm-other-specify-field"><label for="other_ict_specify">Other ICT property details</label><input id="other_ict_specify" name="other_ict_specify" value="{{ old('other_ict_specify') }}" maxlength="255"></div>
                </div>
            </section>
            <section class="pm-request-card" aria-labelledby="pm-checks-title">
                <h2 id="pm-checks-title">Checks requested</h2><p>These are the same categories on the official form. Select only the checks relevant to your equipment.</p>
                <div class="pm-request-options">
                    <details class="pm-request-check-group" id="pm-hardware-checks" @if(old('hardware_checks')) open @endif><summary>Computer hardware</summary>@foreach($hardwareOptions as $value => $label)<label class="pm-request-choice"><input type="checkbox" name="hardware_checks[]" value="{{ $value }}" @checked(in_array($value, (array) old('hardware_checks', []), true))>{{ $label }}</label>@endforeach</details>
                    <details class="pm-request-check-group" id="pm-software-checks" @if(old('software_checks')) open @endif><summary>Computer software</summary>@foreach($softwareOptions as $value => $label)<label class="pm-request-choice"><input type="checkbox" name="software_checks[]" value="{{ $value }}" @checked(in_array($value, (array) old('software_checks', []), true))>{{ $label }}</label>@endforeach</details>
                    <details class="pm-request-check-group" id="pm-other-checks" @if(old('other_ict_checks')) open @endif><summary>Other ICT property</summary>@foreach($otherIctCheckOptions as $value => $label)<label class="pm-request-choice"><input type="checkbox" name="other_ict_checks[]" value="{{ $value }}" @checked(in_array($value, (array) old('other_ict_checks', []), true))>{{ $label }}</label>@endforeach</details>
                    <details class="pm-request-check-group" id="pm-info-checks" @if(old('info_systems_checks') || old('info_system_name')) open @endif><summary>Information systems</summary><div class="pm-request-field"><label for="info_system_name">Information system name</label><input id="info_system_name" name="info_system_name" value="{{ old('info_system_name') }}" maxlength="255"></div>@foreach($infoSystemsOptions as $value => $label)<label class="pm-request-choice"><input type="checkbox" name="info_systems_checks[]" value="{{ $value }}" @checked(in_array($value, (array) old('info_systems_checks', []), true))>{{ $label }}</label>@endforeach</details>
                </div>
            </section>
            <section class="pm-request-card" aria-labelledby="pm-notes-title">
                <h2 id="pm-notes-title">Findings and recommendations</h2><p>Describe what you have noticed and any work you would like MIS to consider.</p>
                <div class="pm-request-fields">
                    <div class="pm-request-field"><label for="findings">Observed findings</label><textarea id="findings" name="findings" maxlength="5000">{{ old('findings') }}</textarea></div>
                    <div class="pm-request-field"><label for="recommendations">Suggested recommendations</label><textarea id="recommendations" name="recommendations" maxlength="5000">{{ old('recommendations') }}</textarea></div>
                </div>
            </section>
            <section class="pm-request-card" aria-labelledby="pm-signoff-title">
                <h2 id="pm-signoff-title">Review and sign-off</h2>
                <p>Your account name will appear as the requesting end-user. The inspected-by and noted-by fields remain blank until MIS staff completes its review.</p>
                <div class="pm-request-actions"><a href="{{ route('pm-checklists.index') }}">Cancel</a><button type="submit">Submit maintenance checklist</button></div>
            </section>
        </form>
        </section>
    </main>
    <script>
        (() => {
            const equipmentChoices = document.querySelectorAll('input[name="ict_type"]');
            const computerDetails = document.getElementById('pm-computer-details');
            const otherTypeField = document.getElementById('pm-other-type-field');
            const otherSpecifyField = document.getElementById('pm-other-specify-field');
            const computerChoices = computerDetails.querySelectorAll('input');
            const otherType = document.getElementById('other_ict_type');
            const computerChecks = [document.getElementById('pm-hardware-checks'), document.getElementById('pm-software-checks')];
            const otherChecks = document.getElementById('pm-other-checks');
            function updateEquipmentFields() {
                const isComputer = document.querySelector('input[name="ict_type"]:checked')?.value === 'computer';
                computerDetails.hidden = !isComputer;
                computerChoices.forEach(input => { input.disabled = !isComputer; });
                computerChoices[0].required = isComputer;
                otherTypeField.hidden = isComputer;
                otherSpecifyField.hidden = isComputer;
                otherType.disabled = isComputer;
                document.getElementById('other_ict_specify').disabled = isComputer;
                otherType.required = !isComputer;
                computerChecks.forEach(group => {
                    group.hidden = !isComputer;
                    group.querySelectorAll('input').forEach(input => { input.disabled = !isComputer; });
                });
                otherChecks.hidden = isComputer;
                otherChecks.querySelectorAll('input').forEach(input => { input.disabled = isComputer; });
            }
            equipmentChoices.forEach(input => input.addEventListener('change', updateEquipmentFields));
            updateEquipmentFields();
        })();
    </script>
</body>
</html>
