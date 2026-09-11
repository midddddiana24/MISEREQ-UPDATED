<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 4px solid #2980b9;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.95;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #2980b9;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #3498db;
            border-radius: 4px;
            margin: 15px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 150px;
        }

        .info-value {
            color: #555;
            text-align: right;
        }

        .task-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .task-list li {
            padding: 12px 0;
            padding-left: 30px;
            position: relative;
            color: #555;
            font-size: 15px;
        }

        .task-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
            font-size: 18px;
        }

        .cta-section {
            background-color: #ecf9ff;
            padding: 20px;
            border-radius: 4px;
            margin: 25px 0;
            text-align: center;
            border: 1px solid #d4e9f7;
        }

        .cta-section p {
            color: #2c3e50;
            font-size: 15px;
            margin: 0;
            line-height: 1.6;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            border-top: 1px solid #ecf0f1;
            font-size: 13px;
            color: #666;
            text-align: center;
            line-height: 1.8;
        }

        .footer p {
            margin: 8px 0;
        }

        .signature {
            font-weight: 600;
            color: #2c3e50;
            margin-top: 15px;
        }

        hr {
            border: none;
            border-top: 1px solid #ecf0f1;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>New Service Request Assignment</h1>
            <p>You have been assigned a new service request</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Dear {{ $technician->full_name }},</p>
                <p>A new service request has been assigned to you in the MIS Service Request System. Please review the details below and begin work as soon as possible.</p>
            </div>

            <!-- Request Details -->
            <div class="section">
                <div class="section-title">Service Request Details</div>
                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Control Number:</span>
                        <span class="info-value"><strong>{{ $request->control_number }}</strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category:</span>
                        <span class="info-value">{{ $request->category ?? 'General Service' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Requester:</span>
                    <span class="info-value">{{ $request->requester_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Office/Department:</span>
                    <span class="info-value">{{ $request->requester_office ?? $request->office ?? 'N/A' }}</span>
                </div>
            </div>
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 15px;">
                <strong style="display: block; margin-bottom: 8px; color: #2c3e50;">Request Summary:</strong>
                <p style="margin: 0; color: #555; font-size: 14px; line-height: 1.6;">
                    @php
                    $description = $request->description ?? 'No description provided';
                    echo strlen($description) > 200 ? substr($description, 0, 200) . '...' : $description;
                    @endphp
                </p>
            </div>
        </div>

        <!-- Assignment Notes -->
        @if($request->technician_notes)
        <div class="section">
            <div class="section-title">Special Instructions</div>
            <div class="assignment-note">
                <strong>Admin Notes:</strong>
                <p>{{ $request->technician_notes }}</p>
            </div>
        </div>
        @endif

        <!-- Requester Information -->
        <div class="section">
            <div class="section-title">Requester Information</div>
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $request->user->full_name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $request->user->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $request->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ $request->requester_office ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Request Date:</span>
                    <span class="info-value">{{ $request->created_at->format('M d, Y H:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Request status -->
        <div class="section">
            <div class="section-title">Request Metadata</div>
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Current Status:</span>
                    <span class="info-value"><strong>{{ ucfirst(str_replace('-', ' ', $request->status)) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Required Actions -->
        <div class="section">
            <div class="section-title">Your Action Items (IMPORTANT)</div>
            <ul class="task-list">
                <li><strong>Step 1:</strong> Log in to the MIS Service Request System immediately</li>
                <li><strong>Step 2:</strong> Review and download the complete request details and PDF</li>
                <li><strong>Step 3:</strong> Contact the requester to schedule work or confirm receipt</li>
                <li><strong>Step 4:</strong> Update the system status to "Processing" when you begin work</li>
                <li><strong>Step 5:</strong> Document all work done and issues resolved</li>
                <li><strong>Step 6:</strong> Mark as "Completed" with signatures and comments once finished</li>
                <li><strong>Step 7:</strong> Upload any supporting documentation or proof of work</li>
            </ul>
        </div>

        <!-- PDF Documentation -->
        <div class="section">
            <div class="section-title">Documentation</div>
            <div style="background-color: #f0f8ff; padding: 20px; border-radius: 4px; border-left: 4px solid #3498db; margin: 15px 0;">
                <strong style="display: block; margin-bottom: 8px; color: #2980b9; font-size: 14px;">📄 Printable PDF Available</strong>
                <p style="margin: 8px 0; color: #555; font-size: 14px;">
                    Access the complete service request details with a printable PDF format by logging into the system. This PDF contains:
                </p>
                <ul style="margin: 10px 0 0 20px; color: #555; font-size: 14px; list-style-type: disc;">
                    <li>Complete service request information</li>
                    <li>Requester details and contact information</li>
                    <li>Detailed description of requested services</li>
                    <li>Assigned technician information</li>
                    <li>Space for technician notes and sign-off</li>
                </ul>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <strong style="display: block; margin-bottom: 8px; color: #2c3e50; font-size: 16px;">🔐 Access Your Assignments</strong>
            <p style="margin: 12px 0; color: #2c3e50; font-size: 14px;">
                Login to the MIS Service Request System to view detailed request information, download the PDF, and track your work progress.
            </p>
            <p style="margin: 8px 0; font-size: 13px; color: #666;">
                <strong>Only you (as the assigned technician) can view and process this request.</strong>
            </p>
        </div>

        <!-- Important Notice -->
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid #ffc107; margin: 20px 0;">
            <strong style="color: #856404; display: block; margin-bottom: 5px;">⚠️ Important Notice</strong>
            <p style="margin: 0; color: #856404; font-size: 13px;">
                This request has been specifically assigned to you. Please acknowledge this assignment in the system within 24 hours.
                If you cannot complete this work, immediately notify the MIS administrative staff.
            </p>
        </div>

        <!-- Closing -->
        <div style="margin-top: 30px;">
            <p style="color: #555; font-size: 15px;">Best regards,</p>
            <p style="color: #2c3e50; font-weight: 600; font-size: 15px;">MIS Service Request System</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>⚠️ Important:</strong> This is an automated notification from the MIS Service Request System.</p>
        <p>Please do not reply to this email directly. Use the system portal to respond or contact the MIS Department.</p>
        <hr style="margin: 12px 0; opacity: 0.5;">
        <p>&copy; {{ date('Y') }} MIS Department • All rights reserved</p>
        <p style="font-size: 12px; opacity: 0.7;">Management Information System</p>
    </div>
    </div>
</body>

</html>
