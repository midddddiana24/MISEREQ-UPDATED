<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>MIS Service Request Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #D1884F;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }

        .header-info {
            flex: 1;
            margin-left: 20px;
            text-align: center;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .university-name {
            font-size: 14px;
            font-weight: bold;
        }

        .form-number {
            text-align: right;
            margin-bottom: 20px;
        }

        .form-number-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .form-number-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
        }

        .form-section {
            margin-bottom: 20px;
            border: 1px solid #000;
        }

        .form-section-title {
            background-color: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            font-size: 12px;
        }

        .form-row {
            display: flex;
            border-bottom: 1px solid #000;
        }

        .form-field {
            flex: 1;
            padding: 8px;
            border-right: 1px solid #000;
        }

        .form-field:last-child {
            border-right: none;
        }

        .form-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .form-value {
            font-size: 12px;
            min-height: 20px;
        }

        .checkbox {
            margin-right: 5px;
        }

        .checked {
            content: "☑";
        }

        .service-items {
            padding: 10px;
            font-size: 11px;
        }

        .service-item {
            margin-bottom: 5px;
        }

        .service-item input {
            margin-right: 5px;
        }

        .footer-section {
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
            font-size: 11px;
        }

        .signature-area {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            flex: 1;
            margin-right: 20px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 11px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">M</div>
            <div class="header-info">
                <div class="header-title">MIS SERVICE REQUEST FORM</div>
                <div class="university-name">WEST VISAYAS STATE UNIVERSITY</div>
            </div>
        </div>

        <!-- Form Number -->
        <div class="form-number">
            <table class="form-number-table">
                <tr>
                    <td><strong>Form No.</strong></td>
                    <td>WVSU-MIS-SOL-01-F01</td>
                </tr>
                <tr>
                    <td><strong>Issue No.</strong></td>
                    <td>1</td>
                </tr>
                <tr>
                    <td><strong>Date of Effectivity</strong></td>
                    <td>{{ $serviceRequest->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Issued by</strong></td>
                    <td>MIS</td>
                </tr>
                <tr>
                    <td><strong>Page No.</strong></td>
                    <td>Page 1 of 1</td>
                </tr>
            </table>
        </div>

        <!-- Requester Information -->
        <div class="form-section">
            <div class="form-section-title">REQUESTER INFORMATION</div>
            <div class="form-row">
                <div class="form-field" style="flex: 2;">
                    <div class="form-label">Name and Signature of Requestor</div>
                    <div class="form-value">{{ $serviceRequest->user?->full_name ?? $serviceRequest->requester_name }}</div>
                </div>
                <div class="form-field">
                    <div class="form-label">Designation</div>
                    <div class="form-value">{{ $serviceRequest->requester_designation ?? 'N/A' }}</div>
                </div>
                <div class="form-field">
                    <div class="form-label">Office/Unit</div>
                    <div class="form-value">{{ $serviceRequest->requester_office ?? $serviceRequest->office ?? 'N/A' }}</div>
                </div>
                <div class="form-field">
                    <div class="form-label">Contact Number</div>
                    <div class="form-value">{{ $serviceRequest->phone ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-field" style="flex: 2;">
                    <div class="form-label">Name and Signature of Dean/Department Head</div>
                    <div class="form-value"></div>
                </div>
                <div class="form-field">
                    <div class="form-label">Phone/Fax No.</div>
                    <div class="form-value"></div>
                </div>
                <div class="form-field">
                    <div class="form-label">Date of Request</div>
                    <div class="form-value">{{ $serviceRequest->created_at->format('m/d/Y') }}</div>
                </div>
                <div class="form-field">
                    <div class="form-label">Time of Request</div>
                    <div class="form-value">{{ $serviceRequest->created_at->format('h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Service Category -->
        <div class="form-section">
            <div class="form-section-title">SERVICE REQUESTED</div>
            <div class="service-items">
                <strong>Service Category:</strong> {{ ucwords(str_replace('_', ' ', $serviceRequest->service_category ?? $serviceRequest->service_type ?? 'General')) }}
                <br><br>

                @if($serviceRequest->service_category === 'network' || $serviceRequest->service_type === 'network')
                <strong>Network/Telephone Services:</strong>
                <div class="service-item">
                    @if($serviceRequest->network_telephone_new_network ?? false) ☑ @else ☐ @endif New Network for Computer
                </div>
                <div class="service-item">
                    @if($serviceRequest->network_telephone_new_printer ?? false) ☑ @else ☐ @endif New Network for Printer
                </div>
                <div class="service-item">
                    @if($serviceRequest->network_telephone_new_calling ?? false) ☑ @else ☐ @endif New Calling for Network/Layout plan
                </div>
                <div class="service-item">
                    @if($serviceRequest->network_telephone_new_telephone ?? false) ☑ @else ☐ @endif New Telephone Line
                </div>
                <div class="service-item">
                    @if($serviceRequest->network_telephone_new_internet ?? false) ☑ @else ☐ @endif New Internet/Cable Line
                </div>
                <div class="service-item">
                    @if($serviceRequest->network_telephone_move ?? false) ☑ @else ☐ @endif Move to New Location: {{ $serviceRequest->network_telephone_location ?? 'N/A' }}
                </div>
                @elseif($serviceRequest->service_category === 'ict_equipment' || $serviceRequest->service_type === 'ict_equipment')
                <strong>ICT Equipment Services:</strong>
                <div class="service-item">
                    @if($serviceRequest->ict_equipment_troubleshoot_laptop ?? false) ☑ @else ☐ @endif Troubleshoot PC/Laptop
                </div>
                <div class="service-item">
                    @if($serviceRequest->ict_equipment_troubleshoot_printer ?? false) ☑ @else ☐ @endif Troubleshoot Printer
                </div>
                <div class="service-item">
                    @if($serviceRequest->ict_equipment_troubleshoot_connection ?? false) ☑ @else ☐ @endif Troubleshoot Telephone/Internet Connection
                </div>
                <div class="service-item">
                    @if($serviceRequest->ict_equipment_troubleshoot_other ?? false) ☑ @else ☐ @endif Others
                </div>
                @elseif($serviceRequest->service_category === 'software' || $serviceRequest->service_type === 'software')
                <strong>Software/Applications Services:</strong>
                <div class="service-item">
                    @if($serviceRequest->software_new_user_account ?? false) ☑ @else ☐ @endif New User Account
                </div>
                <div class="service-item">
                    @if($serviceRequest->software_reset_password ?? false) ☑ @else ☐ @endif Reset Password
                </div>
                <div class="service-item">
                    @if($serviceRequest->software_request_copy ?? false) ☑ @else ☐ @endif Request for Copy of Data
                </div>
                <div class="service-item">
                    @if($serviceRequest->software_grant_privilege ?? false) ☑ @else ☐ @endif Grant Privilege/Access
                </div>
                <div class="service-item">
                    @if($serviceRequest->software_others ?? false) ☑ @else ☐ @endif Others
                </div>
                @elseif($serviceRequest->service_category === 'web_services' || $serviceRequest->service_type === 'web_services')
                <strong>Web Administrator Services:</strong>
                <div class="service-item">
                    @if($serviceRequest->web_new_email_account ?? false) ☑ @else ☐ @endif New WVSU Email Account
                </div>
                <div class="service-item">
                    @if($serviceRequest->web_reset_email ?? false) ☑ @else ☐ @endif Reset Email Account Password
                </div>
                <div class="service-item">
                    @if($serviceRequest->web_graphic_design ?? false) ☑ @else ☐ @endif Req/Graphic Design
                </div>
                <div class="service-item">
                    @if($serviceRequest->web_website_update ?? false) ☑ @else ☐ @endif Website Update/Article Posting
                </div>
                <div class="service-item">
                    @if($serviceRequest->web_multimedia_files ?? false) ☑ @else ☐ @endif Request for Copy of Multimedia Files
                </div>
                <div class="service-item">
                    @if($serviceRequest->web_others ?? false) ☑ @else ☐ @endif Others
                </div>
                @else
                <div class="service-item">General Service Request</div>
                @endif
            </div>
        </div>

        <!-- Description -->
        <div class="form-section">
            <div class="form-section-title">DESCRIPTION OF REQUEST</div>
            <div class="service-items">
                {{ $serviceRequest->description ?? 'No description provided' }}
            </div>
        </div>

        <!-- Status Section -->
        <div class="form-section">
            <div class="form-section-title">STATUS AND REMARKS</div>
            <div class="form-row">
                <div class="form-field" style="flex: 1;">
                    <div class="form-label">Current Status</div>
                    <div class="form-value">{{ ucfirst($serviceRequest->status) }}</div>
                </div>
                <div class="form-field" style="flex: 2;">
                    <div class="form-label">Remarks</div>
                    <div class="form-value">{{ $serviceRequest->remarks ?? 'Pending assignment' }}</div>
                </div>
            </div>
        </div>

        <!-- Technician Information -->
        @if($serviceRequest->assigned_technician_id && $technician)
        <div class="form-section">
            <div class="form-section-title">ASSIGNED TECHNICIAN</div>
            <div class="form-row">
                <div class="form-field" style="flex: 2;">
                    <div class="form-label">Technician Name</div>
                    <div class="form-value">{{ $technician->user?->full_name ?? $technician->first_name . ' ' . $technician->last_name }}</div>
                </div>
                <div class="form-field">
                    <div class="form-label">Specialization</div>
                    <div class="form-value">{{ $technician->specialization ?? 'General' }}</div>
                </div>
                <div class="form-field">
                    <div class="form-label">Job Assignment</div>
                    <div class="form-value">{{ $technician->job_assignment ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Signature Area -->
        <div class="signature-area">
            <div class="signature-box">
                <strong>Requestor Signature:</strong>
                <div class="signature-line">____________________</div>
            </div>
            <div class="signature-box">
                <strong>MIS Officer Signature:</strong>
                <div class="signature-line">____________________</div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 30px; font-size: 10px; text-align: center; color: #666;">
            <p>This is an electronically generated document. Printed on {{ now()->format('F d, Y H:i:s') }}</p>
            <p>Control Number: {{ $serviceRequest->control_number }}</p>
        </div>
    </div>
</body>

</html>
