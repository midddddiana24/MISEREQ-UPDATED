<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New ICT Incident Report</title>
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

        /* keep textarea taller */
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

        /* file inputs smaller padding */
        .form-group input[type="file"] {
            padding: 8px 6px;
            border-radius: 6px;
            box-sizing: border-box;
        }

        /* Ensure rows with a single form-group span full width */
        .form-row>.form-group:only-child {
            grid-column: 1 / -1;
        }

        /* File inputs appearance */
        .form-group input[type="file"] {
            padding: 8px 6px;
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

        .checks {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 14px;
            margin: 8px 0 14px;
        }

        .checks label {
            display: flex;
            gap: 10px;
            font-weight: 400;
            align-items: center;
            cursor: pointer;
        }

        .checks input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1f6fa8;
            flex-shrink: 0;
            margin: 0;
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
    </style>
    @include('user.partials.theme')
</head>

<body>
    @include('partials.header')

    <main class="page">
        @include('user.partials.navigation')

        <section class="panel">
            <div class="section-header">
                <div class="header-content">
                    <div>
                        <h1>ICT Related Incident Report Form</h1>
                        <small>ICT Incident Report · West Visayas State University</small>
                    </div>
                    <a href="{{ route('dashboard') }}" class="button secondary" style="white-space:nowrap;">← Back to Dashboard</a>
                </div>
            </div>

            @if(isset($linkedRequest) && $linkedRequest)
                <div class="alert alert-success">Linked to service request <strong>{{ $linkedRequest->control_number }}</strong>.</div>
            @endif

            <form method="POST" action="{{ route('incident-reports.store') }}">
                @csrf
                @if(isset($linkedRequest) && $linkedRequest)
                    <input type="hidden" name="service_request_id" value="{{ $linkedRequest->id }}">
                @endif
                <div class="form-section">
                    <h3>Reporter Information</h3>
                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="reported_by" class="required">Reported By</label>
                            <input id="reported_by" value="{{ $user->full_name ?? '' }}" readonly>
                            <small>Your signed-in account is used as the official reporter.</small>
                            @error('reported_by') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="id_number">ID Number</label>
                            <input id="id_number" name="id_number" value="{{ old('id_number') }}">
                        </div>
                    </div>

                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="department">Department/Office</label>
                            <input id="department" name="department" value="{{ old('department', $user->office ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="reporter_email">Email</label>
                            <input type="email" id="reporter_email" value="{{ $user->email ?? '' }}" readonly>
                        </div>
                    </div>

                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="reporter_phone">Phone</label>
                            <input id="reporter_phone" name="reporter_phone" value="{{ old('reporter_phone') }}">
                        </div>
                        <div class="form-group">
                            <label for="date_of_incident" class="required">Date of Incident</label>
                            <input type="date" id="date_of_incident" name="date_of_incident" value="{{ old('date_of_incident', now()->format('Y-m-d')) }}" required>
                            @error('date_of_incident') <div class="error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row two-col">
                        <div class="form-group">
                            <label for="time_of_incident">Time of Incident</label>
                            <input type="time" id="time_of_incident" name="time_of_incident" value="{{ old('time_of_incident') }}">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Nature of Incident</h3>
                    @php
                    $natureOptions = [
                    'loss_of_sensitive_data' => 'Loss of sensitive and valuable data',
                    'loss_of_valuable_hardware' => 'Loss of valuable hardware',
                    'breach_of_information_security' => 'Breach of information security/protocols',
                    'ict_hardware_software_theft' => 'ICT hardware/software theft, damage, or loss',
                    'virus_worm_malware' => 'Virus/Trojan/Worm infection/DDoS',
                    'inappropriate_use' => 'Inappropriate use of WVSU ICT facilities',
                    'others_nature' => 'Others',
                    ];
                    @endphp
                    <div class="checks">
                        @foreach ($natureOptions as $value => $label)
                        <label><input type="checkbox" name="nature_of_incident[]" value="{{ $value }}" @checked(in_array($value, old('nature_of_incident', [])))> {{ $label }}</label>
                        @endforeach
                    </div>


                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="nature_others">If Others, specify</label>
                            <input id="nature_others" name="nature_others" value="{{ old('nature_others') }}">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Incident Details</h3>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="details_of_incident">Details of Incident</label>
                            <textarea id="details_of_incident" name="details_of_incident">{{ old('details_of_incident') }}</textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Action Plan</label>
                            <div class="alert alert-success">MIS will prepare the action plan after reviewing the incident.</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Remarks and Preventive Maintenance</label>
                            <div class="alert alert-success">MIS will complete these sections after reviewing the incident report.</div>
                        </div>
                    </div>

                    <div class="action-buttons" style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary">Submit Report</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Cancel</a>
                    </div>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
