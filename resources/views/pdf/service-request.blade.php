<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8" />
    <title>MIS Service Request Form - {{ $request->control_number ?? 'PREVIEW' }}</title>
    <style>
        /* ================= PRINT CONSISTENCY FIX ================= */
        @page {
            size: A4;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            width: 100%;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            line-height: 1;
        }

        .page {
            width: calc(100% - 1.4in);
            margin: 0;
            margin-left: 0.6in;
            margin-right: 0.8in;
            margin-top: 0.95in;
            margin-bottom: 1.2in;
            padding: 0;
            box-sizing: border-box;
            background: #fff;
            page-break-after: auto;
            overflow: hidden;
            display: block;
        }

        .page.has-following-page {
            page-break-after: always;
        }

        @media print {
            body {
                zoom: 100%;
                margin: 0;
                padding: 0;
            }

            .page {
                width: calc(100% - 1.4in);
                margin: 0;
                margin-left: 0.6in;
                margin-right: 0.8in;
                margin-top: 0.95in;
                margin-bottom: 1.2in;
                padding: 0;
                min-height: auto;
            }

            table {
                page-break-inside: avoid;
                width: 100%;
            }

            tr {
                page-break-inside: avoid;
            }

            .section {
                page-break-inside: avoid;
            }
        }

        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            margin: 0 3px 0 0;
            vertical-align: middle;
        }

        .checkbox.checked::after {
            content: "✓";
            font-weight: bold;
            font-size: 16pt;
            display: block;
            text-align: center;
            line-height: 10px;
            font-family: DejaVu Sans, sans-serif;
        }

        /* ================= BASE TABLE STYLES ================= */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin: 0;
            padding: 0;
            line-height: 1.1;
            box-sizing: border-box;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: top;
        }

        .no-bottom {
            border-bottom: none !important;
        }

        .no-top {
            border-top: none !important;
        }

        .no-left {
            border-left: none !important;
        }

        .no-right {
            border-right: none !important;
        }

        .center {
            text-align: center;
            font-size: 12px;
        }

        .center-cell {
            text-align: center;
            font-weight: bold;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 10px;
            margin: 2px 0;
        }

        .tiny {
            font-size: 10px;
        }

        /* ================= HEADER STYLES ================= */
        .header-section {
            margin-bottom: 4px;
        }

        .header-logo {
            text-align: center;
        }

        .header-logo img {
            width: 60px;
            display: block;
            margin: 0 auto;
        }

        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .header-info {
            font-size: 12px;
            padding: 1px;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-info td {
            border: 1px solid #000;
            padding: 2px;
            height: auto;
            vertical-align: middle;
        }

        .label-col {
            font-weight: bold;
            font-size: 9px;
        }

        .center-cell {
            text-align: center;
            font-size: 9px;
        }

        /* ================= REQUEST INFO SECTION ================= */
        .request-info-table td {
            padding: 4px 3px;
            height: auto;
        }

        .request-label {
            font-size: 9px;
            vertical-align: left;
            font-weight: normal;
            text-transform: none;
            text-align: left;
        }

        .request-value {
            font-size: 9px;
            padding-top: 6px;
            min-height: 12px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* ================= SECTION TITLES ================= */
        .section-title {
            background: #dcdcdc;
            text-align: center;
            padding: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-subtitle {
            background: #efefef;
            font-weight: 600;
            padding: 2px;
            font-size: 8px;
            text-align: center;
            text-transform: uppercase;
        }

        /* ================= SERVICES SECTION ================= */
        .services-column {
            padding: 3px;
            vertical-align: top;
            height: 150px;
        }

        .service-item {
            margin-bottom: 3px;
            font-size: 12px;
            line-height: 1.2;
        }

        .service-indent {
            margin-left: 8px;
            font-size: 10px;
        }

        /* ================= DESCRIPTION SECTION ================= */
        .description-cell {
            padding: 1px;
            vertical-align: top;
            height: 20px;
        }

        .description-label {
            font-size: 8pt;
            display: block;
            margin-left: 3px;
            font-weight: normal;
            text-transform: none;
        }

        .description-label-title {
            font-weight: bold;
            text-transform: none;
        }

        .description-instructions {
            font-weight: normal;
            text-transform: none;
        }

        .description-content {
            min-height: 20px;
            padding: 6px;
            font-size: 9pt;
            line-height: 1.4;
            margin-top: 4px;
            font-weight: normal;
            text-transform: none;
            text-align: center;
        }

        /* ================= MIS SECTION ================= */
        .footer-note {
            text-align: center;
            padding: 3px;
            font-size: 10px;
            margin: 3px 0;
        }

        .job-assignment td {
            padding: 3px;
            vertical-align: middle;
            font-size: 9px;
        }

        .job-center {
            min-height: 30px;
            font-size: 9pt;
            padding: 3px;
        }

        .job-row-2 td {
            height: 24px;
            font-size: 8px;
            padding: 2px;
            vertical-align: top;
        }

        .job-row-3 td {
            height: 30px;
            font-size: 8px;
            padding: 2px;
            vertical-align: top;
        }

        .bold1 {
            font-weight: normal;
            font-size: 10px;
        }

        /* ================= COLGROUP STYLES ================= */
        col.col-20 {
            width: 20%;
        }

        col.col-25 {
            width: 25%;
        }

        col.col-30 {
            width: 30%;
        }

        col.col-33 {
            width: 33.33%;
        }

        col.col-50 {
            width: 50%;
        }
    </style>
</head>

<body>

    <div class="page {{ $request->attachment ? 'has-following-page' : '' }}">
        @if(false)
        {{--
        The incident-report markup that was accidentally copied into this service-request
        template is intentionally disabled. The service-request form begins below.
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
                    background: #fff;
                    width: 100%;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Arial', 'Helvetica', sans-serif;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    line-height: 1;
                }

                .page {
                    width: calc(100% - 1.4in);
                    margin-left: 0.6in;
                    margin-right: 0.8in;
                    margin-top: 0.6in;
                    margin-bottom: 0.6in;
                    padding: 0;
                    box-sizing: border-box;
                    background: #fff;
                }

                @media print {
                    body {
                        zoom: 100%;
                        margin: 0;
                        padding: 0;
                    }

                    .page {
                        width: calc(100% - 1.4in);
                        margin: 0;
                        margin-left: 0.6in;
                        margin-right: 0.8in;
                        margin-top: 0.95in;
                        margin-bottom: 1.2in;
                        padding: 0;
                        min-height: auto;
                    }

                    table {
                        page-break-inside: avoid;
                        width: 100%;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    .section {
                        page-break-inside: avoid;
                    }
                }

                .checkbox {
                    display: inline-block;
                    width: 10px;
                    height: 10px;
                    border: 1px solid #000;
                    margin: 0 3px 0 0;
                    vertical-align: middle;
                }

                .checkbox.checked::after {
                    content: "✓";
                    font-weight: bold;
                    font-size: 16pt;
                    display: block;
                    text-align: center;
                    line-height: 10px;
                    font-family: DejaVu Sans, sans-serif;
                }

                /* ── Tables ── */
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 9pt;
                    margin: 0;
                    padding: 0;
                }

                td,
                th {
                    border: 1px solid #000;
                    padding: 3px 5px;
                    vertical-align: middle;
                }

                .no-border {
                    border: none !important;
                }

                .no-top {
                    border-top: none !important;
                }

                .no-bottom {
                    border-bottom: none !important;
                }

                .no-left {
                    border-left: none !important;
                }

                .no-right {
                    border-right: none !important;
                }

                .center {
                    text-align: center;
                    font-size: 12px;
                }

                .center-cell {
                    text-align: center;
                    font-weight: bold;
                }

                .bold {
                    font-weight: bold;
                }

                .small {
                    font-size: 10px;
                    margin: 2px 0;
                }

                .tiny {
                    font-size: 10px;
                }

                /* ── Header ── */
                .header-section {
                    margin-bottom: 4px;
                }

                .header-logo {
                    text-align: center;
                }

                .header-logo img {
                    width: 60px;
                    display: block;
                    margin: 0 auto;
                }

                .header-title {
                    text-align: center;
                    font-weight: bold;
                    font-size: 14px;
                }

                .header-info {
                    font-size: 12px;
                    padding: 1px;
                    width: 100%;
                    border-collapse: collapse;
                    table-layout: fixed;
                }

                .header-info td {
                    border: 1px solid #000;
                    padding: 2px;
                    height: auto;
                    vertical-align: middle;
                }

                .label-col {
                    font-weight: bold;
                    font-size: 9px;
                }

                .center-cell {
                    text-align: center;
                    font-size: 9px;
                }


                /* ── Section labels ── */
                .lbl {
                    font-weight: bold;
                    font-size: 9pt;
                }

                .val {
                    font-size: 9pt;
                }

                .italic-note {
                    font-style: italic;
                    font-size: 8.5pt;
                    text-align: center;
                }

                /* ── Signature underline ── */
                .sig-line {
                    border-bottom: 1px solid #000;
                    min-width: 140px;
                    display: inline-block;
                    margin-top: 30px;
                }

                .sig-role {
                    font-size: 8.5pt;
                }
            </style>
        </head>

        <body>

            <div class="page">

                <!-- HEADER -->
                <div class="header-section">
                    <table class="header-info">
                        <tr>
                            <td rowspan="6" class="center header-logo" style="width: 10%;">
                                <img src="{{ public_path('images/wvsu-logo.png') }}" alt="WVSU Logo">
                            </td>
                            <td rowspan="3" class="header-title" style="width: 45%; padding: 3px; font-weight: bold;">
                                ICT RELATED INCIDENT<br>REPORT FORM
                            </td>
                            <td class="label-col" style="width: 15%;">Form No.</td>
                            <td class="center-cell" style="width: 30%;">WVSU-MIS-SOI-05-F01</td>
                        </tr>
                        <tr>
                            <td class="label-col">Issue No.</td>
                            <td class="center-cell">1</td>
                        </tr>
                        <tr>
                            <td class="label-col">Revision No.</td>
                            <td class="center-cell">0</td>
                        </tr>
                        <tr>
                            <td rowspan="3" class="header-title" style="width: 50%; padding: 3px; font-weight: bold;">
                                WEST VISAYAS STATE<br>UNIVERSITY
                            </td>
                            <td class="label-col">Date of Effectivity</td>
                            <td class="center-cell">July 26, 2022</td>
                        </tr>
                        <tr>
                            <td class="label-col">Issued by</td>
                            <td class="center-cell">MIS</td>
                        </tr>
                        <tr>
                            <td class="label-col">Page No.</td>
                            <td class="center-cell">Page 1 of 1</td>
                        </tr>
                    </table>
                </div>

                <br>

                {{-- ========================================================= --}}
                {{-- DOCUMENT NUMBER                                             --}}
                {{-- ========================================================= --}}
                <table style="width:auto; border-collapse:collapse;">
                    <tr>
                        <td style="border:1px solid #000; padding:3px 6px; white-space:nowrap;">
                            <span class="lbl">Document Number:</span>
                        </td>
                        <td style="border:1px solid #000; padding:3px 6px; min-width:160px; width:160px;">
                            {{ $report->document_number ?? '' }}
                        </td>
                    </tr>
                </table>

                <br>

                {{-- ========================================================= --}}
                {{-- NATURE OF INCIDENT + DATE                                   --}}
                {{-- ========================================================= --}}
                @php
                $natures = is_array($report->nature_of_incident ?? null)
                ? $report->nature_of_incident
                : (json_decode($report->nature_of_incident ?? '[]', true) ?? []);
                @endphp

                <table>
                    <tr>
                        <td style="width:55%; border-bottom:1px solid #000;"><span class="val">Nature of Incident:</span></td>
                        <td style="width:45%; border-bottom:1px solid #000;"><span class="val">Date of Incident:</span>
                            &nbsp;&nbsp;
                            <span style="border-bottom:1px solid #000; display:inline-block; min-width:120px;">
                                {{ ($report->date_of_incident instanceof \DateTime || (is_object($report->date_of_incident ?? null) && method_exists($report->date_of_incident, 'format')))
                        ? $report->date_of_incident->format('m/d/Y')
                        : ($report->date_of_incident ?? '') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="no-top" style="padding: 8px 10px;">
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('loss_sensitive_data', $natures ?? []) ? 'checked' : '' }}"></span>
                                Loss of sensitive and valuable data
                            </div>
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('loss_valuable_hardware', $natures ?? []) ? 'checked' : '' }}"></span>
                                Loss of valuable hardware
                            </div>
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('breach_info_security', $natures ?? []) ? 'checked' : '' }}"></span>
                                Breach of information security/protocols
                            </div>
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('ict_hardware_software_theft', $natures ?? []) ? 'checked' : '' }}"></span>
                                ICT hardware or software theft, damage, or loss
                            </div>
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('virus_worm_dos', $natures ?? []) ? 'checked' : '' }}"></span>
                                Virus/Trojan/Worm infection/DOS on servers or WVSU owned applications
                            </div>
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('inappropriate_use', $natures ?? []) ? 'checked' : '' }}"></span>
                                Inappropriate use of WVSU ICT facilities or services
                            </div>
                            <div style="margin-bottom:6px;">
                                <span class="checkbox {{ in_array('others_nature', $natures ?? []) ? 'checked' : '' }}"></span>
                                Others. Please specify
                                <span style="border-bottom:1px solid #000; display:inline-block; min-width:200px; margin-left:4px;">{{ $report->nature_others ?? '' }}</span>
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- ========================================================= --}}
                {{-- DETAILS OF INCIDENT                                         --}}
                {{-- ========================================================= --}}
                <table style="margin-top:-1px;">
                    <tr>
                        <td class="lbl no-bottom">Details of Incident:</td>
                    </tr>
                    <tr>
                        <td class="no-top" style="height:70px; vertical-align:top; padding:6px;">
                            {{ $report->details_of_incident ?? '' }}
                        </td>
                    </tr>
                </table>

                {{-- ========================================================= --}}
                {{-- REPORTER INFO                                               --}}
                {{-- ========================================================= --}}
                <table style="margin-top:-1px;">
                    <colgroup>
                        <col style="width:30%">
                        <col style="width:40%">
                        <col style="width:10%">
                        <col style="width:20%">
                    </colgroup>
                    <tr>
                        <td class="lbl" style="width:30%;">Reported By:</td>
                        <td style="width:40%;">{{ $report->reported_by ?? '' }}</td>
                        <td class="lbl" style="width:10%;">Signature:</td>
                        <td style="width:20%;">{{ $report->reporter_signature ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>ID Number:</td>
                        <td>{{ $report->id_number ?? '' }}</td>
                        <td>Email:</td>
                        <td>{{ $report->reporter_email ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Department:</td>
                        <td>{{ $report->department ?? '' }}</td>
                        <td>Phone:</td>
                        <td>{{ $report->reporter_phone ?? '' }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="italic-note" style="background:#f9f9f9;">
                            <em>The following fields below must be filled by the MIS Staff</em>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding:0; border:none;">
                            <table>
                                <colgroup>
                                    <col style="width:70%">
                                    <col style="width:10%">
                                    <col style="width:20%">
                                </colgroup>
                                <tr>
                                    <td class="lbl" style="width:70%;">Received by</td>
                                    <td class="lbl" style="width:10%; text-align:center;">Date</td>
                                    <td class="lbl" style="width:20%; text-align:center;">Time</td>
                                </tr>
                                <tr>
                                    <td style="width:70%; height:34px; text-align:center; vertical-align:middle;">
                                        @if($report->received_by)
                                            <strong style="display:inline-block; min-width:170px; padding:0 4px 2px; border-bottom:1px solid #000;">{{ mb_strtoupper($report->received_by, 'UTF-8') }}</strong><br>
                                            <span>MIS STAFF</span>
                                        @endif
                                    </td>
                                    <td style="width:10%; height:34px; text-align:center; vertical-align:middle;">
                                        {{ ($report->received_date instanceof \DateTime || (is_object($report->received_date ?? null) && method_exists($report->received_date, 'format')))
                                            ? $report->received_date->format('m/d/Y')
                                            : ($report->received_date ?? '') }}
                                    </td>
                                    <td style="width:20%; height:34px; text-align:center; vertical-align:middle;">{{ $report->received_time ?? '' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <br>

                {{-- ========================================================= --}}
                {{-- ACTION PLAN                                                  --}}
                {{-- ========================================================= --}}
                <table>
                    <tr>
                        <td class="lbl no-bottom">Action Plan:</td>
                    </tr>
                    <tr>
                        <td class="no-top" style="height:70px; vertical-align:middle; text-align:center; padding:6px;">
                            {{ mb_strtoupper((string) ($report->action_plan ?? ''), 'UTF-8') }}
                        </td>
                    </tr>
                </table>

                {{-- ========================================================= --}}
                {{-- REMARKS                                                      --}}
                {{-- ========================================================= --}}
                <table style="margin-top:-1px;">
                    <tr>
                        <td class="lbl no-bottom">Remarks:</td>
                    </tr>
                    <tr>
                        <td class="no-top" style="height:70px; vertical-align:middle; text-align:center; padding:6px;">
                            {{ mb_strtoupper((string) ($report->remarks ?? ''), 'UTF-8') }}
                        </td>
                    </tr>
                </table>

                <br>

                {{-- ========================================================= --}}
                {{-- SIGNATORY NOTE + SIGNATURES                                 --}}
                {{-- ========================================================= --}}
                <p style="font-style:italic; font-size:8.5pt; margin:4px 0;">
                    *More than one signatory can be added if there are other persons involved in the action plan
                </p>

                <table style="border:none;">
                    <colgroup>
                        <col style="width:50%">
                        <col style="width:50%">
                    </colgroup>
                    <tr>
                        <td style="border:none; padding:2px 0;">
                            <span class="lbl">Noted by:</span>
                        </td>
                        <td style="border:none; padding:2px 0;">
                            <span class="lbl">Acknowledgement:</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:0; height:50px; vertical-align:bottom;">
                            {{-- signature area --}}
                            @if($report->noted_by_signature ?? false)
                            <img src="{{ $report->noted_by_signature }}" style="max-height:40px; display:block;">
                            @endif
                        </td>
                        <td style="border:none; padding:0; height:50px; vertical-align:bottom;">
                            @if($report->acknowledged_by_signature ?? false)
                            <img src="{{ $report->acknowledged_by_signature }}" style="max-height:40px; display:block;">
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border:none; border-top:1px solid #000; padding:2px 0; width:50%;">
                            <div style="font-size:8.5pt; font-style:italic;">(signature above printed name)</div>
                            <div class="sig-role">{{ $report->noted_by_name ?? 'MIS Head/Unit Head' }}</div>
                        </td>
                        <td style="border:none; border-top:1px solid #000; padding:2px 0; width:50%;">
                            <div style="font-size:8.5pt; font-style:italic;">(signature above printed name)</div>
                            <div class="sig-role">{{ $report->acknowledged_by_name ?? 'End-User' }}</div>
                        </td>
                    </tr>
                </table>

            </div>
        </body>

        </html>
        --}}
        @endif
        <!-- HEADER -->
        <div class="header-section">
            <table class="header-info">
                <tr>
                    <td rowspan="6" class="center header-logo" style="width: 10%;">
                        <img src="{{ public_path('images/wvsu-janiuay-logo.png') }}" alt="WVSU Logo">
                    </td>
                    <td rowspan="3" class="header-title" style="width: 45%; padding: 3px; font-weight: bold;">
                        MIS SERVICE REQUEST FORM
                    </td>
                    <td class="label-col" style="width: 15%;">Form No.</td>
                    <td class="center-cell" style="width: 30%;">WVSU-MIS-SOI-01-F01</td>
                </tr>
                <tr>
                    <td class="label-col">Issue No.</td>
                    <td class="center-cell">1</td>
                </tr>
                <tr>
                    <td class="label-col">Revision No.</td>
                    <td class="center-cell">1</td>
                </tr>
                <tr>
                    <td rowspan="3" class="header-title" style="width: 50%; padding: 3px; font-weight: bold;">
                        WEST VISAYAS STATE<br>UNIVERSITY
                    </td>
                    <td class="label-col">Date of Effectivity</td>
                    <td class="center-cell">July 26, 2022</td>
                </tr>
                <tr>
                    <td class="label-col">Issued by</td>
                    <td class="center-cell">MIS</td>
                </tr>
                <tr>
                    <td class="label-col">Page No.</td>
                    <td class="center-cell">Page 1 of 1</td>
                </tr>
            </table>
        </div>
        <br>

        <!-- REQUEST INFO -->
        <table class="request-info-table">
            <colgroup>
                <col style="width: 25%">
                <col style="width: 25%">
                <col style="width: 25%">
                <col style="width: 25%">
            </colgroup>
            <tr>
                <td class="no-bottom"><span class="request-label">Name and Signature of Requestor:</span></td>
                <td class="no-bottom"><span class="request-label">Designation:</span></td>
                <td class="no-bottom"><span class="request-label">Office/Unit:</span></td>
                <td class="no-bottom"><span class="request-label">*Control Number:</span></td>
            </tr>
            <tr>
                <td class="no-top request-value">{{ $request->requester_name ?? '' }}</td>
                <td class="no-top request-value">{{ $request->requester_designation ?? '' }}</td>
                <td class="no-top request-value">{{ $request->requester_office ?? '' }}</td>
                <td class="no-top request-value">{{ $request->control_number ?? '' }}</td>
            </tr>
            <tr>
                <td class="no-bottom"><span class="request-label">Name and Signature of Unit/Department Head:</span></td>
                <td class="no-bottom"><span class="request-label">Phone/Local No:</span></td>
                <td class="no-bottom"><span class="request-label">*Date of Request:</span></td>
                <td class="no-bottom"><span class="request-label">*Time of Request:</span></td>
            </tr>
            <tr>
                <td class="no-top request-value">{{ $request->unit_head_name ?? '' }}</td>
                <td class="no-top request-value">{{ $request->phone ?? '' }}</td>
                <td class="no-top request-value">{{ ($request->date_of_request instanceof \DateTime || method_exists($request->date_of_request, 'format')) ? $request->date_of_request->format('m/d/Y') : $request->date_of_request }}</td>
                <td class="no-top request-value">{{ $request->time_of_request ?? '' }}</td>
            </tr>
        </table>
        <p class="small"><i class="tiny">(*) to be filled up by MIS Staff</i></p>

        <!-- SERVICES -->
        @php
        $networkServices = $request->network_services ?? [];
        $ictServices = $request->ict_equipment_services ?? [];
        $misServices = $request->mis_software_services ?? [];
        $webServices = $request->web_admin_services ?? [];

        if (!is_array($networkServices)) {
        $networkServices = (is_string($networkServices) && !empty($networkServices)) ? (json_decode($networkServices, true) ?? []) : [];
        }
        if (!is_array($ictServices)) {
        $ictServices = (is_string($ictServices) && !empty($ictServices)) ? (json_decode($ictServices, true) ?? []) : [];
        }
        if (!is_array($misServices)) {
        $misServices = (is_string($misServices) && !empty($misServices)) ? (json_decode($misServices, true) ?? []) : [];
        }
        if (!is_array($webServices)) {
        $webServices = (is_string($webServices) && !empty($webServices)) ? (json_decode($webServices, true) ?? []) : [];
        }
        @endphp

        <table class="services-table">
            <colgroup>
                <col style="width: 50%">
                <col style="width: 50%">
            </colgroup>
            <tr>
                <td class="section-title">NETWORK / TELEPHONE SERVICES</td>
                <td class="section-title">ICT EQUIPMENT SERVICES</td>
            </tr>
            <tr>
                <td class="services-column">
                    <div class="service-item"><span class="checkbox {{ in_array('new_network_computer', $request->services ?? []) ? 'checked' : '' }}"></span>New Network for Computer</div>
                    <div class="service-item"><span class="checkbox {{ in_array('new_network_printer', $request->services ?? []) ? 'checked' : '' }}"></span>New Network for Printer</div>
                    <div class="service-item"><span class="checkbox {{ in_array('new_cabling', $request->services ?? []) ? 'checked' : '' }}"></span>New Cabling for Network (Layout plan required)</div>
                    <div class="service-item"><span class="checkbox {{ in_array('new_telephone_line', $request->services ?? []) ? 'checked' : '' }}"></span>New Telephone Line</div>
                    <div class="service-item"><span class="checkbox {{ in_array('new_cabling_telephone', $request->services ?? []) ? 'checked' : '' }}"></span>Relocation of Telephone</div>
                    <div class="service-item"><span class="checkbox {{ in_array('move_new_location', $request->services ?? []) ? 'checked' : '' }}"></span>Move to New Location</div>
                    <div class="service-indent">Specify Location: _________________________________</div>
                    <div class="service-item"><span class="checkbox {{ in_array('others_network', $request->services ?? []) ? 'checked' : '' }}"></span>Others</div>
                    <div class="service-indent">Specify: __________________________________________</div>
                </td>
                <td class="services-column">
                    <div class="service-item"><span class="checkbox {{ in_array('troubleshoot_pc', $request->services ?? []) ? 'checked' : '' }}"></span>Troubleshoot PC/Laptop</div>
                    <div class="service-item"><span class="checkbox {{ in_array('troubleshoot_printer', $request->services ?? []) ? 'checked' : '' }}"></span>Troubleshoot Printer</div>
                    <div class="service-item"><span class="checkbox {{ in_array('network_connection', $request->services ?? []) ? 'checked' : '' }}"></span>Troubleshoot Telephone / Internet Connection</div>
                    <div class="service-item"><span class="checkbox {{ in_array('software_installation', $request->services ?? []) ? 'checked' : '' }}"></span>Software Installation</div>
                    <div class="service-item"><span class="checkbox {{ count(array_intersect(['hardware_install', 'hardware_installation'], $request->services ?? [])) ? 'checked' : '' }}"></span>Hardware Installation</div>
                    <div class="service-item"><span class="checkbox {{ in_array('inspection_equipment', $request->services ?? []) ? 'checked' : '' }}"></span>Inspection of Delivered IT-Related Equipment</div>
                    <div class="service-item"><span class="checkbox {{ count(array_intersect(['others_ict', 'ict_other'], $request->services ?? [])) ? 'checked' : '' }}"></span>Others</div>
                    <div class="service-indent">Specify: __________________________________________</div>
                    <div style="margin-top: 6px;">Property No: {{ $request->ict_property_no ?? '___________________________________' }}</div>
                </td>
            </tr>

            <tr>
                <td class="section-title">MIS SOFTWARE / APPLICATION SERVICES</td>
                <td class="section-title">WEB ADMINISTRATOR SERVICES</td>
            </tr>
            <tr>
                <td class="services-column">
                    <div class="service-item">Name of Application: _________________________________</div>
                    <div class="service-item"><span class="checkbox {{ count(array_intersect(['account_creation', 'new_user_account'], $request->services ?? [])) ? 'checked' : '' }}"></span>New User Account &nbsp; <span class="checkbox {{ count(array_intersect(['password_reset', 'reset_password'], $request->services ?? [])) ? 'checked' : '' }}"></span>Reset Password</div>
                    <div class="service-item"><span class="checkbox {{ in_array('software_problem', $request->services ?? []) ? 'checked' : '' }}"></span>Software Problem &nbsp; <span class="checkbox {{ in_array('data_repair', $request->services ?? []) ? 'checked' : '' }}"></span>Data Repair</div>
                    <div class="service-item"><span class="checkbox {{ in_array('copy_data', $request->services ?? []) ? 'checked' : '' }}"></span>Request for Copy of Data</div>
                    <div class="service-item"><span class="checkbox {{ count(array_intersect(['system_access', 'grant_privilege'], $request->services ?? [])) ? 'checked' : '' }}"></span>Grant Privilege / Access</div>
                    <div class="service-item"><span class="checkbox {{ count(array_intersect(['others_mis', 'mis_other'], $request->services ?? [])) ? 'checked' : '' }}"></span>Others</div>
                    <div class="service-indent">Specify: __________________________________________</div>
                </td>
                <td class="services-column">
                    <div class="service-item"><span class="checkbox {{ in_array('new_email_account', $request->services ?? []) ? 'checked' : '' }}"></span>New WVSU Email Account</div>
                    <div class="service-item"><span class="checkbox {{ in_array('reset_email_password', $request->services ?? []) ? 'checked' : '' }}"></span>Reset Email Account Password</div>
                    <div class="service-item"><span class="checkbox {{ in_array('logo_graphic', $request->services ?? []) ? 'checked' : '' }}"></span>Tarp / Graphic Design</div>
                    <div class="service-item"><span class="checkbox {{ in_array('photo_video', $request->services ?? []) ? 'checked' : '' }}"></span>Photo / Video Documentation</div>
                    <div class="service-item"><span class="checkbox {{ in_array('website_update', $request->services ?? []) ? 'checked' : '' }}"></span>Website Update / Article Posting</div>
                    <div class="service-item"><span class="checkbox {{ in_array('multimedia_files', $request->services ?? []) ? 'checked' : '' }}"></span>Request for Copy of Multimedia Files</div>
                    <div class="service-item"><span class="checkbox {{ in_array('web_other', $request->services ?? []) ? 'checked' : '' }}"></span>Others</div>
                    <div class="service-indent">Specify: __________________________________________</div>
                </td>
            </tr>
            </td>
            </tr>

            <tr>
                <td colspan="2" class="description-cell">
                    <span class="description-label"><span class="description-label-title">Description of Request</span> <i class="description-instructions">(write detailed information of the request. If any documents are included, please attach to this form. Use separate sheet if necessary.)</i></span>
                    <div class="description-content">{{ $request->description ?? '' }}</div>
                </td>
            </tr>
        </table>

        <!-- MIS PERSONNEL SECTION -->
        <div class="footer-note">******************** TO BE FILLED UP BY MIS PERSONNEL ONLY ********************</div>

        <table class="job-assignment">
            <colgroup>
                <col style="width: 33.33%">
                <col style="width: 33.33%">
                <col style="width: 33.34%">
            </colgroup>

            <!-- Row 1: Job Assignment & MIS Head Signature -->
            <tr>
                <td colspan="2" class="job-center">
                    <div style="text-align: center; font-weight: bold; margin-bottom: 4px; font-size: 9px;">Job Assignment</div>
                    <div style="text-align: left; font-size: 9px;">
                        Assigned To:<br>
                        @php
                        $assignedTechnician = $request->assignedTechnician;
                        $technicianRole = $assignedTechnician
                            ? \App\Models\Technician::normalizeJobAssignment($assignedTechnician->job_assignment, $assignedTechnician->specialization)
                            : null;
                        @endphp
                        <span class="checkbox {{ $technicianRole === 'Network Administrator' ? 'checked' : '' }}"></span>Network Administrator &nbsp;
                        <span class="checkbox {{ $technicianRole === 'Technical Staff' ? 'checked' : '' }}"></span>Technical Staff
                        <span class="checkbox {{ $technicianRole === 'Web Administrator' ? 'checked' : '' }}"></span>Web Administrator
                        <span class="checkbox {{ $technicianRole === 'Computer Programmer' ? 'checked' : '' }}"></span>Computer Programmer
                    </div>
                </td>
                <td class="mis-head" style="vertical-align: top; font-size: 9px; padding: 2px;">
                    <span style="font-weight: normal; display: block; margin-bottom: 2px; font-size: 9px; text-align: left;">Name & Signature of MIS Director</span>
                    <div style="margin-top: 8px; font-weight: bold; font-size: 9px; text-align: center; height: 15px;">MIS</div>
                </td>
            </tr>

            <!-- Row 2: Response Details -->
            <tr class="job-row-2">
                <td>
                    <span class="bold1" style="text-align: left; display: block;">Responded By:</span><br>
                    <span style="font-weight: bold; text-align: center; display: block; text-transform: uppercase;">
                        @if($request->assignedTechnician)
                        {{ $request->assignedTechnician->full_name ?? '' }}
                        @else
                        {{ $request->responded_by ?? '' }}
                        @endif
                    </span>
                </td>
                <td>
                    <span class="bold1" style="text-align: left; display: block;">PRIR No.(for Pre-repair):</span><br>
                    <span style="font-weight: bold; text-align: center; display: block; text-transform: uppercase;">
                        {{ $request->prr_number ?? '' }}
                    </span>
                </td>
                <td>
                    <span class="bold1" style="text-align: left; display: block;">Date & Time Responded:</span><br>
                    <span style="font-weight: bold; text-align: center; display: block; text-transform: uppercase;">
                        @if($request->date_responded)
                        {{ ($request->date_responded instanceof \DateTime || method_exists($request->date_responded, 'format')) ? $request->date_responded->format('m/d/Y h:i A') : $request->date_responded }}
                        @else
                        <span style="color: #999; font-size: 10px; text-transform: none;">—</span>
                        @endif
                    </span>
                </td>
            </tr>

            <!-- Row 3: Major Repair & Completion -->
            <tr class="job-row-3">
                <td>&nbsp;</td>
                <td class="center" style="vertical-align:middle; font-size: 12px;">
                    <span class="checkbox {{ $request->major_repair ? 'checked' : '' }}"></span>
                    Mark (✓) for Major Repair
                </td>
                <td>
                    <span class="bold1" style="text-align: left; display: block;">Date & Time Completed:</span><br>
                    <span style="font-weight: bold; text-align: center; display: block; text-transform: uppercase;">
                        @if($request->date_completed)
                        {{ ($request->date_completed instanceof \DateTime || method_exists($request->date_completed, 'format')) ? $request->date_completed->format('m/d/Y h:i A') : $request->date_completed }}
                        @else
                        <span style="color: #999; font-size: 10px; text-transform: none;">Not Yet Completed</span>
                        @endif
                    </span>
                </td>
            </tr>
        </table>

        <!-- END USER ACCEPTANCE SECTION -->
        <div class="footer-note">******************** END-USER ACCEPTANCE ********************</div>

        <table class="job-assignment">
            <colgroup>
                <col style="width: 50%">
                <col style="width: 50%">
            </colgroup>

            <!-- Comments -->
            <tr>
                <td colspan="2" style="padding: 3px; vertical-align: top;">
                    <span class="bold">Comments / Recommendations:</span><br>
                    <div style="margin-top: 3px; min-height: 30px; font-size: 8px; line-height: 1.3; text-align: center; text-transform: uppercase; font-weight: normal;">{{ mb_strtoupper((string) ($request->comments ?? ''), 'UTF-8') }}</div>
                </td>
            </tr>

            <!-- Final Signatures -->
            <tr>
                <td style="padding: 3px; vertical-align: top;">
                    <span class="bold">Signature of End-user:</span><br>
                    <div class="signature-box" style="margin-top: 10px; height:20px;">{{ $request->end_user_signature ?? '' }}</div>
                </td>
                <td style="padding: 3px; vertical-align: top;">
                    <span class="bold">Date:</span><br>
                    <div class="signature-box" style="margin-top: 10px; text-align: center; font-weight: bold;">{{ $request->acceptance_date ? (($request->acceptance_date instanceof \DateTime || method_exists($request->acceptance_date, 'format')) ? $request->acceptance_date->format('m/d/Y') : $request->acceptance_date) : '' }}</div>
                </td>
            </tr>
        </table>

    </div>

    <!-- ATTACHMENT PAGE -->
    @if($request->attachment)
    <div class="page">
        <div class="header-section">
            <table class="header-info">
                <tr>
                    <td class="label-col" style="width: 50%;">Attachment Document(s)</td>
                    <td class="label-col" style="text-align: right;">Control Number: {{ $request->control_number ?? '' }}</td>
                </tr>
            </table>
        </div>
        <br>

        <table class="job-assignment" style="margin-top: 10px;">
            <colgroup>
                <col style="width: 100%">
            </colgroup>
            <tr>
                <td style="padding: 10px; vertical-align: top;">
                    <div style="margin-bottom: 15px;">
                        <span class="bold" style="font-size: 11px;">REQUEST CONTROL NUMBER:</span>
                        <div style="font-size: 10px; margin-top: 3px;">{{ $request->control_number ?? '' }}</div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <span class="bold" style="font-size: 11px;">REQUESTER NAME:</span>
                        <div style="font-size: 10px; margin-top: 3px;">{{ $request->requester_name ?? '' }}</div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <span class="bold" style="font-size: 11px;">ATTACHED FILE(S):</span>
                        <div style="font-size: 10px; margin-top: 3px;">{{ $request->attachment }}</div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <span class="bold" style="font-size: 11px;">SUBMISSION DATE:</span>
                        <div style="font-size: 10px; margin-top: 3px;">{{ $request->created_at ? (($request->created_at instanceof \DateTime || method_exists($request->created_at, 'format')) ? $request->created_at->format('m/d/Y H:i A') : $request->created_at) : '' }}</div>
                    </div>

                    <!-- Display Image/File Preview -->
                    @php
                    $attachmentPath = public_path('storage/service-requests/' . $request->attachment);
                    $fileExtension = strtolower(pathinfo($request->attachment, PATHINFO_EXTENSION));
                    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']);
                    $imageData = null;

                    // Create base64 encoded image for PDF embedding
                    if($isImage && file_exists($attachmentPath)) {
                    $imageContent = file_get_contents($attachmentPath);
                    $mimeType = 'image/jpeg';
                    if($fileExtension === 'png') {
                    $mimeType = 'image/png';
                    } elseif($fileExtension === 'gif') {
                    $mimeType = 'image/gif';
                    }
                    $imageData = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                    }
                    @endphp

                    @if($isImage && $imageData)
                    <div style="margin-top: 20px; border: 1px solid #000; padding: 10px; text-align: center;">
                        <img src="{{ $imageData }}" style="max-width: 100%; max-height: 500px; display: block; margin: 0 auto;">
                    </div>
                    @else
                    <div style="margin-top: 20px; border: 1px solid #ccc; padding: 15px; background-color: #f5f5f5; text-align: center;">
                        <div style="font-size: 10px; color: #666;">
                            <strong>File Type:</strong> {{ strtoupper($fileExtension) }}<br>
                            <em>Document file attached ({{ $fileExtension }})</em>
                        </div>
                    </div>
                    @endif

                    <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #666;">
                        <i>This page contains the attached file information for request {{ $request->control_number ?? 'N/A' }}</i>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endif

</body>

</html>
