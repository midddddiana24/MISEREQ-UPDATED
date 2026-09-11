<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8" />
    <title>ICT Related Incident Report Form - {{ $report->document_number ?? 'PREVIEW' }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #fff;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 8.5pt;
            line-height: 1.16;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            width: 177mm;
            margin: 18mm 18mm 15mm 15mm;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 1px 3px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .middle {
            vertical-align: middle;
        }

        .bold {
            font-weight: 700;
        }

        .header-meta td {
            padding: .35mm 1mm;
            vertical-align: middle;
        }

        .header-title {
            font-size: 10.5pt;
            font-weight: 700;
            line-height: 1.08;
            text-align: center;
        }

        .header-label {
            font-size: 8.5pt;
            font-weight: 700;
        }

        .header-value {
            font-size: 8.5pt;
            font-weight: 700;
            text-align: center;
        }

        .logo-box img {
            width: 13mm;
            display: block;
            margin: 0 auto;
        }

        .section-label {
            font-weight: 700;
        }

        .plain-cell {
            border: none;
            padding: 0;
        }

        .no-top {
            border-top: none !important;
        }

        .no-bottom {
            border-bottom: none !important;
        }

        .note-row {
            font-size: 8.5pt;
            font-style: italic;
            text-align: center;
        }

        .checkbox {
            display: inline-block;
            width: 4mm;
            height: 4mm;
            margin-right: 2mm;
            border: 1px solid #000;
            text-align: center;
            line-height: 3.7mm;
            font-size: 8pt;
            font-family: DejaVu Sans, sans-serif;
            vertical-align: middle;
        }

        .checkbox.on::after {
            content: "\2713";
        }

        .incident-option {
            margin: 0 0 .8mm;
        }

        .underline {
            display: inline-block;
            min-height: 12px;
            border-bottom: 1px solid #000;
            vertical-align: bottom;
        }

        .boxed-value {
            min-height: 6mm;
        }

        .area-cell {
            vertical-align: top;
            padding: 1.2mm 2mm;
        }

        .details-area { height: 14mm; }
        .action-area { height: 20mm; }
        .remarks-area { height: 16mm; }

        .spacer-lg {
            height: 11mm;
        }

        .mini-note {
            margin: .5mm 0 1mm;
            font-size: 8pt;
            font-style: italic;
        }

        .signature-table td {
            border: none;
            padding: 1mm 0;
        }

        .signature-line {
            border: none !important;
            padding-top: 0 !important;
        }

        .signature-rule {
            width: 41mm;
            border-top: 1px solid #000;
            padding-top: 1mm;
        }

        .signature-caption {
            font-size: 7.5pt;
            font-style: italic;
        }

        .university-name {
            font-size: 10.5pt;
            font-weight: 700;
            text-align: center;
            line-height: 1.1;
        }
        .header-row-sm {
            height: 4.4mm;
        }

        .header-row-lg {
            height: 4.4mm;
        }

        .field-head {
            height: 4.5mm;
            vertical-align: middle;
        }

        .field-body {
            height: 4.5mm;
            vertical-align: middle;
        }

        .nature-body {
            height: 43mm;
            padding: 2.5mm 6mm 1.5mm !important;
        }

        .details-head {
            height: 4mm;
            vertical-align: middle;
        }

        .mis-note {
            height: 4.5mm;
        }

        .received-values td { height: 12.5mm; }

        .received-centered {
            text-align: center;
            vertical-align: middle;
        }

        .centered-output {
            text-align: center;
            vertical-align: middle;
        }

        .reported-name,
        .mis-staff-name {
            font-weight: 700;
            text-transform: uppercase;
        }

        .mis-staff-name {
            display: inline-block;
            min-width: 45mm;
            padding: 0 1mm .5mm;
            border-bottom: 1px solid #000;
        }

        .mis-staff-role {
            margin-top: .8mm;
            font-weight: 400;
        }

        .sig-slot {
            height: 6.3mm;
            vertical-align: bottom;
        }

        .doc-table {
            width: 70mm;
        }

        .doc-table td {
            padding: .6mm 1.5mm;
        }

        .doc-label {
            width: 24mm;
            white-space: nowrap;
        }

        .sign-name {
            min-height: 4.8mm;
        }

        .signature-printed-name {
            width: 47mm;
            min-height: 4mm;
            margin: 0 auto;
            font-weight: 700;
            text-align: center;
        }

        .signature-rule-below {
            width: 47mm;
            margin: .3mm auto 0;
            border-top: 1px solid #000;
        }

        .signature-position {
            width: 47mm;
            margin: 1mm auto 0;
            text-align: center;
        }

        .signature-caption-centered {
            width: 47mm;
            margin: .6mm auto 0;
            font-size: 7.5pt;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $natures = is_array($report->nature_of_incident ?? null)
            ? $report->nature_of_incident
            : (json_decode($report->nature_of_incident ?? '[]', true) ?? []);
        $incidentDate = $report->date_of_incident?->format('m/d/Y') ?? ($report->date_of_incident ?? '');
        $receivedDate = $report->received_date?->format('m/d/Y') ?? ($report->received_date ?? '');
        $receivedTime = $report->received_time ? \Carbon\Carbon::parse($report->received_time)->format('h:i A') : '';
        $isNatureChecked = fn (...$keys) => collect($keys)->contains(fn ($key) => in_array($key, $natures, true));
        $acknowledgedName = $report->acknowledged_by_name ?: $report->user?->full_name ?: $report->reported_by;
        $acknowledgedPosition = $report->acknowledged_by_role ?: $report->requester_position ?: $report->user?->designation ?: 'Position';
    @endphp

    <div class="page">
        <table class="header-meta">
            <tr class="header-row-lg">
                <td rowspan="6" class="logo-box middle center" style="width: 12%;">
                    <img src="{{ public_path('images/wvsu-janiuay-logo.png') }}" alt="WVSU Logo">
                </td>
                <td rowspan="3" class="header-title middle" style="width: 36%;">
                    ICT RELATED INCIDENT<br>REPORT FORM
                </td>
                <td class="header-label" style="width: 22%;">Form No.</td>
                <td class="header-value" style="width: 30%;">WVSU-MIS-SOI-05-F01</td>
            </tr>
            <tr class="header-row-sm">
                <td class="header-label">Issue No.</td>
                <td class="header-value">1</td>
            </tr>
            <tr class="header-row-sm">
                <td class="header-label">Revision No.</td>
                <td class="header-value">0</td>
            </tr>
            <tr class="header-row-lg">
                <td rowspan="3" class="university-name middle">
                    WEST VISAYAS STATE<br>UNIVERSITY
                </td>
                <td class="header-label">Date of Effectivity</td>
                <td class="header-value">July 6, 2022</td>
            </tr>
            <tr class="header-row-sm">
                <td class="header-label">Issued by</td>
                <td class="header-value">MIS</td>
            </tr>
            <tr class="header-row-sm">
                <td class="header-label">Page No.</td>
                <td class="header-value">Page 1 of 1</td>
            </tr>
        </table>

        <div class="spacer-lg"></div>

        <table class="doc-table">
            <tr>
                <td class="section-label middle doc-label">Document Number:</td>
                <td class="boxed-value">{{ $report->document_number ?? '' }}</td>
            </tr>
        </table>

        <div style="height: 5mm;"></div>

        <table>
            <tr>
                <td class="field-head" style="width: 58%;"><span class="section-label">Nature of Incident:</span></td>
                <td class="field-head" style="width: 42%;">
                    <span class="section-label">Date of Incident:</span>
                    <span class="underline" style="width: 32mm; margin-left: 2mm;">{{ $incidentDate }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="nature-body">
                    <div class="incident-option">
                        <span class="checkbox {{ $isNatureChecked('loss_sensitive_data', 'loss_of_sensitive_data') ? 'on' : '' }}"></span>
                        Loss of sensitive and valuable data
                    </div>
                    <div class="incident-option">
                        <span class="checkbox {{ $isNatureChecked('loss_valuable_hardware', 'loss_of_valuable_hardware') ? 'on' : '' }}"></span>
                        Loss of valuable hardware
                    </div>
                    <div class="incident-option">
                        <span class="checkbox {{ $isNatureChecked('breach_info_security', 'breach_of_information_security') ? 'on' : '' }}"></span>
                        Breach of information security/protocols
                    </div>
                    <div class="incident-option">
                        <span class="checkbox {{ $isNatureChecked('ict_hardware_software_theft') ? 'on' : '' }}"></span>
                        ICT hardware or software theft, damage, or loss
                    </div>
                    <div class="incident-option">
                        <span class="checkbox {{ $isNatureChecked('virus_worm_dos', 'virus_worm_malware') ? 'on' : '' }}"></span>
                        Virus/Trojan/Worm infection/DOS on servers or WVSU owned applications
                    </div>
                    <div class="incident-option">
                        <span class="checkbox {{ $isNatureChecked('inappropriate_use') ? 'on' : '' }}"></span>
                        Inappropriate use of WVSU ICT facilities or services
                    </div>
                    <div class="incident-option" style="margin-bottom: 0;">
                        <span class="checkbox {{ $isNatureChecked('others_nature') ? 'on' : '' }}"></span>
                        Others. Please specify
                        <span class="underline bold" style="width: 58mm; margin-left: 2mm; text-transform:uppercase;">{{ mb_strtoupper((string) ($report->nature_others ?? ''), 'UTF-8') }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table style="margin-top: -1px;">
            <tr>
                <td class="section-label no-bottom details-head">Details of Incident:</td>
            </tr>
            <tr>
                <td class="area-cell details-area no-top">{{ mb_strtoupper((string) ($report->details_of_incident ?? ''), 'UTF-8') }}</td>
            </tr>
        </table>

        <table style="margin-top: -1px;">
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 40%;">
                <col style="width: 10%;">
                <col style="width: 20%;">
            </colgroup>
            <tr>
                <td class="section-label field-body" style="width:30%;">Reported By:</td>
                <td class="field-body reported-name" style="width:40%;">{{ mb_strtoupper((string) ($report->reported_by ?? ''), 'UTF-8') }}</td>
                <td class="section-label field-body" style="width:10%;">Signature:</td>
                <td class="field-body" style="width:20%;">{{ $report->reporter_signature ?? '' }}</td>
            </tr>
            <tr>
                <td class="field-body">ID Number:</td>
                <td class="field-body">{{ $report->id_number ?? '' }}</td>
                <td class="field-body">Email:</td>
                <td class="field-body">{{ $report->reporter_email ?? '' }}</td>
            </tr>
            <tr>
                <td class="field-body">Department:</td>
                <td class="field-body">{{ $report->department ?? '' }}</td>
                <td class="field-body">Phone:</td>
                <td class="field-body">{{ $report->reporter_phone ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="4" class="note-row mis-note">The following fields below must be filled by the MIS Staff</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:0; border:none;">
                    <table>
                        <colgroup>
                            <col style="width: 70%;">
                            <col style="width: 10%;">
                            <col style="width: 20%;">
                        </colgroup>
                        <tr>
                            <td class="section-label field-body" style="width:70%;">Received by</td>
                            <td class="section-label field-body center" style="width:10%;">Date</td>
                            <td class="section-label field-body center" style="width:20%;">Time</td>
                        </tr>
                        <tr class="received-values">
                            <td class="boxed-value received-centered" style="width:70%;">
                                @if($report->received_by)
                                    <div class="mis-staff-name">{{ mb_strtoupper($report->received_by, 'UTF-8') }}</div>
                                    <div class="mis-staff-role">MIS STAFF</div>
                                @endif
                            </td>
                            <td class="boxed-value received-centered" style="width:10%;">{{ $receivedDate }}</td>
                            <td class="boxed-value received-centered" style="width:20%;">{{ $receivedTime }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="height: 3mm;"></div>

        <table>
            <tr>
                <td class="section-label details-head">Action Plan:</td>
            </tr>
            <tr>
                <td class="area-cell action-area centered-output">{{ mb_strtoupper((string) ($report->action_plan ?? ''), 'UTF-8') }}</td>
            </tr>
        </table>

        <table style="margin-top: -1px;">
            <tr>
                <td class="section-label details-head">Remarks:</td>
            </tr>
            <tr>
                <td class="area-cell remarks-area centered-output">{{ mb_strtoupper((string) ($report->remarks ?? ''), 'UTF-8') }}</td>
            </tr>
        </table>

        <p class="mini-note">*More than one signatory can be added if there are other persons involved in the action plan</p>

        <table class="signature-table">
            <colgroup>
                <col style="width: 33.33%;">
                <col style="width: 33.33%;">
                <col style="width: 33.34%;">
            </colgroup>
            <tr>
                <td colspan="2"><span class="section-label">Noted by:</span></td>
                <td><span class="section-label">Acknowledgement:</span></td>
            </tr>
            <tr>
                <td class="sig-slot">
                    @if($report->noted_by_signature ?? false)
                        <img src="{{ $report->noted_by_signature }}" alt="Noted by signature" style="max-height: 36px; display: block;">
                    @endif
                </td>
                <td class="sig-slot">
                    @if($report->updo_director_signature ?? false)
                        <img src="{{ $report->updo_director_signature }}" alt="UPDO Director signature" style="max-height: 36px; display: block;">
                    @endif
                </td>
                <td class="sig-slot">
                    @if($report->acknowledged_by_signature ?? false)
                        <img src="{{ $report->acknowledged_by_signature }}" alt="Acknowledgement signature" style="max-height: 36px; display: block;">
                    @endif
                </td>
            </tr>
            <tr>
                <td class="signature-line">
                    <div class="signature-printed-name">{{ $report->noted_by_name ?: 'JAY C. LIZA, PhD.' }}</div>
                    <div class="signature-caption-centered">(signature above printed name)</div>
                    <div class="signature-rule-below"></div>
                    <div class="signature-position">{{ $report->noted_by_role ?: 'MIS Head' }}</div>
                </td>
                <td class="signature-line">
                    <div class="signature-printed-name">{{ $report->updo_director_name ? mb_strtoupper($report->updo_director_name, 'UTF-8') : '' }}</div>
                    <div class="signature-caption-centered">(signature above printed name)</div>
                    <div class="signature-rule-below"></div>
                    <div class="signature-position">UPDO Director</div>
                </td>
                <td class="signature-line">
                    <div class="signature-printed-name">{{ mb_strtoupper((string) $acknowledgedName, 'UTF-8') }}</div>
                    <div class="signature-caption-centered">(signature above printed name)</div>
                    <div class="signature-rule-below"></div>
                    <div class="signature-position">{{ $acknowledgedPosition }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
