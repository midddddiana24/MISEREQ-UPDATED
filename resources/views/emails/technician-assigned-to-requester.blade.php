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
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 4px solid #229954;
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
            color: #27ae60;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-left: 4px solid #27ae60;
            border-radius: 4px;
            margin: 15px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            min-width: 140px;
        }

        .info-value {
            color: #555;
            text-align: right;
        }

        .tech-card {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.05) 0%, rgba(46, 204, 113, 0.05) 100%);
            padding: 25px;
            border-left: 4px solid #27ae60;
            border-radius: 4px;
            margin: 20px 0;
        }

        .tech-name {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tech-detail {
            padding: 8px 0;
            color: #555;
            font-size: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .tech-detail:last-child {
            border-bottom: none;
        }

        .tech-detail strong {
            color: #2c3e50;
            display: block;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            color: #27ae60;
        }

        .timeline {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .timeline-item {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-icon {
            min-width: 30px;
            text-align: center;
            font-weight: bold;
            color: #27ae60;
            margin-right: 15px;
        }

        .timeline-content {
            color: #555;
        }

        .cta-section {
            background-color: #e8f8f5;
            padding: 20px;
            border-radius: 4px;
            margin: 25px 0;
            border-left: 4px solid #27ae60;
            border: 1px solid #d4e9f2;
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

        hr {
            border: none;
            border-top: 1px solid #ecf0f1;
            margin: 20px 0;
        }

        .highlight {
            background-color: #fffacd;
            padding: 2px 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✓ Technician Assigned</h1>
            <p>Your service request has been assigned to a professional technician</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Dear {{ $user->full_name ?? $user->first_name . ' ' . $user->last_name }},</p>
                <p>Good news! Your service request has been reviewed and assigned to a qualified technician. We appreciate your patience and are committed to resolving your issue promptly.</p>
            </div>

            <!-- Request Details -->
            <div class="section">
                <div class="section-title">Your Service Request</div>
                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Request ID:</span>
                        <span class="info-value"><strong>{{ $request->control_number }}</strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Service Category:</span>
                        <span class="info-value">{{ $request->category ?? 'General Service' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Request Date:</span>
                        <span class="info-value">{{ $request->created_at->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Technician Information -->
            <div class="section">
                <div class="section-title">Your Assigned Technician</div>
                <div class="tech-card">
                    <div class="tech-name">
                        👤 {{ $technician->full_name }}
                    </div>
                    @if($technician->specialization)
                    <div class="tech-detail">
                        <strong>Specialization</strong>
                        {{ $technician->specialization }}
                    </div>
                    @endif
                    @if($technician->phone)
                    <div class="tech-detail">
                        <strong>Contact Number</strong>
                        {{ $technician->phone }}
                    </div>
                    @endif
                    @if($technician->email)
                    <div class="tech-detail">
                        <strong>Email Address</strong>
                        {{ $technician->email }}
                    </div>
                    @endif
                    @if($technician->notes)
                    <div class="tech-detail">
                        <strong>Notes</strong>
                        {{ $technician->notes }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Assignment Notes -->
            @if($request->technician_notes)
            <div class="section">
                <div class="section-title">Important Information</div>
                <div style="background-color: #fffacd; padding: 20px; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <strong style="display: block; margin-bottom: 8px; color: #856404;">📌 Special Instructions:</strong>
                    <p style="margin: 0; color: #856404; line-height: 1.6;">{{ $request->technician_notes }}</p>
                </div>
            </div>
            @endif

            <!-- What to Expect -->
            <div class="section">
                <div class="section-title">What to Expect</div>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon">1</div>
                        <div class="timeline-content">
                            <strong>Initial Contact</strong><br>
                            The technician will reach out to you within 1-2 business days to discuss the details
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">2</div>
                        <div class="timeline-content">
                            <strong>Assessment</strong><br>
                            A thorough assessment of your request will be conducted
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">3</div>
                        <div class="timeline-content">
                            <strong>Work Execution</strong><br>
                            The technician will begin work and keep you informed of progress
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">✓</div>
                        <div class="timeline-content">
                            <strong>Completion</strong><br>
                            You will be notified upon successful completion of the work
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="cta-section">
                <p><strong>📞 Need to Reach Your Technician?</strong><br>You can use the MIS Service Request System portal to send messages or call the contact number provided above. For urgent matters, please contact the MIS Department directly.</p>
            </div>

            <!-- Closing -->
            <div style="margin-top: 30px;">
                <p style="color: #555; font-size: 15px;">Thank you for using the MIS Service Request System. We are committed to providing you with excellent service.</p>
                <p style="margin-top: 15px;"><br>Best regards,<br><strong style="color: #2c3e50;">MIS Service Request System</strong></p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>ℹ️ This is an automated notification</strong> from the MIS Service Request System.</p>
            <p>For questions or concerns, please contact the MIS Department or use the system portal.</p>
            <hr style="margin: 12px 0; opacity: 0.5;">
            <p>&copy; {{ date('Y') }} MIS Department • All rights reserved</p>
            <p style="font-size: 12px; opacity: 0.7;">Management Information System</p>
        </div>
    </div>
</body>

</html>
