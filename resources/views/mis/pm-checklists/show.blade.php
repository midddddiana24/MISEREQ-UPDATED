<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $checklist->control_number }}</title>
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

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 16px 0;
        }

        .item {
            padding: 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            border-left: 3px solid #1f6fa8;
        }

        .item strong {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .description-box {
            background: #f9fafb;
            padding: 14px;
            border-radius: 4px;
            border-left: 3px solid #1f6fa8;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
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

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            justify-content: center;
            flex-wrap: wrap;
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

        @media (max-width: 720px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
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
                        <h1>Preventive Maintenance Checklist Details</h1>
                        <small>Checklist Report · West Visayas State University</small>
                    </div>
                    <div class="detail-document-actions">
                        <a href="{{ route('pm-checklists.pdf', ['pmChecklist' => $checklist, 'v' => $checklist->updated_at?->timestamp]) }}" target="_blank" rel="noopener" class="button">Print PDF</a>
                        <button onclick="window.print()" class="button" type="button">Print</button>
                        <a href="{{ route('pm-checklists.index') }}" class="button secondary">&larr; Back to Checklists</a>
                    </div>
                </div>
            </div>

            @if (session('success')) <p style="color:#166534;margin-bottom:12px;">{{ session('success') }}</p> @endif

            <div style="margin-bottom: 28px;">
                <div class="item"><strong>Control Number</strong>{{ $checklist->control_number }}</div>
            </div>

            <div class="grid">
                <div class="item"><strong>System Owner</strong>{{ $checklist->system_owner }}</div>
                <div class="item"><strong>Date Inspected</strong>{{ optional($checklist->date_inspected)->format('M d, Y') }}</div>
                <div class="item"><strong>Office/Department</strong>{{ $checklist->office_department ?? 'N/A' }}</div>
                <div class="item"><strong>Status</strong>{{ ucfirst(str_replace('_', ' ', $checklist->status)) }}</div>
            </div>

            <div class="grid" style="margin-top:12px;">
                <div class="item"><strong>ICT Type</strong>{{ $checklist->ict_type === 'other_ict' ? ($checklist->other_ict_type ?? 'Other') : ($checklist->ict_type ? ucfirst($checklist->ict_type) : 'To be completed by MIS') }}</div>
                <div class="item"><strong>Computer Type</strong>{{ $checklist->computer_type ? ucfirst($checklist->computer_type) : 'N/A' }}</div>
                <div class="item"><strong>Other ICT - Specify</strong>{{ $checklist->other_ict_specify ?? 'N/A' }}</div>
            </div>

            @if($checklist->serviceRequest)
            <div class="item"><strong>Linked Service Request</strong><a href="{{ route('requests.show', $checklist->serviceRequest) }}">{{ $checklist->serviceRequest->control_number }}</a></div>
            @endif


            @if(!empty($checklist->other_ict_checks) && is_array($checklist->other_ict_checks))
            <div class="item" style="margin-top:12px;"><strong>Other ICT Checks</strong>
                <ul style="margin:8px 0 0 18px;">
                    @foreach($checklist->other_ict_checks as $c)
                    <li>{{ $c }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="item"><strong>Findings</strong>{{ $checklist->findings ?? 'N/A' }}</div>
        </section>
    </main>
</body>

</html>