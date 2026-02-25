<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Shift;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    /**
     * Show the live manager dashboard — current, upcoming, and recent shifts across all active events.
     */
    public function index()
    {
        $now = Carbon::now();

        // Window settings (can be tweaked)
        $upcomingWindow  = $now->copy()->addHours(3);
        $recentWindow    = $now->copy()->subHours(2);

        // Load all shifts from events that are currently active (haven't ended yet)
        // or ended within the recent window, and include events starting soon.
        $shifts = Shift::with(['event', 'users'])
            ->whereHas('event', function ($query) use ($now, $recentWindow, $upcomingWindow) {
                $query->where('start_date', '<=', $upcomingWindow)
                      ->where('end_date', '>=', $recentWindow);
            })
            ->whereBetween('start_time', [$recentWindow, $upcomingWindow])
            ->orWhere(function ($query) use ($now) {
                // Also grab any shift currently happening (started before now, ends after now)
                $query->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now)
                      ->whereHas('event', function ($q) use ($now) {
                          $q->where('end_date', '>=', $now->copy()->subHours(2));
                      });
            })
            ->orderBy('start_time')
            ->get();

        // Categorise shifts
        $activeShifts   = $shifts->filter(fn ($s) => $s->start_time->lte($now) && $s->end_time->gte($now));
        $upcomingShifts = $shifts->filter(fn ($s) => $s->start_time->gt($now) && $s->start_time->lte($upcomingWindow));
        $recentShifts   = $shifts->filter(fn ($s) => $s->end_time->lt($now) && $s->end_time->gte($recentWindow))
                                  ->sortByDesc('end_time');

        // Aggregate stats
        $totalSlots   = $shifts->sum('max_volunteers');
        $filledSlots  = $shifts->sum(fn ($s) => $s->users->count());
        $coveragePct  = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100) : 0;
        $emptyShifts  = $shifts->filter(fn ($s) => $s->users->isEmpty())->count();

        // Distinct active events visible on the board
        $activeEventIds = $shifts->pluck('event_id')->unique();
        $activeEvents   = Event::whereIn('id', $activeEventIds)->orderBy('start_date')->get();

        return view('admin.manager-dashboard', compact(
            'activeShifts',
            'upcomingShifts',
            'recentShifts',
            'totalSlots',
            'filledSlots',
            'coveragePct',
            'emptyShifts',
            'activeEvents',
            'now',
        ));
    }
}
