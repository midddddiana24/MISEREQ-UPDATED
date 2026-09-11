<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 20px;
        }

        .email-container {
            background: white;
            border-radius: 8px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #D1884F 0%, #B87540 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
        }

        .request-card {
            background: #f9f9f9;
            border-left: 4px solid #D1884F;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .label {
            font-weight: 600;
            color: #333;
        }

        .value {
            color: #666;
        }

        .button {
            display: inline-block;
            background: #D1884F;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }

        .button:hover {
            background: #B87540;
        }

        .footer {
            background: #f5f7fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>🔔 New Service Request Received</h1>
            <p>For MIS review and assignment</p>
        </div>

        <div class="content">
            <p>Hello Admin,</p>

            <p>A new service request has been submitted by a user and requires your attention.</p>

            <div class="request-card">
                <div class="detail-row">
                    <div class="label">Control #:</div>
                    <div class="value">{{ $request->control_number }}</div>
                </div>

                <div class="detail-row">
                    <div class="label">Requester:</div>
                    <div class="value">{{ $user->full_name ?? 'N/A' }}</div>
                </div>

                <div class="detail-row">
                    <div class="label">Email:</div>
                    <div class="value">{{ $user->email }}</div>
                </div>

                <div class="detail-row">
                    <div class="label">Office:</div>
                    <div class="value">{{ $request->requester_office ?? 'N/A' }}</div>
                </div>

                <div class="detail-row">
                    <div class="label">Category:</div>
                    <div class="value">{{ $request->category ?? 'General' }}</div>
                </div>

                <div class="detail-row">
                    <div class="label">Title:</div>
                    <div class="value">{{ $request->title ?? 'Service Request' }}</div>
                </div>

                <div class="detail-row">
                    <div class="label">Status:</div>
                    <div class="value"><strong style="color: #FF9800;">{{ ucfirst($request->status) }}</strong></div>
                </div>

                <div class="detail-row">
                    <div class="label">Submitted:</div>
                    <div class="value">{{ $request->created_at->format('M d, Y - h:i A') }}</div>
                </div>
            </div>

            <h3>Description:</h3>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px;">
                <p>{{ $request->description }}</p>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/admin/requests/' . $request->id) }}" class="button">View Request Details</a>
            </p>

            <p style="color: #999; font-size: 13px;">
                <strong>Quick Action:</strong> Log in to the admin panel to review, assign, or respond to this request.
            </p>
        </div>

        <div class="footer">
            <p>MIS Service Request Management System<br>
                This is an automated notification. Do not reply directly to this email.</p>
        </div>
    </div>
</body>

</html>
