<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Incident Reports</title>
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

        .toolbar-pills,
        .search-form {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .search-input {
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            min-width: 280px;
            flex: 1 1 280px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 14px;
        }

        table thead {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        table tr:hover {
            background: #f9fafb;
        }

        .empty {
            margin-top: 16px;
            color: #6b7280;
            padding: 20px;
            text-align: center;
        }

        a {
            color: #1f6fa8;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
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

            .toolbar-pills .button,
            .search-form .button {
                width: 100%;
            }

            .search-input {
                min-width: 0;
                width: 100%;
            }

            table,
            thead,
            tbody,
            tr,
            td {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                padding: 10px 0;
                border-bottom: 1px solid #e5e7eb;
            }

            td {
                border: 0;
                padding: 5px 0;
            }

            td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 3px;
                color: #6b7280;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
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
                        <h1>Your Reports</h1>
                        <small>ICT Incident Reports · West Visayas State University</small>
                    </div>
                    <a href="{{ route('incident-reports.create') }}" class="button">+ New ICT Incident Report</a>
                </div>
            </div>
            <div class="toolbar-pills">
                <a href="{{ route('incident-reports.index', ['search' => request('search')]) }}" class="button secondary">All</a>
                <a href="{{ route('incident-reports.index', ['status' => 'open', 'search' => request('search')]) }}" class="button secondary">Open</a>
                <a href="{{ route('incident-reports.index', ['status' => 'resolved', 'search' => request('search')]) }}" class="button secondary">Resolved</a>
                <a href="{{ route('incident-reports.index', ['status' => 'closed', 'search' => request('search')]) }}" class="button secondary">Closed</a>
            </div>
            <form method="GET" class="search-form record-search-form">
                @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" class="search-input" name="search" value="{{ request('search') }}" placeholder="Search document number, department, or details">
                <button type="submit" class="button">Search</button>
            </form>
            @if (session('success')) <p style="margin-top:12px;color:#166534;">{{ session('success') }}</p> @endif
            @if ($reports->count())
            <p class="client-record-note" id="incidentRecordHint">Scroll inside the records area to review more reports.</p>
            <div class="client-record-scroll" tabindex="0" role="region" aria-label="ICT incident report records" aria-describedby="incidentRecordHint">
                <table class="client-record-table">
                    <thead>
                        <tr>
                            <th>Document No.</th>
                            <th>Incident Date</th>
                            <th>Department</th>
                            <th>Connected Records</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                        <tr>
                            <td data-label="Document No.">{{ $report->document_number }}</td>
                            <td data-label="Incident Date">{{ optional($report->date_of_incident)->format('M d, Y') }}</td>
                            <td data-label="Department">{{ $report->department }}</td>
                            <td data-label="Connected Records">
                                @if($report->serviceRequest)
                                <a href="{{ route('requests.show', $report->serviceRequest) }}">{{ $report->serviceRequest->control_number }}</a>
                                @else
                                <span>Standalone incident</span>
                                @endif
                            </td>
                            <td data-label="Status">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</td>
                            <td data-label="Actions">
                                <div class="record-actions"><a class="record-action primary" href="{{ route('incident-reports.show', $report) }}">View details</a><a class="record-action" href="{{ route('incident-reports.pdf', ['incidentReport' => $report, 'v' => $report->updated_at?->timestamp]) }}" target="_blank" rel="noopener">View PDF</a></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $reports->links() }}</div>
            @else
            <p class="empty">No ICT incident reports submitted yet.</p>
            @endif
        </section>
    </main>
</body>

</html>