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
            background: linear-gradient(135deg, #16a085 0%, #138d75 100%);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 4px solid #0e6251;
        }

        .header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.95;
        }

        .content {
            padding: 30px 30px;
        }

        .success-banner {
            background-color: #d5f4e6;
            padding: 25px;
            border-radius: 4px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #16a085;
        }

        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }

        .success-text {
            color: #0e6251;
            font-weight: 600;
            font-size: 18px;
        }

        .info-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #16a085;
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

        .section-title {
            font-weight: 600;
            margin-bottom: 12px;
            color: #2c3e50;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 8px;
        }

        .comments-box {
            background-color: #fef5e7;
            padding: 15px;
            border-left: 4px solid #f39c12;
            border-radius: 4px;
            margin: 20px 0;
        }

        .next-steps {
            background-color: #ecf9ff;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #d4e9f7;
        }

        .next-steps h3 {
            color: #2980b9;
            margin-top: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .next-steps ul {
            margin: 12px 0;
            padding-left: 20px;
            list-style-type: disc;
        }

        .next-steps li {
            margin: 6px 0;
            color: #2c3e50;
            font-size: 14px;
        }

        .rating-section {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
            border-left: 4px solid #27ae60;
        }

        .rating-section h3 {
            color: #2e7d32;
            margin: 0 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .rating-section p {
            margin: 8px 0;
            color: #2e7d32;
            font-size: 13px;
        }

        .cta-button {
            display: inline-block;
            background-color: #16a085;
            color: #fff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            font-weight: 600;
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

        .timeline {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border-left: 4px solid #95a5a6;
        }

        .timeline-item {
            margin: 10px 0;
            font-size: 13px;
            padding: 5px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .timeline-date {
            color: #999;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✓ Service Request Completed</h1>
            <p>Your request has been successfully fulfilled</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p style="font-size: 15px; color: #2c3e50;">Dear {{ $user->full_name ?? ($user->first_name . ' ' . $user->last_name) }},</p>

            <!-- Success Banner -->
            <div class="success-banner">
                <div class="success-icon">✓</div>
                <div class="success-text">Your service request has been completed successfully!</div>
                <p style="margin: 10px 0 0 0; color: #0e6251; font-size: 13px;">Thank you for using our service</p>
            </div>

            <!-- Request Summary -->
            <div class="info-section">
                <div class="section-title">✓ Request Summary</div>
                <div class="info-row">
                    <span class="info-label">Control Number:</span>
                    <span class="info-value"><strong>{{ $request->control_number }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Category:</span>
                    <span class="info-value">{{ $request->category ?? 'General Service' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><strong style="color: #16a085;">COMPLETED</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Completion Date:</span>
                    <span class="info-value">{{ $request->date_completed ? $request->date_completed->format('F d, Y H:i A') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Assigned Technician:</span>
                    <span class="info-value">{{ $request->assignedTechnician?->user?->full_name ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Work Summary -->
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #95a5a6;">
                <strong style="display: block; margin-bottom: 8px; color: #2c3e50; font-size: 13px;">📋 Original Request Description:</strong>
                <p style="margin: 0; color: #555; font-size: 14px; line-height: 1.6;">
                    {{ \Illuminate\Support\Str::limit($request->description, 300) }}
                </p>
            </div>

            <!-- Admin Comments/Notes -->
            @if($request->comments)
            <div class="comments-box">
                <strong style="color: #f39c12; font-size: 13px; text-transform: uppercase; display: block; margin-bottom: 8px;">📝 Completion Notes</strong>
                <p style="margin: 0; color: #7d6608; font-size: 14px; line-height: 1.6;">
                    {{ $request->comments }}
                </p>
            </div>
            @endif

            <!-- Timeline -->
            <div class="timeline">
                <div style="font-weight: 600; color: #2c3e50; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #ecf0f1;">
                    📅 Timeline
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Request Submitted:</span>
                    <span class="timeline-date">{{ $request->created_at->format('F d, Y H:i A') }}</span>
                </div>
                @if($request->assigned_date)
                <div class="timeline-item">
                    <span class="timeline-label">Technician Assigned:</span>
                    <span class="timeline-date">{{ \Carbon\Carbon::parse($request->assigned_date)->format('F d, Y H:i A') }}</span>
                </div>
                @endif
                @if($request->date_completed)
                <div class="timeline-item">
                    <span class="timeline-label">Work Completed:</span>
                    <span class="timeline-date">{{ $request->date_completed->format('F d, Y H:i A') }}</span>
                </div>
                @endif
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>📋 What Happens Next</h3>
                <ul>
                    <li>Review the completed work in your service request</li>
                    <li>Confirm whether your concern is resolved and leave feedback in the portal</li>
                    <li>For follow-up work, submit a new service request</li>
                    <li>Contact MIS for any clarifications or concerns</li>
                </ul>
            </div>

            <!-- Feedback Section -->
            <div class="rating-section">
                <h3>⭐ Your Feedback Matters</h3>
                <p>
                    Please take a moment to rate the service provided by our technician and share your feedback.
                    This helps us improve our service quality.
                </p>
                <p style="margin-top: 15px;">
                    <a href="{{ route('requests.show', $request) }}" style="color: #16a085; text-decoration: none; font-weight: 600;">
                        → Rate this Service Request
                    </a>
                </p>
            </div>

            <!-- Support Section -->
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #ffc107;">
                <strong style="color: #856404; display: block; margin-bottom: 8px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">💡 Need Further Assistance?</strong>
                <p style="margin: 8px 0 0 0; color: #856404; font-size: 13px;">
                    If the issue persists or you need additional support, please submit a new service request or contact the MIS Department directly.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>📧 Automated Notification:</strong> This email was generated by the MIS Service Request System.</p>
            <p>✉️ Please do not reply directly. Log in to the portal for further communication.</p>
            <p style="margin-top: 15px; border-top: 1px solid #ecf0f1; padding-top: 10px; opacity: 0.7;">
                &copy; {{ date('Y') }} Management Information System • All rights reserved
            </p>
        </div>
    </div>
</body>

</html>
