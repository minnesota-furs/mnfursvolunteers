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

        $upcomingEvents = Event::where('start_date', '>=', $now)->orderBy('start_date')->get();
        $recentPastEvents = Event::whereBetween('start_date', [$threeMonthsAgo, $now])->orderByDesc('start_date')->get();

        return view('events.index', compact('upcomingEvents', 'recentPastEvents'));
    }

    public function show(Event $event)
    {
        $shifts = $event->shifts()->orderBy('start_time')->get();

        return view('events.show', compact('event', 'shifts'));
    }
}
