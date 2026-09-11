<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->subject }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f7f9;font-family:Arial,Helvetica,sans-serif;color:#243442;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #d9e2e8;border-radius:8px;">
                    <tr>
                        <td style="padding:18px 22px;background:#1f5f89;color:#ffffff;border-radius:8px 8px 0 0;">
                            <strong style="font-size:16px;">WVSU MIS Service Request System</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 22px;">
                            <h1 style="margin:0 0 12px;font-size:20px;color:#243442;">{{ $notification->subject }}</h1>
                            <p style="margin:0;color:#516573;font-size:14px;line-height:1.6;">{{ $notification->message }}</p>
                            @if($notification->action_url)
                                <p style="margin:20px 0 0;">
                                    <a href="{{ $notification->action_url }}" style="display:inline-block;padding:10px 14px;border-radius:6px;background:#1f6fa8;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;">Open MIS System</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 22px;border-top:1px solid #e5ebef;color:#71808b;font-size:11px;line-height:1.5;">
                            This is an automated notification. If you do not recognize this activity, contact the MIS Office.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
