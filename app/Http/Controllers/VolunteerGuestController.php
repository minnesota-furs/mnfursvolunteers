<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Shift;

use Illuminate\Http\Request;
use Parsedown;

class VolunteerGuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function guestIndex()
    {
        $events = Event::visibleToPublic()
            ->orderBy('start_date', 'asc')
            ->where('end_date', '>=', now())
            ->get();

        return view('vol-listings-guest.index', compact('events'));
    }

    public function guestShow(Event $event)
    {            
        $shifts = $event->shifts
            ->when($event->hide_past_shifts, fn ($shifts) =>
                $shifts->filter(fn ($shift) => $shift->start_time->isFuture())
            )
            ->sortBy('start_time')
            ->values(); // reindex
        
        return view('vol-listings-guest.show', compact('event', 'shifts'));
    }
}
