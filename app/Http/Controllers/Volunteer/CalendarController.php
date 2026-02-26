<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\EventStatus;

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

        $calendar = Calendar::create('My Volunteer Shifts')
            ->productIdentifier('-//MNFurs Volunteers//Shift Calendar//EN')
            ->description('Your MNFurs volunteer shift schedule')
            ->withoutAutoTimezoneComponents();

        foreach ($shifts as $shift) {
            $description = $this->buildDescription($shift);

            $event = Event::create($shift->event->name . ' – ' . $shift->name)
                ->uniqueIdentifier('shift-' . $shift->id . '@mnfurs-volunteers')
                ->startsAt($shift->start_time->toDateTime())
                ->endsAt($shift->end_time->toDateTime())
                ->status(EventStatus::Confirmed);

            if ($shift->event->location) {
                $event->address($shift->event->location);
            }

            if ($description) {
                $event->description($description);
            }

            $calendar->event($event);
        }

        return response($calendar->get(), 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="my-volunteer-shifts.ics"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
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

        return implode("\n", $parts);
    }
}
