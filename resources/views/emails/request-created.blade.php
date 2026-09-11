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
            background-color: #2c3e50;
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

        .button {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }

        .info-box {
            background-color: #ecf0f1;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Service Request Submitted</h1>
        </div>

        <div class="content">
            <p>Dear {{ $user->full_name ?? $user->first_name . ' ' . $user->last_name }},</p>

            <p>Your service request has been successfully submitted to our system.</p>

            <div class="info-box">
                <strong>Request Details:</strong><br>
                Control Number: <strong>{{ $request->control_number }}</strong><br>
                Category: {{ $request->category ?? 'General' }}<br>
                Status: {{ ucfirst($request->status) }}<br>
                Submitted Date: {{ $request->created_at->format('F d, Y H:i A') }}
            </div>

            <p>Your request is now queued for review. You will receive email updates as your request progresses through our system.</p>

            <p>If you have any questions about your request, please reply to this email or contact our support team.</p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p>&copy; {{ date('Y') }} MIS Service Request System. All rights reserved.</p>
        </div>
    </div>
</body>

</html>