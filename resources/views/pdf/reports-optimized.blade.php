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
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #000;
            background-color: #fff;
            width: 100%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            line-height: 1.2;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #000;
            font-size: 18px;
            margin-bottom: 3px;
            font-weight: bold;
        }

        .header p {
            color: #333;
            font-size: 10px;
            margin: 2px 0;
        }

        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 10px;
            color: #000;
        }

        .report-info div {
            flex: 1;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }

        .stat-box {
            padding: 6px;
            border: 1px solid #000;
            background-color: #f5f5f5;
            text-align: center;
        }

        .stat-box h4 {
            color: #000;
            font-size: 9px;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .stat-box p {
            color: #000;
            font-size: 16px;
            font-weight: bold;
        }

        .table-container {
            margin-top: 0;
            overflow-x: visible;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        table thead {
            background-color: #ddd;
            color: #000;
        }

        table th {
            padding: 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #999;
            word-break: break-word;
        }

        table td {
            padding: 4px;
            border: 1px solid #ccc;
            word-break: break-word;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #000;
            border: 1px solid #ffc107;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #000;
            border: 1px solid #0066cc;
        }

        .status-completed {
            background-color: #d4edda;
            color: #000;
            border: 1px solid #28a745;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #000;
            border: 1px solid #f5c6cb;
        }

        .status-on-hold {
            background-color: #e2e3e5;
            color: #000;
            border: 1px solid #d3d6d8;
        }

        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #999;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .control-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 8px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #333;
            font-size: 11px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .header {
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
            <h1>SERVICE REQUESTS REPORT</h1>
            <p>MIS Service Request Management System</p>
            <p>Generated: {{ now()->format('F d, Y \a\t h:i A') }}</p>
        </div>

        <!-- Report Info -->
        <div class="report-info">
            <div>
                <strong>Report Period:</strong> All Records
            </div>
            <div style="text-align: right;">
                <strong>Total Records:</strong> {{ $requests->count() }}
            </div>
        </div>

        <!-- Summary Statistics -->
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

        <!-- Table Container -->
        <div class="table-container">
            @if ($requests->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Control #</th>
                        <th style="width: 15%;">Requester</th>
                        <th style="width: 12%;">Designation</th>
                        <th style="width: 12%;">Office</th>
                        <th style="width: 28%;">Description</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 9%;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                    <tr>
                        <td><span class="control-number">{{ $request->control_number }}</span></td>
                        <td>{{ $request->requester_name ?? 'N/A' }}</td>
                        <td>{{ Str::limit($request->requester_designation ?? 'N/A', 12, '...') }}</td>
                        <td>{{ Str::limit($request->requester_office ?? 'N/A', 12, '...') }}</td>
                        <td>{{ Str::limit($request->description, 35, '...') }}</td>
                        <td>
                            <span class="status-badge status-{{ str_replace('-', '-', strtolower($request->status)) }}">
                                {{ ucfirst(str_replace('-', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('m/d/y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data">
                <p>No service requests found in the system.</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ now()->year }} MIS Service Request Management System | This report was automatically generated.</p>
        </div>
    </div>
</body>

</html>
