<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8" />
    <title>Preventive Maintenance Checklist - {{ $checklist->control_number ?? 'PREVIEW' }}</title>
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
            font-size: 8.3pt;
            line-height: 1.12;
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
            font-size: 8pt;
            font-weight: 700;
        }

        .header-value {
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
        }

        .logo-box img {
            width: 13mm;
            display: block;
            margin: 0 auto;
        }

        .header-spacer {
            height: 13mm;
        }

        .info-line {
            width: 177mm;
            margin: 0 0 4mm 3mm;
            font-size: 8.4pt;
        }

        .underline {
            display: inline-block;
            min-height: 4.8mm;
            border-bottom: 1px solid #000;
            vertical-align: bottom;
        }

        .radio,
        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 5px;
            text-align: center;
            vertical-align: middle;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            font-weight: 700;
            line-height: 9px;
        }

        .checkbox {
            border: 1px solid #000;
        }

        .checkbox.on::after {
            content: "\2713";
            font-weight: 700;
        }

        .radio {
            border: 1px solid #000;
            border-radius: 50%;
        }

        .radio.on::after {
            content: "\2713";
            font-size: 10pt;
            font-weight: 700;
            position: relative;
            top: -2px;
        }

        .sub-note {
            font-size: 7.5pt;
            font-style: italic;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 2mm;
        }

        .check-item {
            margin-bottom: 2.8mm;
        }

        .line-block {
            display: block;
            width: 100%;
            min-height: 5mm;
            border-bottom: 1px solid #000;
            margin-bottom: 2.2mm;
            text-align: center;
            text-transform: uppercase;
        }

        .signature-stage {
            position: relative;
            width: 177mm;
            height: 28mm;
            margin-left: 3mm;
        }

        .signature-block {
            position: absolute;
            top: 0;
            width: 43mm;
            text-align: center;
        }

        .signature-findings { left: 8mm; }
        .signature-noted { left: 67mm; }
        .signature-recommendations { left: 126mm; }

        .signature-heading {
            height: 5mm;
            font-weight: 700;
            line-height: 4mm;
        }

        .signature-printed-name {
            width: 100%;
            height: 4.5mm;
            margin: 0 auto;
            padding-bottom: .7mm;
            border-bottom: 1px solid #000;
            font-weight: 700;
            line-height: 4mm;
            white-space: nowrap;
            text-align: center;
            text-transform: uppercase;
        }

        .signature-position {
            width: 100%;
            min-height: 4mm;
            margin: 1mm auto 0;
            line-height: 3.5mm;
            text-align: center;
        }

        .header-row-sm {
            height: 4.7mm;
        }

        .header-row-lg {
            height: 4.7mm;
        }

        .choice-row {
            height: 11.8mm;
        }

        .choice-row td { height: 11.8mm; }

        .pm-main td {
            padding-top: 1.5mm;
            padding-bottom: 1.2mm;
        }

        .pm-main { width: 177mm; margin-left: 3mm; }
        .checklist-body td { height: 92.5mm; }

        .findings-table {
            width: 177mm;
            margin-left: 3mm;
        }

        .pm-sign-slot {
            height: 9.2mm;
            vertical-align: bottom;
        }
    </style>
</head>

<body>
    @php
        $hw = is_array($checklist->hardware_checks ?? null)
            ? $checklist->hardware_checks
            : (json_decode($checklist->hardware_checks ?? '[]', true) ?? []);
        $sw = is_array($checklist->software_checks ?? null)
            ? $checklist->software_checks
            : (json_decode($checklist->software_checks ?? '[]', true) ?? []);
        $otherIct = is_array($checklist->other_ict_checks ?? null)
            ? $checklist->other_ict_checks
            : (json_decode($checklist->other_ict_checks ?? '[]', true) ?? []);
        $infoSys = is_array($checklist->info_systems_checks ?? null)
            ? $checklist->info_systems_checks
            : (json_decode($checklist->info_systems_checks ?? '[]', true) ?? []);

        $ictType = $checklist->ict_type ?? '';
        $computerType = $checklist->computer_type ?? '';
        $dateInspected = $checklist->date_inspected?->format('m/d/Y') ?? ($checklist->date_inspected ?? '');
        $otherIctLabel = $checklist->other_ict_type ?? $checklist->other_ict_specify ?? '';
        $isInformationSystem = !empty($checklist->info_system_name) || !empty($infoSys);
        $isOtherEquipment = $ictType === 'other_ict' && !$isInformationSystem;
        $findingsParts = preg_split('/\r\n|\r|\n/', trim((string) ($checklist->findings ?? ''))) ?: [];
        $recommendationParts = preg_split('/\r\n|\r|\n/', trim((string) ($checklist->recommendations ?? ''))) ?: [];
        $findingsParts = array_pad(array_filter($findingsParts, static fn ($value) => $value !== ''), 3, '');
        $recommendationParts = array_pad(array_filter($recommendationParts, static fn ($value) => $value !== ''), 3, '');
    @endphp

    <div class="page">
        <table class="header-meta">
            <tr class="header-row-lg">
                <td rowspan="6" class="logo-box middle center" style="width: 12%;">
                    <img src="{{ public_path('images/wvsu-janiuay-logo.png') }}" alt="WVSU Logo">
                </td>
                <td rowspan="3" class="header-title middle" style="width: 36%;">
                    PREVENTIVE MAINTENANCE<br>CHECKLIST
                </td>
                <td class="header-label" style="width: 22%;">Form No.</td>
                <td class="header-value" style="width: 30%;">WVSU-MIS-SOI-03-F02</td>
            </tr>
            <tr class="header-row-sm">
                <td class="header-label">Issue No.</td>
                <td class="header-value">1</td>
            </tr>
            <tr class="header-row-sm">
                <td class="header-label">Revision No.</td>
                <td class="header-value">1</td>
            </tr>
            <tr class="header-row-lg">
                <td rowspan="3" class="header-title middle" style="font-size: 10.4pt;">
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

        <div class="header-spacer"></div>

        <p class="info-line">
            Control No.: <span class="underline" style="width: 23mm;">{{ $checklist->control_number ?? '' }}</span>
            Property Number: <span class="underline" style="width: 58mm;">{{ $checklist->property_number ?? '' }}</span>
            Date Inspected: <span class="underline" style="width: 20mm;">{{ $dateInspected }}</span>
        </p>

        <p class="info-line" style="margin-bottom: 3mm;">
            End-user/System Owner: <span class="underline bold center" style="width: 56mm;">{{ mb_strtoupper((string) ($checklist->system_owner ?? ''), 'UTF-8') }}</span>
            Office/Unit/Department: <span class="underline bold center" style="width: 44mm;">{{ mb_strtoupper((string) ($checklist->office_department ?? ''), 'UTF-8') }}</span>
        </p>

        <table class="pm-main">
            <tr class="choice-row">
                <td colspan="2" class="center middle" style="width: 56%; border-right: 1px solid #000; padding-top: 5px; padding-bottom: 5px;">
                    <div><span class="radio {{ $ictType === 'computer' ? 'on' : '' }}"></span><span class="bold">Computer</span></div>
                    <div class="sub-note">(Mark check the appropriate computer type)</div>
                </td>
                <td colspan="2" class="center middle" style="width: 44%; padding-top: 5px; padding-bottom: 5px;">
                    <span class="radio {{ $ictType === 'other_ict' ? 'on' : '' }}"></span><span class="bold">For Other ICT Property</span>
                </td>
            </tr>
            <tr class="choice-row">
                <td colspan="2" class="middle" style="border-right: 1px solid #000;">
                    <span class="radio {{ $computerType === 'desktop' ? 'on' : '' }}"></span><span class="bold">Desktop</span>
                    <span style="display: inline-block; width: 18px;"></span>
                    <span class="radio {{ $computerType === 'laptop' ? 'on' : '' }}"></span><span class="bold">Laptop</span>
                </td>
                <td class="center middle">
                    <span class="radio {{ $isOtherEquipment ? 'on' : '' }}"></span>Other ICT Equipment
                </td>
                <td class="center middle">
                    <span class="radio {{ $isInformationSystem ? 'on' : '' }}"></span>Information System<br>and Other Software
                </td>
            </tr>
            <tr class="checklist-body">
                <td style="width: 27%;">
                    <div class="section-title">1. Hardware</div>
                    <div class="check-item"><span class="checkbox {{ collect(['complete_components', 'avr_ups', 'processor', 'motherboard', 'memory', 'video_card', 'storage', 'monitor', 'keyboard_mouse'])->contains(fn ($key) => in_array($key, $hw, true)) ? 'on' : '' }}"></span>Complete Components &amp; Peripherals</div>
                    <div class="check-item"><span class="checkbox {{ collect(['remove_dust', 'cleanup'])->contains(fn ($key) => in_array($key, $hw, true)) ? 'on' : '' }}"></span>Remove Dust Build-up</div>
                    <div class="check-item"><span class="checkbox {{ collect(['check_power', 'power_supply', 'system_fan'])->contains(fn ($key) => in_array($key, $hw, true)) ? 'on' : '' }}"></span>Check Power/Overheating Issues</div>
                    <div class="check-item"><span class="checkbox {{ collect(['check_network_connectivity', 'network_connectivity'])->contains(fn ($key) => in_array($key, $hw, true)) ? 'on' : '' }}"></span>Check/Optimize Network Connectivity</div>
                </td>
                <td style="width: 29%; border-right: 1px solid #000;">
                    <div class="section-title">2. Software</div>
                    <div class="check-item"><span class="checkbox {{ in_array('update_drivers', $sw, true) ? 'on' : '' }}"></span>Up-to-date Drivers</div>
                    <div class="check-item"><span class="checkbox {{ collect(['remove_unwanted_registry', 'remove_registry'])->contains(fn ($key) => in_array($key, $sw, true)) ? 'on' : '' }}"></span>Remove Unwanted Registry Files</div>
                    <div class="check-item"><span class="checkbox {{ in_array('defragment_disk', $sw, true) ? 'on' : '' }}"></span>Defragment Disk Drive</div>
                    <div class="check-item"><span class="checkbox {{ collect(['complete_productivity_tools', 'productivity_tools'])->contains(fn ($key) => in_array($key, $sw, true)) ? 'on' : '' }}"></span>Complete Productivity Tools</div>
                    <div class="check-item"><span class="checkbox {{ collect(['scan_for_viruses', 'scan_viruses', 'antivirus', 'scandisk'])->contains(fn ($key) => in_array($key, $sw, true)) ? 'on' : '' }}"></span>Scan for Viruses</div>
                    <div class="check-item"><span class="checkbox {{ in_array('optimize_startup', $sw, true) ? 'on' : '' }}"></span>Optimize Start-up</div>
                    <div class="check-item"><span class="checkbox {{ in_array('check_os_licenses', $sw, true) ? 'on' : '' }}"></span>Check OS and Other Licenses</div>
                    <div class="check-item"><span class="checkbox {{ collect(['delete_trash_junk', 'delete_junk', 'disk_cleanup'])->contains(fn ($key) => in_array($key, $sw, true)) ? 'on' : '' }}"></span>Delete Trash/Junk Files</div>
                    <div class="check-item"><span class="checkbox {{ collect(['update_security_tools', 'security_tools'])->contains(fn ($key) => in_array($key, $sw, true)) ? 'on' : '' }}"></span>Up-to-date Security Tools</div>
                </td>
                <td style="width: 22%;">
                    <div style="margin-bottom: 2mm;">Type: <span class="underline" style="width: 22mm;">{{ $otherIctLabel }}</span></div>
                    <div class="check-item"><span class="checkbox {{ in_array('software', $otherIct, true) ? 'on' : '' }}"></span>Software</div>
                    <div class="check-item"><span class="checkbox {{ in_array('hardware', $otherIct, true) ? 'on' : '' }}"></span>Hardware</div>
                    <div class="check-item"><span class="checkbox {{ in_array('network', $otherIct, true) ? 'on' : '' }}"></span>Network</div>
                    <div class="check-item"><span class="checkbox {{ in_array('communications', $otherIct, true) ? 'on' : '' }}"></span>Communications</div>
                </td>
                <td style="width: 22%;">
                    <div style="margin-bottom: 2mm;">Name: <span class="underline" style="width: 22mm;">{{ $checklist->info_system_name ?? '' }}</span></div>
                    <div class="check-item"><span class="checkbox {{ in_array('connectivity', $infoSys, true) ? 'on' : '' }}"></span>Connectivity</div>
                    <div class="check-item"><span class="checkbox {{ in_array('software_maintenance', $infoSys, true) ? 'on' : '' }}"></span>Software Maintenance</div>
                    <div class="check-item"><span class="checkbox {{ in_array('database_backup', $infoSys, true) ? 'on' : '' }}"></span>Database Backup</div>
                    <div class="check-item"><span class="checkbox {{ in_array('application_backup', $infoSys, true) ? 'on' : '' }}"></span>Application Backup</div>
                </td>
            </tr>
        </table>

        <div style="height: 8mm;"></div>

        <table class="findings-table" style="border: none;">
            <tr>
                <td style="border: none; padding: 0 10px 0 0;"><span class="bold">Findings:</span></td>
                <td style="border: none; padding: 0 0 0 10px;"><span class="bold">Recommendations:</span></td>
            </tr>
            <tr>
                <td style="border: none; padding: 4px 10px 0 0;">
                    @foreach(array_slice($findingsParts, 0, 3) as $line)
                        <span class="line-block">{{ mb_strtoupper((string) $line, 'UTF-8') }}</span>
                    @endforeach
                </td>
                <td style="border: none; padding: 4px 0 0 10px;">
                    @foreach(array_slice($recommendationParts, 0, 3) as $line)
                        <span class="line-block">{{ mb_strtoupper((string) $line, 'UTF-8') }}</span>
                    @endforeach
                </td>
            </tr>
        </table>

        <div style="height: 14mm;"></div>

        <div class="signature-stage">
            <div class="signature-block signature-findings">
                <div class="signature-heading">Inspected By:</div>
                <div class="pm-sign-slot">
                    @if($checklist->inspected_by_signature ?? false)
                        <img src="{{ $checklist->inspected_by_signature }}" alt="Inspected by signature" style="max-height: 36px;">
                    @endif
                </div>
                <div class="signature-printed-name">{{ mb_strtoupper((string) ($checklist->inspected_by_name ?: 'Technical Staff'), 'UTF-8') }}</div>
                <div class="signature-position">{{ $checklist->inspected_by_role ?: 'Technical Staff' }}</div>
            </div>
            <div class="signature-block signature-noted">
                <div class="signature-heading">Noted By:</div>
                <div class="pm-sign-slot">
                    @if($checklist->noted_by_signature ?? false)
                        <img src="{{ $checklist->noted_by_signature }}" alt="Noted by signature" style="max-height: 36px;">
                    @endif
                </div>
                <div class="signature-printed-name">{{ mb_strtoupper((string) ($checklist->noted_by_name ?: 'MIS Head/Unit Head'), 'UTF-8') }}</div>
                <div class="signature-position">{{ $checklist->noted_by_role ?: 'MIS Head/Unit Head' }}</div>
            </div>
            <div class="signature-block signature-recommendations">
                <div class="signature-heading">Acknowledgment:</div>
                <div class="pm-sign-slot">
                    @if($checklist->acknowledged_by_signature ?? false)
                        <img src="{{ $checklist->acknowledged_by_signature }}" alt="Acknowledgement signature" style="max-height: 36px;">
                    @endif
                </div>
                <div class="signature-printed-name">{{ mb_strtoupper((string) ($checklist->acknowledged_by_name ?: 'End-user/System Owner'), 'UTF-8') }}</div>
                <div class="signature-position">{{ $checklist->acknowledged_by_role ?: 'End-user/System Owner' }}</div>
            </div>
        </div>
    </div>
</body>

</html>
