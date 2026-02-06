<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f44336; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .info { background: #e3f2fd; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔒 Security Alert</h2>
        </div>
        <div class="content">
            <p>Hi {{ $userName }},</p>
            
            <div class="alert-box">
                <strong>{{ $details }}</strong>
            </div>
            
            <div class="info">
                <strong>Details:</strong><br>
                IP Address: {{ $ipAddress }}<br>
                @if($country)
                Country: {{ $country }}<br>
                @endif
                Time: {{ $timestamp }}
            </div>
            
            <p><strong>Was this you?</strong></p>
            <p>If you recognize this activity, you can safely ignore this email.</p>
            <p>If you did NOT attempt to log in, please:</p>
            <ul>
                <li>Change your password immediately</li>
                <li>Review your account activity</li>
                <li>Contact support if you need assistance</li>
            </ul>
            
            <p style="margin-top: 20px; color: #666; font-size: 12px;">
                This is an automated security notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
