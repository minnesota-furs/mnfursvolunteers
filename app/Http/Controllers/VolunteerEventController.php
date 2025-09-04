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

    public function myShifts(Event $event)
    {
        $user = auth()->user();

        // Get all shifts for this event the user signed up for
        $shifts = $event->shifts()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->orderBy('start_time')
            ->get();

        $futureShifts = $event->shifts()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->where('start_time', '>=', now()) // Only future shifts
            ->orderBy('start_time')
            ->get();

        // Add up hours across all shifts
        $totalVolunteerHours = $shifts->sum(function ($shift) {
            return $shift->double_hours ? $shift->durationInHours() * 2 : $shift->durationInHours();
        });

        $shiftsRemaining = $shifts->filter(fn($shift) => $shift->start_time->isFuture())->count();

        return view('events.my-shifts', compact('event', 'shifts', 'futureShifts', 'totalVolunteerHours', 'shiftsRemaining'));
    }

    public function myShiftsAll()
    {
        $user = auth()->user();

        // Eager load event for each shift
        $shifts = $user->shifts()
            ->with('event')
            ->orderBy('start_time')
            ->get();

        $futureShifts = $shifts->filter(fn($shift) => $shift->start_time->isFuture());

        // Add up hours across all shifts and double the shifts that have double_hours set
        $totalVolunteerHours = $shifts->sum(function ($shift) {
            return $shift->double_hours ? $shift->durationInHours() * 2 : $shift->durationInHours();
        });

        $shiftsRemaining = $shifts->filter(fn($shift) => $shift->start_time->isFuture())->count();

        return view('events.my-shifts-all', compact('shifts', 'futureShifts', 'totalVolunteerHours', 'shiftsRemaining'));
    }
}
