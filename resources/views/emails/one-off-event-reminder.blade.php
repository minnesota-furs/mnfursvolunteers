<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Reminder</title>
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
        .event-card {
            background-color: #f8f9fa;
            border-left: 4px solid {{ app_setting('primary_color', '#10b981') }};
            padding: 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .event-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .event-details {
            margin: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }
        .event-details strong {
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
            <h1>Event Reminder!</h1>
        </div>

        <div class="content">
            <p class="greeting">Hi {{ $user->name }},</p>

            @if($event->isRsvpType())
                @if($timing === 'hour_before')
                    <p>Just a heads up, you're RSVP'd for an event starting in about an hour!</p>
                @else
                    <p>Just a heads up, you're RSVP'd for an event happening today!</p>
                @endif
            @else
                @if($timing === 'hour_before')
                    <p>Just a heads up, don't forget to check in — your event starts in about an hour!</p>
                @else
                    <p>Just a heads up, don't forget to check in for today's event!</p>
                @endif
            @endif

            <div class="event-card">
                <h2 class="event-title">{{ $event->name }}</h2>

                <div class="event-details">
                    <strong>Time:</strong>
                    {{ $event->start_time->format('g:i A') }}{{ $event->end_time ? ' - ' . $event->end_time->format('g:i A') : '' }}
                </div>

                @if($event->location)
                    <div class="event-details">
                        <strong>Location:</strong>
                        @if($event->url)
                            <a href="{{ $event->url }}" style="color: {{ app_setting('primary_color', '#10b981') }};">{{ $event->location }}</a>
                        @else
                            {{ $event->location }}
                        @endif
                    </div>
                @endif
            </div>

            <div class="summary">
                <strong>⏰ Remember:</strong> Please arrive a few minutes early so you're ready to go when the event starts.
            </div>

            <div style="text-align: center;">
                <a href="{{ route('simple-volunteer-events.show', $event) }}" class="button">
                    View Event Details
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
                <a href="{{ route('simple-volunteer-events.show', $event) }}">View Event</a> |
                <a href="{{ route('simple-volunteer-events.show', $event) }}">Update Reminder Preferences</a>
            </p>
        </div>
    </div>
</body>
</html>
