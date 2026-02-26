<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tag;
use Illuminate\Http\Request;

class ShiftTagReportController extends Controller
{
    /**
     * Tag-based breakdown for a single event.
     */
    public function eventReport(Event $event)
    {
        $this->authorize('update', $event);

        $tags = Tag::with(['shifts' => function ($q) use ($event) {
            $q->where('event_id', $event->id)
              ->with(['users' => function ($uq) {
                  $uq->select('users.id', 'users.name', 'users.email')
                     ->withPivot('no_show', 'hours_logged_at');
              }]);
        }])
        ->orderBy('name')
        ->get()
        ->filter(fn ($tag) => $tag->shifts->isNotEmpty())
        ->values();

        // Aggregate stats per tag
        $stats = $tags->map(function ($tag) {
            $slots     = $tag->shifts->sum('max_volunteers');
            $filled    = $tag->shifts->sum(fn ($s) => $s->users->count());
            $noShows   = $tag->shifts->sum(fn ($s) => $s->users->where('pivot.no_show', true)->count());
            $credited  = $tag->shifts->sum(fn ($s) => $s->users->whereNotNull('pivot.hours_logged_at')->count());
            return [
                'tag'        => $tag,
                'shifts'     => $tag->shifts->count(),
                'slots'      => $slots,
                'filled'     => $filled,
                'no_shows'   => $noShows,
                'credited'   => $credited,
                'fill_rate'  => $slots > 0 ? round(($filled / $slots) * 100) : 0,
            ];
        });

        return view('admin.shifts.tag-report', compact('event', 'tags', 'stats'));
    }

    /**
     * Cross-event tag report with optional event/tag filters.
     */
    public function crossEventReport(Request $request)
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        $tags   = Tag::forShifts()->orderBy('name')->get();

        $selectedEventIds = array_values(array_filter((array) $request->input('event_ids', [])));
        $selectedTagIds   = array_values(array_filter((array) $request->input('tag_ids', [])));

        $report = collect();

        if (! empty($selectedTagIds)) {
            $report = Tag::whereIn('id', $selectedTagIds)
                ->with(['shifts' => function ($q) use ($selectedEventIds) {
                    $q->with(['event', 'users' => function ($uq) {
                        $uq->select('users.id', 'users.name', 'users.email')
                           ->withPivot('no_show', 'hours_logged_at');
                    }]);
                    if (! empty($selectedEventIds)) {
                        $q->whereIn('event_id', $selectedEventIds);
                    }
                }])
                ->orderBy('name')
                ->get()
                ->filter(fn ($tag) => $tag->shifts->isNotEmpty())
                ->values();
        }

        // Aggregate per-tag stats
        $stats = $report->map(function ($tag) {
            $slots    = $tag->shifts->sum('max_volunteers');
            $filled   = $tag->shifts->sum(fn ($s) => $s->users->count());
            $noShows  = $tag->shifts->sum(fn ($s) => $s->users->where('pivot.no_show', true)->count());
            $credited = $tag->shifts->sum(fn ($s) => $s->users->whereNotNull('pivot.hours_logged_at')->count());
            return [
                'tag'       => $tag,
                'shifts'    => $tag->shifts->count(),
                'slots'     => $slots,
                'filled'    => $filled,
                'no_shows'  => $noShows,
                'credited'  => $credited,
                'fill_rate' => $slots > 0 ? round(($filled / $slots) * 100) : 0,
            ];
        });

        return view('admin.shifts.tag-report-cross', compact(
            'events',
            'tags',
            'report',
            'stats',
            'selectedEventIds',
            'selectedTagIds',
        ));
    }
}
