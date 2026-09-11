<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: #fff;
            padding: 30px 30px;
            text-align: center;
            border-bottom: 4px solid #1e8449;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 700;
        }

        .header p {
            font-size: 13px;
            margin-top: 5px;
            opacity: 0.95;
        }

        .content {
            padding: 30px 30px;
        }

        .status-banner {
            background-color: #d5f4e6;
            border-left: 4px solid #27ae60;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            background-color: #27ae60;
            color: #fff;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 16px;
            margin: 10px 0;
        }

        .info-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #27ae60;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .info-value {
            color: #555;
            text-align: right;
        }

        .remarks-box {
            background-color: #fef5e7;
            padding: 15px;
            border-left: 4px solid #f39c12;
            margin: 20px 0;
            border-radius: 4px;
        }

        .action-items {
            background-color: #ecf9ff;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #d4e9f7;
        }

        .action-items h3 {
            color: #2980b9;
            margin-top: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-items ul {
            margin: 15px 0;
            padding-left: 20px;
            list-style-type: disc;
        }

        .action-items li {
            margin: 8px 0;
            color: #2c3e50;
            font-size: 14px;
        }

        .timeline {
            margin: 20px 0;
        }

        .timeline-item {
            display: flex;
            margin: 15px 0;
            padding: 0 0 15px 35px;
            position: relative;
            border-left: 2px solid #ecf0f1;
            padding-left: 25px;
        }

        .timeline-item:last-child {
            border-left: none;
        }

        .timeline-dot {
            position: absolute;
            left: -9px;
            width: 16px;
            height: 16px;
            background-color: #27ae60;
            border-radius: 50%;
            border: 3px solid #fff;
            top: 0;
        }

        .timeline-status {
            font-weight: 600;
            color: #27ae60;
            font-size: 13px;
            text-transform: uppercase;
        }

        .timeline-date {
            color: #999;
            font-size: 12px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #ecf0f1;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
        }

    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Service Request Status Update</h1>
            <p>Your request has been updated</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Dear {{ $user->full_name ?? ($user->first_name . ' ' . $user->last_name) }},</p>

            <!-- Status Banner -->
            <div class="status-banner">
                <p style="margin: 0; color: #2c3e50; font-weight: 600;">Your request status has changed to:</p>
                <div class="status-badge">{{ ucfirst(str_replace('-', ' ', $newStatus)) }}</div>
                <p style="margin: 8px 0 0 0; color: #555; font-size: 13px;">
                    Updated on {{ now()->format('F d, Y') }} at {{ now()->format('g:i A') }}
                </p>
            </div>

            <!-- Status Message -->
            <p style="font-size: 15px; line-height: 1.8; color: #2c3e50; margin: 20px 0;">
                {{ $statusMessage }}
            </p>

            <!-- Request Details Section -->
            <div class="info-section">
                <div style="font-weight: 600; margin-bottom: 12px; color: #2c3e50; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px;">
                    Request Details
                </div>
                <div class="info-row">
                    <span class="info-label">Control Number:</span>
                    <span class="info-value"><strong>{{ $request->control_number }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Category:</span>
                    <span class="info-value">{{ $request->category ?? 'General Service' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Previous Status:</span>
                    <span class="info-value">{{ ucfirst(str_replace('-', ' ', $oldStatus)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Current Status:</span>
                    <span class="info-value"><strong style="color: #27ae60;">{{ ucfirst(str_replace('-', ' ', $newStatus)) }}</strong></span>
                </div>
                @if($request->assigned_technician_id)
                <div class="info-row">
                    <span class="info-label">Assigned Technician:</span>
                    <span class="info-value">{{ $request->assignedTechnician->user->full_name ?? 'N/A' }}</span>
                </div>
                @endif
            </div>

            <!-- Remarks Section -->
            @if($remarks)
            <div class="remarks-box">
                <strong style="color: #f39c12; font-size: 13px; text-transform: uppercase; display: block; margin-bottom: 8px;">📝 Update Notes</strong>
                <p style="margin: 0; color: #7d6608; font-size: 14px; line-height: 1.6;">
                    {{ $remarks }}
                </p>
            </div>
            @endif

            <!-- Action Items Based on Status -->
            <div class="action-items">
                <h3>📋 What Happens Next</h3>
                @if($newStatus === 'processing')
                <ul>
                    <li><strong>Your request is now being actively processed</strong></li>
                    <li>The assigned technician will contact you if additional information is needed</li>
                    <li>You will receive further updates as work progresses</li>
                    <li>Expected completion time will be communicated separately</li>
                    <li>Check the system dashboard for real-time updates</li>
                </ul>
                @elseif($newStatus === 'pending')
                <ul>
                    <li><strong>Your request has been received and is in queue</strong></li>
                    <li>A technician will be assigned based on availability and skill set</li>
                    <li>You will be notified once a technician is assigned</li>
                    <li>Please ensure your contact information is up to date</li>
                    <li>Average waiting time is noted in your request details</li>
                </ul>
                @elseif($newStatus === 'completed')
                <ul>
                    <li><strong>Your request has been completed successfully!</strong></li>
                    <li>Please review the completed work and final documentation</li>
                    <li>If you have feedback, please rate this service request</li>
                    <li>Download the completion certificate from the system if needed</li>
                    <li>For any follow-up work, please submit a new service request</li>
                </ul>
                @else
                <ul>
                    <li>Your request status has been updated in the system</li>
                    <li>Log in to view detailed information about your request</li>
                    <li>You will be notified of any further changes</li>
                    <li>Contact MIS if you have questions</li>
                </ul>
                @endif
            </div>

            <!-- Request Summary -->
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #95a5a6;">
                <strong style="display: block; margin-bottom: 8px; color: #2c3e50; font-size: 13px;">Service Description:</strong>
                <p style="margin: 0; color: #555; font-size: 14px; line-height: 1.6;">
                    {{ mb_strimwidth($request->description, 0, 250, '...') }}
                </p>
            </div>

            <!-- Next Steps -->
            <div style="background-color: #e8f5e9; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #4caf50;">
                <strong style="color: #2e7d32; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase;">✓ Recommended Actions</strong>
                <ul style="margin: 10px 0; padding-left: 20px; color: #2e7d32; font-size: 13px;">
                    <li>Log in to the portal to view complete details</li>
                    <li>Keep your contact information updated</li>
                    <li>Provide additional information if requested by the technician</li>
                    <li>Ensure someone is available for any required on-site visits</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>📧 Notification:</strong> This is an automated email from the MIS Service Request System.</p>
            <p>✉️ Do not reply to this email. Log in to the system portal to communicate about your request.</p>
            <p style="margin-top: 15px; border-top: 1px solid #ecf0f1; padding-top: 10px; opacity: 0.7;">
                &copy; {{ date('Y') }} Management Information System • All rights reserved
            </p>
        </div>
    </div>
</body>

</html>
</div>
</div>
</body>

</html>
