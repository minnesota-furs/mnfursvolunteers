<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: {{ app_setting('primary_color', '#10b981') }};
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid {{ app_setting('primary_color', '#10b981') }};
            padding: 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: {{ app_setting('primary_color', '#10b981') }};
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test Email</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Hi {{ $user->name }},</p>
            
            <div class="message-box">
                <p>This is a test email from the MNFursVolunteers system.</p>
                <p>If you're receiving this, it means the email configuration is working correctly!</p>
                <p><strong>Sent at:</strong> {{ now()->format('F j, Y g:i A') }}</p>
            </div>
            
            <p>If you have any questions or concerns, please contact your administrator.</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ app_setting('organization_name', 'MNFursVolunteers') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
