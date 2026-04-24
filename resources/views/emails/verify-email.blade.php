<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Address</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }
        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .email-header {
            background-color: #8b0000; /* Maroon theme */
            padding: 30px 40px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .email-body {
            padding: 40px;
        }
        .email-body h2 {
            margin-top: 0;
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }
        .email-body p {
            font-size: 16px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .btn-verify {
            display: inline-block;
            background-color: #8b0000;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(139, 0, 0, 0.2);
            transition: background-color 0.2s;
        }
        .email-footer {
            padding: 30px 40px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .email-footer p {
            margin: 0 0 10px 0;
        }
        .fallback-url {
            word-break: break-all;
            color: #8b0000;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="email-header">
                    <h1>Andalan Artha Primanusa</h1>
                </td>
            </tr>
            <tr>
                <td class="email-body">
                    <h2>Hello {{ $user->name }},</h2>
                    <p>Welcome to Andalan Artha Primanusa! We're excited to have you on board. Please click the button below to verify your email address and complete your registration.</p>
                    
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="center">
                                <a href="{{ $url }}" class="btn-verify">Verify Email Address</a>
                            </td>
                        </tr>
                    </table>
                    
                    <p style="margin-top: 30px;">If you did not create an account, no further action is required.</p>
                    
                    <p style="margin-top: 30px; margin-bottom: 0;">Best regards,<br><strong>HR Department</strong></p>
                </td>
            </tr>
            <tr>
                <td class="email-footer">
                    <p>If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:</p>
                    <a href="{{ $url }}" class="fallback-url">{{ $url }}</a>
                    <p style="margin-top: 20px;">&copy; {{ date('Y') }} Andalan Artha Primanusa. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
