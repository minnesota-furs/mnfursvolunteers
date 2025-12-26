<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Reminder</title>
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
        .election-card {
            background-color: #f8f9fa;
            border-left: 4px solid {{ app_setting('primary_color', '#10b981') }};
            padding: 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .election-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .election-details {
            margin: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }
        .election-details strong {
            color: #1f2937;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: {{ app_setting('primary_color', '#10b981') }};
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: {{ app_setting('secondary_color', '#059669') }};
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
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 20px 0;
        }
        .urgent-notice {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ app_setting('organization_name', 'MNFursVolunteers') }} - Voting Reminder</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Hi {{ $user->name }},</p>
            
            <p>This is a reminder that you are eligible to vote in the following election, but we haven't received your vote yet:</p>
            
            <div class="election-card">
                <h2 class="election-title">{{ $election->title }}</h2>
                
                @if($election->description)
                    <div class="election-details">
                        <p>{{ strip_tags($election->description) }}</p>
                    </div>
                @endif
                
                <div class="election-details">
                    <strong>Voting Period:</strong> {{ $election->start_date->format('F j, Y') }} - {{ $election->end_date->format('F j, Y g:i A') }}
                </div>
                
                <div class="election-details">
                    <strong>Positions Available:</strong> {{ $election->max_positions }}
                </div>
                
                @if($election->end_date->diffInDays(now()) <= 2)
                    <div class="urgent-notice">
                        <strong>⚠️ Voting ends soon!</strong> Only {{ $election->end_date->diffForHumans() }} remaining to cast your vote.
                    </div>
                @endif
            </div>
            
            <div style="text-align: center;">
                <a href="{{ route('elections.show', $election->id) }}" class="button">
                    Vote Now
                </a>
            </div>
            
            <div class="divider"></div>
            
            <p style="font-size: 14px; color: #6b7280;">
                Your voice matters! Make sure to participate in this important election. 
                You can vote for up to {{ $election->max_positions }} candidate{{ $election->max_positions > 1 ? 's' : '' }}.
            </p>
            
            <p style="font-size: 14px; color: #6b7280;">
                If you have any questions about the election or need assistance voting, please contact the elections team.
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ app_setting('organization_name', 'MNFursVolunteers') }}. All rights reserved.</p>
            <p>
                <a href="{{ route('elections.show', $election->id) }}">View Election Details</a>
            </p>
        </div>
    </div>
</body>
</html>
