<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #e74c3c;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .content {
            margin: 20px 0;
        }

        .footer {
            border-top: 1px solid #ddd;
            margin-top: 20px;
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }

        .warning-box {
            background-color: #fadbd8;
            padding: 15px;
            border-left: 4px solid #e74c3c;
            margin: 15px 0;
        }

        .reason-box {
            background-color: #fdeaea;
            padding: 15px;
            border-left: 4px solid #e74c3c;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Service Request Cannot Be Processed</h1>
        </div>

        <div class="content">
            <p>Dear {{ $user->full_name ?? $user->first_name . ' ' . $user->last_name }},</p>

            <div class="warning-box">
                <strong>⚠ Notice:</strong><br>
                Your service request could not be processed or has been rejected.
            </div>

            <div class="reason-box">
                <strong>Reason for Rejection:</strong><br>
                {{ $reason ?: 'No specific reason provided. Please contact support.' }}
            </div>

            <p><strong>Request Details:</strong></p>
            <ul>
                <li>Control Number: <strong>{{ $request->control_number }}</strong></li>
                <li>Category: {{ $request->category ?? 'General' }}</li>
                <li>Submitted: {{ $request->created_at->format('F d, Y H:i A') }}</li>
            </ul>

            <p>If you believe this is an error or would like to discuss this further, please contact our support team or resubmit your request with the necessary corrections.</p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p>&copy; {{ date('Y') }} MIS Service Request System. All rights reserved.</p>
        </div>
    </div>
</body>

</html>