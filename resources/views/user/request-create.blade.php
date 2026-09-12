<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($requestModel ?? null) ? 'Edit Service Request' : 'New Service Request' }} - MIS Service Request System</title>
    @php
    $user = auth()->user();
    $requestModel = $requestModel ?? null;
    $isEdit = $requestModel !== null;
    $isMaintenanceRequest = request('service') === 'preventive_maintenance';
    $selectedServices = old('services', $requestModel?->services ?? ($isMaintenanceRequest ? ['others_ict'] : []));
    $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
    ? asset('images/wvsu-janiuay-logo.png')
    : asset('images/wvsu-logo.png');
    @endphp
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

        .nav-shell {
            margin-bottom: 18px;
        }

        .nav-toggle {
            display: none;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid #cfd8e3;
            border-radius: 10px;
            background: #fff;
            color: #1f3b57;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            margin-bottom: 12px;
        }

        .nav-toggle-icon {
            display: inline-grid;
            gap: 4px;
        }

        .nav-toggle-icon span {
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: #1f6fa8;
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
        }

        .form-group {
            display: flex;
            flex-direction: column;
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
        .form-group input[type="file"],
        .form-group textarea,
        .form-group select {
            padding: 9px 11px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
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

        #description {
            min-height: 72px;
        }

        .form-group.readonly input {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .service-box {
            margin-bottom: 20px;
            padding: 16px;
            background: #f9fafb;
            border-left: 4px solid #1f6fa8;
            border-radius: 4px;
        }

        .service-box h4 {
            margin: 0 0 12px;
            font-weight: 700;
            color: #111827;
            font-size: 15px;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .checkbox-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            margin-top: 4px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-item label {
            cursor: pointer;
            flex: 1;
            line-height: 1.4;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

        @media (max-width: 1024px) {
            .page {
                padding-bottom: 40px;
            }

            .panel {
                padding: 20px;
            }
        }

        @media (max-width: 860px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-toggle {
                display: inline-flex;
            }

            .nav-tabs {
                display: none;
                flex-direction: column;
                gap: 6px;
                padding: 12px;
                border: 1px solid #d7dce2;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                margin-bottom: 0;
            }

            .nav-tabs.open {
                display: flex;
            }

            .nav-tabs a {
                width: 100%;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row,
            .two-col,
            .three-col,
            .four-col {
                grid-template-columns: 1fr;
            }

            .checkbox-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 560px) {
            .panel {
                padding: 18px;
            }

            .section-header {
                padding: 14px 18px;
                margin: -18px -18px 16px -18px;
            }

            .button,
            button.button {
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
    @include('user.partials.theme')
    <style>
        /* Minimal service selection layout */
        .service-request-form .form-section.services-section {
            padding-top: 22px;
        }

        .service-request-form .services-section>h3 {
            margin-bottom: 12px !important;
        }

        .service-request-form .service-box {
            margin-bottom: 12px !important;
            padding: 14px !important;
            border: 1px solid var(--client-line) !important;
            border-left: 1px solid var(--client-line) !important;
            border-radius: 7px !important;
            background: #ffffff !important;
        }

        .service-request-form .service-box:last-child {
            margin-bottom: 0 !important;
        }

        .service-request-form .service-box h4 {
            margin: 0 0 10px !important;
            padding-bottom: 9px;
            border-bottom: 1px solid #e7ecef;
            color: #29404f !important;
            font-size: 13px !important;
            font-weight: 700;
        }

        .service-request-form .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 7px;
        }

        .service-request-form .checkbox-item {
            min-width: 0;
            min-height: 40px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 9px;
            border: 1px solid #e0e6ea !important;
            border-radius: 5px !important;
            background: #fafcfd !important;
        }

        .service-request-form .checkbox-item:hover {
            border-color: #b8ccd9 !important;
            background: #f5f9fc !important;
        }

        .service-request-form .checkbox-item:has(input:checked) {
            border-color: #8fb7d0 !important;
            background: #eef6fb !important;
        }

        .service-request-form .checkbox-item:focus-within {
            outline: 3px solid rgba(35, 109, 158, .16);
            outline-offset: 1px;
        }

        .service-request-form .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            margin: 0 !important;
            accent-color: var(--client-brand-700);
            cursor: pointer;
        }

        .service-request-form .checkbox-item label {
            min-width: 0;
            margin: 0 !important;
            padding: 0;
            color: #43545f !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1.35;
            overflow-wrap: anywhere;
            cursor: pointer;
        }

        .service-request-form .service-extra-grid,
        .service-request-form .service-extra-input {
            margin-top: 10px !important;
        }

        .service-request-form .service-extra-input {
            width: 100%;
            min-height: 41px;
            padding: 9px 11px !important;
            border: 1px solid var(--client-line-strong) !important;
            border-radius: 6px !important;
            background: #ffffff;
        }

        @media (max-width: 960px) {
            .service-request-form .checkbox-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .service-request-form .service-box {
                padding: 12px !important;
            }

            .service-request-form .checkbox-grid {
                grid-template-columns: 1fr !important;
                gap: 6px;
            }

            .service-request-form .checkbox-item {
                min-height: 42px;
            }
        }

        .draft-banner {
            margin: 0 0 14px;
            padding: 11px 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid #bad8e8;
            border-radius: 7px;
            background: #f1f8fc;
            color: #385665;
            font-size: 12px;
        }

        .draft-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .draft-actions button {
            padding: 6px 9px;
            border: 1px solid #9fc5d9;
            border-radius: 5px;
            background: #fff;
            color: #245f83;
            font: inherit;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .draft-status {
            margin: 0 0 10px;
            min-height: 17px;
            color: #647985;
            font-size: 11px;
            text-align: right;
        }

        .service-request-form .form-action-bar {
            position: sticky;
            z-index: 10;
            bottom: 10px;
            margin: 26px -8px -8px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 9px;
            border: 1px solid var(--client-line);
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 8px 24px rgba(25, 55, 74, .12);
            backdrop-filter: blur(8px);
        }

        .service-request-form .form-action-bar .btn {
            min-width: 140px;
        }

        @media (max-width: 600px) {
            .draft-banner {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 600px) {
            .service-request-form .form-action-bar {
                bottom: 6px;
                margin: 22px -8px -8px;
                padding: 9px;
                display: grid;
                grid-template-columns: 1fr;
            }

            .service-request-form .form-action-bar .btn {
                width: 100%;
                min-height: 46px;
            }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')
        <div id="requestFormConfig" hidden
            data-draft-url="{{ route('request-draft.show') }}"
            data-duplicate-url="{{ route('requests.duplicate-check') }}"
            data-csrf="{{ csrf_token() }}"></div>

        <div class="panel service-request-form">
            <div class="section-header">
                <div class="header-content">
                    <div>
                        <h1>{{ $isEdit ? 'Edit Service Request' : 'New Service Request' }}</h1>
                        <small>WVSU-MIS-SOL-01-F01 · West Visayas State University</small>
                    </div>
                    <a href="{{ route('requests.index') }}" class="button secondary" style="white-space:nowrap;">&larr; Back to requests</a>
                </div>
            </div>

            @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please review the following fields:</strong>
                <ul style="margin: 8px 0 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(!$isEdit)
            <div class="draft-banner" data-draft-banner hidden>
                <span data-draft-copy>A saved draft is available.</span>
                <div class="draft-actions">
                    <button type="button" data-draft-restore>Restore draft</button>
                    <button type="button" data-draft-discard>Discard</button>
                </div>
            </div>
            <p class="draft-status" data-draft-status aria-live="polite"></p>
            <div data-duplicate-warning hidden style="margin:10px 0 14px;padding:12px 14px;border:1px solid #f1d38a;border-left:4px solid #d9aa22;border-radius:7px;background:#fffaf0;color:#5d4a1f;font-size:13px;" role="status"></div>
            @endif

            <form action="{{ $isEdit ? route('requests.update', $requestModel) : route('requests.store') }}" method="POST" enctype="multipart/form-data" data-service-request-form>
                @csrf
                @if($isEdit)
                @method('PATCH')
                @endif

                <!-- Requestor Information -->
                <div class="form-section">
                    <h2>Requestor Information</h2>
                    <div class="form-row four-col">
                        <div class="form-group">
                            <label for="requester_name" class="required">Name of Requestor</label>
                            <input type="text" id="requester_name" name="requester_name"
                                value="{{ old('requester_name', $requestModel?->requester_name ?? (auth()->user()->first_name . (auth()->user()->middle_initial ? ' ' . auth()->user()->middle_initial . '.' : '') . ' ' . auth()->user()->last_name)) }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="designation">Designation</label>
                            <input type="text" id="designation" name="designation"
                                value="{{ old('designation', $requestModel?->requester_designation ?? (auth()->user()->designation ?? '')) }}">
                        </div>
                        <div class="form-group">
                            <label for="requester_office">Office/Unit</label>
                            <input type="text" id="requester_office" name="requester_office"
                                value="{{ old('requester_office', $requestModel?->requester_office ?? (auth()->user()->office ?? '')) }}">
                        </div>
                        <div class="form-group">
                            <label for="phone_local">Phone/Local No.</label>
                            <input type="text" id="phone_local" name="phone_local"
                                value="{{ old('phone_local', $requestModel?->phone ?? (auth()->user()->phone_local ?? '')) }}">
                        </div>
                    </div>

                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="unit_head_name">Unit/Department Head Name & Signature</label>
                            <textarea id="unit_head_name" name="unit_head_name" style="min-height:50px;">{{ old('unit_head_name', $requestModel?->unit_head_name) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="date_request" class="required">Date of Request</label>
                            <input type="date" id="date_request" name="date_request"
                                value="{{ old('date_request', optional($requestModel?->date_of_request)->format('Y-m-d') ?? date('Y-m-d')) }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Service Categories -->
                <div class="form-section services-section">
                    <h2><span class="required">Services Requested</span></h2>

                    <!-- Network Services -->
                    <div class="service-box">
                        <h3>🌐 Network/Telephone Services</h3>
                        <div class="checkbox-grid">
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_new_computer" name="services[]" value="new_network_computer" @checked(in_array('new_network_computer', $selectedServices, true))>
                                <label for="network_new_computer">New Network for Computer</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_new_printer" name="services[]" value="new_network_printer" @checked(in_array('new_network_printer', $selectedServices, true))>
                                <label for="network_new_printer">New Network for Printer</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_new_cabling" name="services[]" value="new_cabling" @checked(in_array('new_cabling', $selectedServices, true))>
                                <label for="network_new_cabling">New Cabling for Network</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_new_telephone" name="services[]" value="new_telephone_line" @checked(in_array('new_telephone_line', $selectedServices, true))>
                                <label for="network_new_telephone">New Telephone Line</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_cabling_telephone" name="services[]" value="new_cabling_telephone" @checked(in_array('new_cabling_telephone', $selectedServices, true))>
                                <label for="network_cabling_telephone">Cabling for Telephone</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_move_location" name="services[]" value="move_new_location" @checked(in_array('move_new_location', $selectedServices, true))>
                                <label for="network_move_location">Move to New Location</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="network_others" name="services[]" value="others_network" @checked(in_array('others_network', $selectedServices, true))>
                                <label for="network_others">Others</label>
                            </div>
                        </div>
                        <div class="form-row two-col service-extra-grid">
                            <input type="text" class="service-extra-input" name="network_specify_location" placeholder="Specify Location (if applicable)">
                            <input type="text" class="service-extra-input" name="network_other" placeholder="Specify Other Service">
                        </div>
                    </div>

                    <!-- ICT Equipment Services -->
                    <div class="service-box">
                        <h3>💻 ICT Equipment Services</h3>
                        <div class="checkbox-grid">
                            <div class="checkbox-item">
                                <input type="checkbox" id="ict_troubleshoot_pc" name="services[]" value="troubleshoot_pc" @checked(in_array('troubleshoot_pc', $selectedServices, true))>
                                <label for="ict_troubleshoot_pc">Troubleshoot PC/Laptop</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ict_troubleshoot_printer" name="services[]" value="troubleshoot_printer" @checked(in_array('troubleshoot_printer', $selectedServices, true))>
                                <label for="ict_troubleshoot_printer">Troubleshoot Printer</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ict_network_connection" name="services[]" value="network_connection" @checked(in_array('network_connection', $selectedServices, true))>
                                <label for="ict_network_connection">Troubleshoot Network/Internet</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ict_software_installation" name="services[]" value="software_installation" @checked(in_array('software_installation', $selectedServices, true))>
                                <label for="ict_software_installation">Software Installation</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ict_hardware_install" name="services[]" value="hardware_install" @checked(in_array('hardware_install', $selectedServices, true))>
                                <label for="ict_hardware_install">Hardware Installation</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="ict_others" name="services[]" value="others_ict" @checked(in_array('others_ict', $selectedServices, true))>
                                <label for="ict_others">Others</label>
                            </div>
                        </div>
                        <input type="text" class="service-extra-input" name="ict_other" placeholder="Specify Other Service">
                    </div>

                    <!-- MIS Software Services -->
                    <div class="service-box">
                        <h3>⚙️ MIS Software/System Services</h3>
                        <div class="checkbox-grid">
                            <div class="checkbox-item">
                                <input type="checkbox" id="mis_account_creation" name="services[]" value="account_creation" @checked(in_array('account_creation', $selectedServices, true))>
                                <label for="mis_account_creation">Account/User Creation</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="mis_password_reset" name="services[]" value="password_reset" @checked(in_array('password_reset', $selectedServices, true))>
                                <label for="mis_password_reset">Password Reset/Change</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="mis_system_access" name="services[]" value="system_access" @checked(in_array('system_access', $selectedServices, true))>
                                <label for="mis_system_access">System Access Request</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="mis_others" name="services[]" value="others_mis" @checked(in_array('others_mis', $selectedServices, true))>
                                <label for="mis_others">Others</label>
                            </div>
                        </div>
                        <input type="text" class="service-extra-input" name="mis_other" placeholder="Specify Other Service">
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h2>Additional Information</h2>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="description">Description/Details of Request <span style="color:#64748b;font-weight:400;">(optional)</span></label>
                            <textarea id="description" name="description" placeholder="Add helpful details only if needed.">{{ old('description', $requestModel?->description ?? ($isMaintenanceRequest ? 'Request for preventive maintenance of ICT equipment. Equipment/location details: ' : '')) }}</textarea>
                        </div>
                    </div>

                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="property_no">Property/Asset No.</label>
                            <input type="text" id="property_no" name="property_no" value="{{ old('property_no', $requestModel?->property_no) }}">
                        </div>
                        <div class="form-group">
                            <label for="attachments">Attachments (Optional)</label>
                            <input type="file" id="attachments" name="attachments[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                            <small>Up to 5 files. PDF, DOC, DOCX, JPG, or PNG; maximum 10MB each. Files are not stored in drafts.</small>
                            @if($isEdit && $requestModel?->attachment)
                            <small>Current file: <a href="{{ route('requests.attachment', $requestModel) }}">Download current attachment</a></small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons form-action-bar" aria-label="Form actions">
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">{{ $isEdit ? 'Discard changes' : 'Cancel' }}</a>
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save changes' : 'Submit request' }}</button>
                </div>
            </form>
        </div>
    </main>
    @if(!$isEdit)
    <script>
        (function() {
            const form = document.querySelector('[data-service-request-form]');
            const banner = document.querySelector('[data-draft-banner]');
            const copy = document.querySelector('[data-draft-copy]');
            const status = document.querySelector('[data-draft-status]');
            if (!form || !banner || !status) return;

            const config = document.getElementById('requestFormConfig');
            const endpoint = config.dataset.draftUrl;
            const csrf = config.dataset.csrf;
            let availableDraft = null;
            let timer = null;

            function payloadFromForm() {
                const payload = {};
                Array.from(form.elements).forEach(function(field) {
                    if (!field.name || field.disabled || field.type === 'file' || field.type === 'submit' || field.name.startsWith('_')) return;
                    const key = field.name.replace(/\[\]$/, '');
                    if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
                    if (field.name.endsWith('[]')) {
                        if (!Array.isArray(payload[key])) payload[key] = [];
                        payload[key].push(field.value);
                    } else {
                        payload[key] = field.value;
                    }
                });
                return payload;
            }

            function restore(payload) {
                Object.keys(payload || {}).forEach(function(key) {
                    const fields = form.querySelectorAll('[name="' + CSS.escape(key) + '"], [name="' + CSS.escape(key + '[]') + '"]');
                    fields.forEach(function(field) {
                        const value = payload[key];
                        if (field.type === 'checkbox') field.checked = Array.isArray(value) && value.includes(field.value);
                        else if (field.type === 'radio') field.checked = String(value) === field.value;
                        else field.value = Array.isArray(value) ? (value[0] || '') : (value ?? '');
                    });
                });
                banner.hidden = true;
                status.textContent = 'Draft restored. Attachments must be selected again.';
            }

            function save() {
                status.textContent = 'Saving draft...';
                fetch(endpoint, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        payload: payloadFromForm()
                    })
                }).then(function(response) {
                    status.textContent = response.ok ? 'Draft saved automatically.' : 'Draft could not be saved.';
                }).catch(function() {
                    status.textContent = 'Draft could not be saved.';
                });
            }

            form.addEventListener('input', function() {
                clearTimeout(timer);
                status.textContent = 'Unsaved changes...';
                timer = setTimeout(save, 900);
            });
            form.addEventListener('change', function() {
                clearTimeout(timer);
                timer = setTimeout(save, 500);
            });

            document.querySelector('[data-draft-restore]').addEventListener('click', function() {
                restore(availableDraft);
            });
            document.querySelector('[data-draft-discard]').addEventListener('click', function() {
                fetch(endpoint, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                });
                availableDraft = null;
                banner.hidden = true;
                status.textContent = 'Saved draft discarded.';
            });

            fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.ok ? response.json() : null;
                })
                .then(function(data) {
                    if (!data || !data.draft) return;
                    availableDraft = data.draft.payload;
                    copy.textContent = 'A draft saved ' + data.draft.updated_human + ' is available.';
                    banner.hidden = false;
                });
        })();

        (function() {
            const description = document.getElementById('description');
            const propertyNo = document.getElementById('property_no');
            const warning = document.querySelector('[data-duplicate-warning]');
            if (!description || !propertyNo || !warning) return;
            const config = document.getElementById('requestFormConfig');
            const endpoint = config.dataset.duplicateUrl;
            const csrf = config.dataset.csrf;
            let timer = null;

            function check() {
                if (description.value.trim().length < 12 && !propertyNo.value.trim()) {
                    warning.hidden = true;
                    return;
                }
                fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            description: description.value,
                            property_no: propertyNo.value
                        })
                    }).then(function(response) {
                        return response.ok ? response.json() : {
                            matches: []
                        };
                    })
                    .then(function(data) {
                        const matches = Array.isArray(data.matches) ? data.matches : [];
                        warning.replaceChildren();
                        warning.hidden = matches.length === 0;
                        if (!matches.length) return;
                        const title = document.createElement('strong');
                        title.textContent = 'Similar active request found. Check it before submitting another request:';
                        warning.appendChild(title);
                        matches.forEach(function(match) {
                            const line = document.createElement('div');
                            line.style.marginTop = '5px';
                            const link = document.createElement('a');
                            link.href = match.url;
                            link.textContent = match.control_number + ' · ' + match.status;
                            link.style.fontWeight = '700';
                            link.style.color = '#1f6fa8';
                            line.appendChild(link);
                            warning.appendChild(line);
                        });
                    });
            }

            [description, propertyNo].forEach(function(field) {
                field.addEventListener('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(check, 700);
                });
                field.addEventListener('blur', check);
            });
        })();
    </script>
    @endif
</body>

</html>