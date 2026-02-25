<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    /**
     * Generate (or regenerate) the authenticated user's iCal calendar token.
     */
    public function regenerateToken(Request $request)
    {
        $request->user()->generateCalendarToken();

        return back()->with('status', 'calendar-token-regenerated');
    }

    /**
     * Return an iCal feed of the user's upcoming and recent shifts.
     * This route is public (token-protected) so calendar apps can subscribe.
     */
    public function feed(string $token): Response
    {
        $user = User::where('calendar_token', $token)->firstOrFail();

        // Include all shifts (past and future) so the calendar stays accurate
        $shifts = $user->shifts()
            ->with('event')
            ->orderBy('start_time')
            ->get();

        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//MNFurs Volunteers//Shift Calendar//EN';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';
        $lines[] = 'X-WR-CALNAME:My Volunteer Shifts';
        $lines[] = 'X-WR-CALDESC:Your MNFurs volunteer shift schedule';
        $lines[] = 'X-WR-TIMEZONE:America/Chicago';

        foreach ($shifts as $shift) {
            $uid        = 'shift-' . $shift->id . '@mnfurs-volunteers';
            $dtstart    = $shift->start_time->utc()->format('Ymd\THis\Z');
            $dtend      = $shift->end_time->utc()->format('Ymd\THis\Z');
            $dtstamp    = now()->utc()->format('Ymd\THis\Z');
            $summary    = $this->escapeIcalText($shift->event->name . ' – ' . $shift->name);
            $location   = $this->escapeIcalText($shift->event->location ?? '');
            $description = $this->buildDescription($shift);

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $dtstamp;
            $lines[] = 'DTSTART:' . $dtstart;
            $lines[] = 'DTEND:' . $dtend;
            $lines[] = $this->foldLine('SUMMARY:' . $summary);
            if ($location) {
                $lines[] = $this->foldLine('LOCATION:' . $location);
            }
            $lines[] = $this->foldLine('DESCRIPTION:' . $description);
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        $ical = implode("\r\n", $lines) . "\r\n";

        return response($ical, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="my-volunteer-shifts.ics"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Escape special characters for iCal text values.
     */
    private function escapeIcalText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace("\n", '\n', $text);

        return $text;
    }

    /**
     * Fold long iCal lines at 75 octets per RFC 5545.
     */
    private function foldLine(string $line): string
    {
        $result = '';
        while (strlen($line) > 75) {
            $result .= substr($line, 0, 75) . "\r\n ";
            $line = substr($line, 75);
        }
        $result .= $line;

        return $result;
    }

    /**
     * Build a plain-text DESCRIPTION for a shift event.
     */
    private function buildDescription($shift): string
    {
        $parts = [];

        if ($shift->description) {
            $parts[] = $shift->description;
            $parts[] = '';
        }

        $hours = $shift->double_hours
            ? ($shift->durationInHours() * 2) . ' hrs (double hours!)'
            : $shift->durationInHours() . ' hrs';

        $parts[] = 'Duration: ' . $hours;

        if ($shift->max_volunteers) {
            $parts[] = 'Volunteers: ' . $shift->users->count() . '/' . $shift->max_volunteers;
        }

        return $this->escapeIcalText(implode('\n', $parts));
    }
}
