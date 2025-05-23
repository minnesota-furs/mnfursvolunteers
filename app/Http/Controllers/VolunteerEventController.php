<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Event;

class VolunteerEventController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $threeMonthsAgo = $now->copy()->subMonths(3);

        $upcomingEvents = Event::visibleToPublic()
            ->where('start_date', '>=', $now)
            ->orderBy('start_date')
            ->get();

        $recentPastEvents = Event::visibleToPublic()
            ->whereBetween('start_date', [$threeMonthsAgo, $now])  
            ->orderByDesc('start_date')
            ->get();

        return view('events.index', compact('upcomingEvents', 'recentPastEvents'));
    }

    public function show(Event $event)
    {
        // Load users for use in shift->users
        $event->load('shifts.users');

        $shifts = $event->shifts
            ->when($event->hide_past_shifts, fn ($shifts) =>
                $shifts->filter(fn ($shift) => $shift->start_time->isFuture())
            )
            ->sortBy('start_time')
            ->values(); // reindex

        $userShifts = auth()->user()->shiftsForEvent($event->id)->sortBy('start_time');

        return view('events.show', [
            'event' => $event,
            'shifts' => $shifts,
            'userShifts' => $userShifts,
        ]);
    }
}
