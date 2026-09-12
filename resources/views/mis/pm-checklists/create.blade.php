<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Preventive Maintenance Checklist</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f4f8fc;
            overflow-x: hidden;
        }

        .site-header {
            background: #1f6fa8;
            border-bottom: 4px solid #f4c430;
            color: #fff;
        }

        .header-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 4px;
        }

        .brand small {
            display: block;
            font: 700 15px Georgia, "Times New Roman", serif;
        }

        .brand h1 {
            font: 700 clamp(20px, 3vw, 32px) Georgia, "Times New Roman", serif;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .brand p {
            font: 700 16px Georgia, "Times New Roman", serif;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .button,
        button.button {
            display: inline-block;
            padding: 9px 13px;
            border: 1px solid #1f6fa8;
            border-radius: 3px;
            background: #1f6fa8;
            color: #fff;
            font: 700 14px Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: #fff;
            color: #1f6fa8;
        }

        .button:hover {
            background: #164875;
        }

        .button.secondary:hover {
            background: #f0f0f0;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 22px clamp(14px, 4vw, 20px) 48px;
        }

        .nav-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .nav-tabs a {
            padding: 8px 14px;
            border-radius: 3px;
            color: #2d123d;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-tabs a.active {
            background: #2f7fbd;
            color: #fff;
        }

        .nav-tabs a:hover {
            background: #e5e7eb;
        }

        .panel {
            padding: 22px;
            background: #fff;
            border: 1px solid #d7dce2;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .form-section {
            margin-bottom: 24px;
        }

        .form-section h3 {
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 6px;
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group input:not([type]),
        .form-group select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            width: 100%;
            height: 44px;
            line-height: 20px;
            box-sizing: border-box;
            background: #ffffff;
        }

        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            width: 100%;
            min-height: 100px;
            resize: vertical;
            box-sizing: border-box;
        }

        .form-group input[type="file"] {
            padding: 8px 6px;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1f6fa8;
            box-shadow: 0 0 0 2px rgba(31, 111, 168, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group.readonly input {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 12px;
            align-items: start;
        }

        .checks {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 14px;
            margin: 8px 0 14px;
        }

        .checks {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 10px 12px;
            background: #ffffff;
            border: 1px solid #eef2f8;
            border-radius: 6px;
        }

        .checks>label:first-child {
            margin: 0 0 6px 0;
            padding: 0;
        }

        .checks label {
            display: flex;
            gap: 10px;
            font-weight: 400;
            align-items: center;
            margin: 0;
            padding: 4px 0;
        }

        .checks label strong {
            display: block;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .checks input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1f6fa8;
            margin: 0;
            flex-shrink: 0;
        }

        .inline label {
            display: flex;
            gap: 8px;
            font-weight: 400;
            margin-bottom: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            justify-content: center;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #1f6fa8;
            color: #fff;
        }

        .btn-primary:hover {
            background: #164875;
        }

        .btn-secondary {
            background: #fff;
            color: #1f6fa8;
            border: 1px solid #1f6fa8;
        }

        .btn-secondary:hover {
            background: #f0f0f0;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 16px;
            border-radius: 4px;
        }

        .alert-error {
            background: #fce8e8;
            color: #991b1b;
            border: 1px solid #f87171;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .required::after {
            content: ' *';
            color: #dc2626;
        }

        .section-header {
            background: #f3f4f6;
            padding: 16px 22px;
            margin: -22px -22px 16px -22px;
            border-radius: 6px 6px 0 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header-content h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .header-content small {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin: 4px 0 0;
        }

        .two-col {
            grid-template-columns: repeat(2, 1fr);
        }

        .three-col {
            grid-template-columns: repeat(3, 1fr);
        }

        .four-col {
            grid-template-columns: repeat(4, 1fr);
        }

        .full-width {
            grid-column: 1 / -1;
        }

        small {
            display: block;
            color: #6b7280;
            margin-top: 4px;
            font-size: 12px;
        }

        input[readonly] {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .error {
            color: #b91c1c;
            font-size: 13px;
            margin-top: 5px;
        }

        @media (max-width: 860px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row,
            .two-col,
            .three-col,
            .four-col,
            .columns {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
    @include('user.partials.theme')
</head>

<body>
    @php
    $user = auth()->user();
    $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
    ? asset('images/wvsu-janiuay-logo.png')
    : asset('images/wvsu-logo.png');
    @endphp

    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        <section class="panel">
            <div class="section-header">
                <div class="header-content">
                    <div>
                        <h1>Preventive Maintenance Checklist</h1>
                        <small>Preventive Maintenance · West Visayas State University</small>
                    </div>
                    <a href="{{ route('dashboard') }}" class="button secondary" style="white-space:nowrap;">← Back to Dashboard</a>
                </div>
            </div>

            @if(isset($linkedRequest) && $linkedRequest)
            <div class="alert alert-success">Linked to service request <strong>{{ $linkedRequest->control_number }}</strong>.</div>
            @endif

            <form method="POST" action="{{ route('pm-checklists.store') }}">
                @csrf
                @if(isset($linkedRequest) && $linkedRequest)
                <input type="hidden" name="service_request_id" value="{{ $linkedRequest->id }}">
                @endif

                <div class="form-section">
                    <h3>📋 Checklist Information</h3>
                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="system_owner" class="required">System Owner</label>
                            <input id="system_owner" name="system_owner" value="{{ old('system_owner', $user->full_name ?? '') }}" required>
                            @error('system_owner') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="office_department">Office/Department</label>
                            <input id="office_department" name="office_department" value="{{ old('office_department', $user->office ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="property_number">Property Number</label>
                            <input id="property_number" name="property_number" value="{{ old('property_number') }}">
                        </div>
                        <div class="form-group">
                            <label for="date_inspected" class="required">Date Inspected</label>
                            <input type="date" id="date_inspected" name="date_inspected" value="{{ old('date_inspected', now()->format('Y-m-d')) }}" required>
                            @error('date_inspected') <div class="error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>⚙️ ICT Equipment Type</h3>
                    <div class="inline">
                        <label><input type="radio" name="ict_type" value="computer" @checked(old('ict_type', 'computer' )==='computer' )> Computer</label>
                        <label><input type="radio" name="computer_type" value="desktop" @checked(old('computer_type')==='desktop' )> Desktop</label>
                        <label><input type="radio" name="computer_type" value="laptop" @checked(old('computer_type')==='laptop' )> Laptop</label>
                        <label><input type="radio" name="ict_type" value="other_ict" @checked(old('ict_type')==='other_ict' )> Other ICT Equipment</label>
                        <label><input type="radio" name="other_ict_type" value="printer" @checked(old('other_ict_type')==='printer' )> Printer</label>
                        <label><input type="radio" name="other_ict_type" value="others" @checked(old('other_ict_type')==='others' )> Others</label>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="other_ict_specify">If Others, specify</label>
                            <input id="other_ict_specify" name="other_ict_specify" value="{{ old('other_ict_specify') }}">
                        </div>
                    </div>
                </div>

                @php
                $hardware = [
                'complete_components' => 'Complete Components & Peripherals',
                'remove_dust' => 'Remove Dust Build-up',
                'check_power' => 'Check Power/Overheating Issues',
                'check_network_connectivity' => 'Check/Optimize Network Connectivity',
                ];
                $software = [
                'update_drivers' => 'Up-to-date Drivers',
                'remove_unwanted_registry' => 'Remove Unwanted Registry Files',
                'defragment_disk' => 'Defragment Disk Drive',
                'complete_productivity_tools' => 'Complete Productivity Tools',
                'scan_for_viruses' => 'Scan for Viruses',
                'optimize_startup' => 'Optimize Start-up',
                'check_os_licenses' => 'Check OS and Other Licenses',
                'delete_trash_junk' => 'Delete Trash/Junk Files',
                'update_security_tools' => 'Up-to-date Security Tools',
                ];
                $infoSystems = [
                'connectivity' => 'Connectivity',
                'software_maintenance' => 'Software Maintenance',
                'database_backup' => 'Database Backup',
                'application_backup' => 'Application Backup',
                ];
                @endphp

                <div class="form-section">
                    <h3>✅ Checklist</h3>
                    <div class="columns">
                        <div class="checks">
                            <label><strong>Hardware</strong></label>
                            @foreach ($hardware as $value => $label)
                            <label><input type="checkbox" name="hardware_checks[]" value="{{ $value }}" @checked(in_array($value, old('hardware_checks', [])))> {{ $label }}</label>
                            @endforeach
                        </div>
                        <div class="checks">
                            <label><strong>Software</strong></label>
                            @foreach ($software as $value => $label)
                            <label><input type="checkbox" name="software_checks[]" value="{{ $value }}" @checked(in_array($value, old('software_checks', [])))> {{ $label }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <div class="checks">
                                <label><strong>Other ICT Type</strong></label>
                                <input id="other_ict_type" name="other_ict_type" value="{{ old('other_ict_type') }}" placeholder="Type (e.g. Network device)">
                                <label><input type="checkbox" name="other_ict_checks[]" value="software" @checked(in_array('software', old('other_ict_checks', [])))> Software</label>
                                <label><input type="checkbox" name="other_ict_checks[]" value="hardware" @checked(in_array('hardware', old('other_ict_checks', [])))> Hardware</label>
                                <label><input type="checkbox" name="other_ict_checks[]" value="network" @checked(in_array('network', old('other_ict_checks', [])))> Network</label>
                                <label><input type="checkbox" name="other_ict_checks[]" value="communications" @checked(in_array('communications', old('other_ict_checks', [])))> Communications</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <div class="checks">
                                <label><strong>Information Systems</strong></label>
                                <input name="info_system_name" value="{{ old('info_system_name') }}" placeholder="System name" style="margin-bottom:8px;">
                                @foreach ($infoSystems as $value => $label)
                                <label><input type="checkbox" name="info_systems_checks[]" value="{{ $value }}" @checked(in_array($value, old('info_systems_checks', [])))> {{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>📝 Findings & Recommendations</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="findings">Findings</label>
                            <textarea id="findings" name="findings">{{ old('findings') }}</textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="recommendations">Recommendations</label>
                            <textarea id="recommendations" name="recommendations">{{ old('recommendations') }}</textarea>
                        </div>
                    </div>

                    <div class="action-buttons" style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary">Submit Checklist</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Cancel</a>
                    </div>
                </div>
            </form>
        </section>
    </main>
</body>

</html>