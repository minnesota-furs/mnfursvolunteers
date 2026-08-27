<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Shift;
use Illuminate\Http\Request;

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

    public function guestShow(Event $event, Request $request)
    {
        // Direct links should only work for public/unlisted events, matching guestIndex's visibility rules.
        abort_unless(in_array($event->visibility, ['public', 'unlisted']), 404);

        $availableDays = $event->isMultiDay()
            ? $event->shifts()
                ->when($event->hide_past_shifts, fn ($q) => $q->where('start_time', '>=', now()))
                ->selectRaw('DATE(start_time) as day')
                ->distinct()
                ->orderBy('day')
                ->pluck('day')
            : collect();

        $availableCategories = $event->categories()->has('shifts')->get();

        $shifts = $event->shifts()
            ->withCount(['users as filled_count'])
            ->with('categories')
            ->when($event->hide_past_shifts, fn ($q) => $q->where('start_time', '>=', now()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('day'), fn ($q) => $q->whereDate('start_time', $request->input('day')))
            ->when($request->input('availability') === 'open', fn ($q) => $q->whereRaw(
                '(select count(*) from shift_signups where shift_signups.shift_id = shifts.id) < shifts.max_volunteers'
            ))
            ->when($request->filled('category'), fn ($q) => $q->whereHas(
                'categories',
                fn ($q2) => $q2->where('event_categories.id', $request->input('category'))
            ))
            ->orderBy('start_time')
            ->paginate(10)
            ->appends($request->query());

        return view('vol-listings-guest.show', compact('event', 'shifts', 'availableDays', 'availableCategories'));
    }

    public function guestShowShift(Event $event, Shift $shift)
    {
        // Ensure the shift belongs to the event
        if ($shift->event_id !== $event->id) {
            abort(404);
        }

        $shift->load('categories');

        // Calculate openings and signup status
        $openings = $shift->max_volunteers - $shift->users->count();
        $isFull = $openings <= 0;
        $isPast = $shift->start_time->isPast();

        return view('vol-listings-guest.shift-show', compact('event', 'shift', 'openings', 'isFull', 'isPast'));
    }
}
