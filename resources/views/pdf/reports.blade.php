<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 0.75in 0.75in 0.75in 0.75in;
        }

        html,
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            color: #2c3e50;
            background-color: #fff;
            width: 100%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            line-height: 1.5;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #D1884F;
        }

        .header h1 {
            color: #D1884F;
            font-size: 20px;
            margin-bottom: 4px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            color: #666;
            font-size: 10px;
            margin: 2px 0;
            font-weight: 400;
        }

        .report-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 12px;
            padding: 8px 0;
            font-size: 10px;
        }

        .report-meta-item {
            text-align: left;
        }

        .report-meta-label {
            font-weight: 600;
            color: #2c3e50;
            display: block;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .report-meta-value {
            color: #555;
            font-size: 10px;
            line-height: 1.3;
        }

        .summary-section {
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 6px;
            border-bottom: 2px solid #D1884F;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 0;
        }

        .stat-box {
            padding: 8px 6px;
            background: #f8f8f8;
            text-align: center;
            border-left: 3px solid #D1884F;
        }

        .stat-box h4 {
            color: #D1884F;
            font-size: 9px;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .stat-box p {
            color: #2c3e50;
            font-size: 18px;
            font-weight: 700;
        }

        .table-container {
            margin-top: 8px;
            overflow-x: visible;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 0;
        }

        table thead {
            background: #D1884F;
            color: white;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            letter-spacing: 0.3px;
            border: none;
        }

        table td {
            padding: 7px 6px;
            border-bottom: 1px solid #e8e8e8;
            word-break: break-word;
            color: #333;
            font-size: 9px;
        }

        table tbody tr {
            background: white;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .control-number {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 10px;
            color: #D1884F;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 2px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }

        .status-processing {
            background-color: #CCE5FF;
            color: #004085;
        }

        .status-completed {
            background-color: #D4EDDA;
            color: #155724;
        }

        .status-cancelled {
            background-color: #F8D7DA;
            color: #721C24;
        }

        .status-rejected {
            background-color: #F5D5DA;
            color: #7A1A1E;
        }

        .no-data {
            text-align: center;
            padding: 25px 20px;
            color: #666;
            font-size: 12px;
            background: #f8f8f8;
            margin: 20px 0;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #D1884F;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .footer p {
            margin: 2px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .header {
                page-break-after: avoid;
                margin-bottom: 25px;
            }

            .section-title {
                page-break-after: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
            }

            @page {
                margin: 0.75in;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 SERVICE REQUESTS REPORT</h1>
            <p>MIS Service Request Management System</p>
            <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        </div>

        <!-- Report Metadata -->
        <div class="report-meta">
            <div class="report-meta-item">
                <span class="report-meta-label">Report Period:</span>
                <span class="report-meta-value">
                    @if ($stats['date_from'] !== 'N/A')
                    {{ \Carbon\Carbon::parse($stats['date_from'])->format('M d, Y') }} to {{ \Carbon\Carbon::parse($stats['date_to'])->format('M d, Y') }}
                    @else
                    All Records
                    @endif
                </span>
            </div>
            <div class="report-meta-item" style="text-align: center;">
                <span class="report-meta-label">Status Filter:</span>
                <span class="report-meta-value">{{ ucfirst($stats['status_filter'] === 'All' ? 'All Statuses' : $stats['status_filter']) }}</span>
            </div>
            <div class="report-meta-item" style="text-align: right;">
                <span class="report-meta-label">Total Records:</span>
                <span class="report-meta-value"><strong>{{ $requests->count() }}</strong></span>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="section-title">📈 Summary Statistics</div>
            <div class="summary-stats">
                <div class="stat-box">
                    <h4>Total</h4>
                    <p>{{ $stats['total_requests'] }}</p>
                </div>
                <div class="stat-box">
                    <h4>Pending</h4>
                    <p>{{ $stats['pending'] }}</p>
                </div>
                <div class="stat-box">
                    <h4>Processing</h4>
                    <p>{{ $stats['processing'] }}</p>
                </div>
                <div class="stat-box">
                    <h4>Completed</h4>
                    <p>{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>

        <!-- Detailed Records Section -->
        <div class="summary-section">
            <div class="section-title">📋 Detailed Service Requests</div>

            <div class="table-container">
                @if ($requests->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 11%;">Control #</th>
                            <th style="width: 14%;">Requester</th>
                            <th style="width: 10%;">Designation</th>
                            <th style="width: 13%;">Office</th>
                            <th style="width: 22%;">Service Type / Description</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                        <tr>
                            <td><span class="control-number">{{ $request->control_number }}</span></td>
                            <td>{{ $request->requester_name ?? 'N/A' }}</td>
                            <td>{{ Str::limit($request->requester_designation ?? 'N/A', 10, '...') }}</td>
                            <td>{{ Str::limit($request->requester_office ?? 'N/A', 12, '...') }}</td>
                            <td>
                                <strong>{{ $request->service_type ?? 'N/A' }}</strong><br>
                                {{ Str::limit($request->description, 45, '...') }}
                            </td>
                            <td>
                                <span class="status-badge status-{{ str_replace('-', '-', strtolower($request->status)) }}">
                                    {{ ucfirst(str_replace('-', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td>{{ $request->created_at->format('m/d/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="no-data">
                    <p>✓ No service requests match the selected criteria.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ now()->year }} MIS Service Request Management System</p>
            <p>This is an automatically generated confidential report. Please handle with appropriate care.</p>
        </div>
    </div>
</body>

</html>