<?php

namespace App\Http\Controllers;

use App\Models\Event;

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
        $shifts = $event->shifts()
            ->orderBy('start_time')
            ->get();
        
        return view('vol-listings-guest.show', compact('event', 'shifts'));
    }
}
