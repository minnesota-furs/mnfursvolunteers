<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Reminder</title>
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
        .shift-card {
            background-color: #f8f9fa;
            border-left: 4px solid {{ app_setting('primary_color', '#10b981') }};
            padding: 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .shift-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .shift-details {
            margin: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }
        .shift-details strong {
            color: #1f2937;
            font-weight: 600;
        }
        .icon {
            display: inline-block;
            width: 18px;
            margin-right: 8px;
            vertical-align: middle;
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
        .summary {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shift Reminder!</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Hi {{ $user->name }},</p>
            
            @if($shifts->count() === 1)
                <p>You have a volunteer shift coming up today! Here are the details:</p>
            @else
                <p>You have <strong>{{ $shifts->count() }} volunteer shifts</strong> coming up today! Here are the details:</p>
            @endif

            @foreach($shifts as $shift)
                <div class="shift-card">
                    <h2 class="shift-title">{{ $shift->event->title ?? 'Volunteer Shift' }}</h2>
                    
                    @if($shift->name)
                        <div class="shift-details">
                            <strong>Shift Name:</strong> {{ $shift->name }}
                        </div>
                    @endif
                    
                    <div class="shift-details">
                        <strong>Time:</strong> 
                        {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                    </div>
                    
                    @if($shift->event->location)
                        <div class="shift-details">
                            <strong>Location:</strong> {{ $shift->event->location }}
                        </div>
                    @endif
                    
                    @if($shift->description)
                        <div class="shift-details">
                            <strong>Details:</strong> {{ $shift->description }}
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="summary">
                <strong>⏰ Remember:</strong> Please arrive a few minutes early to check in and get ready for your shift. 
                @if($shifts->count() > 1)
                    Make sure to check the times carefully as you have multiple shifts today!
                @endif
            </div>

            <div style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="button">
                    View My Dashboard
                </a>
            </div>

            <div class="divider"></div>

            <p style="color: #6b7280; font-size: 14px;">
                Thank you for volunteering with {{ app_name() }}! Your time and dedication make a real difference.
            </p>
        </div>
        
        <div class="footer">
            <p>
                This is an automated reminder from {{ app_name() }}<br>
                <a href="{{ route('dashboard') }}">View Your Shifts</a> | 
                <a href="{{ route('profile.edit') }}">Update Preferences</a>
            </p>
            {{-- <p style="margin-top: 10px;">
                {{ app_setting('contact_email') ? 'Questions? Contact us at ' . app_setting('contact_email') : '' }}
            </p> --}}
        </div>
    </div>
</body>
</html>
