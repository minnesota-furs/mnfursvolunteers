<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Get upcoming public volunteer events
        $upcomingEvents = Event::where('visibility', 'public')
            ->where('end_date', '>=', $now)
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Get the user’s upcoming shifts
        $upcomingShifts = $user->shifts()
            ->with('event') // make sure the Shift model has `event()` defined
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get()
            ->groupBy('event.name'); // you can also groupBy('event_id') for precision

        return view('dashboard', compact('upcomingEvents', 'upcomingShifts'));
    }
}
