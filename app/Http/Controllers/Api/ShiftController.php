<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShiftController extends Controller
{
    public function upcoming(Request $request, Event $event)
    {
        abort_unless($event->isPublic(), 404);

        $limit = max(1, min((int) $request->input('limit', 50), 100));

        $sort = $request->input('sort') === 'desc' ? 'desc' : 'asc';

        $query = $event->shifts()
            ->where('start_time', '>=', now())
            ->withCount('users')
            ->with('tags')
            ->orderBy('start_time', $sort);

        if ($request->filled('minutesFromNow')) {
            $minutes = max(0, (int) $request->input('minutesFromNow'));
            $query->where('start_time', '<=', now()->addMinutes($minutes));
        }

        if ($request->boolean('openSlotsOnly')) {
            $query->havingRaw('users_count < max_volunteers');
        }

        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->input('date'));
        }

        if ($request->filled('tagId')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->input('tagId'));
            });
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $shifts = $query->limit($limit)->get();

        $descriptionLength = $request->filled('descriptionLength')
            ? max(0, (int) $request->input('descriptionLength'))
            : null;

        $data = $shifts->map(function ($shift) use ($event, $descriptionLength) {
            $description = $descriptionLength !== null
                ? Str::limit($shift->description ?? '', $descriptionLength)
                : $shift->description;

            return [
                'id' => $shift->id,
                'name' => $shift->name,
                'description' => $description,
                'start_time' => $shift->start_time->toIso8601String(),
                'end_time' => $shift->end_time->toIso8601String(),
                'max_volunteers' => $shift->max_volunteers,
                'volunteers_signed_up' => $shift->users_count,
                'open_slots' => max(0, $shift->max_volunteers - $shift->users_count),
                'tags' => $shift->tags->pluck('name'),
                'detail_url' => route('vol-listings-public.shift.show', [$event, $shift]),
                // Requires login; takes the volunteer into the app to sign up for this event's shifts.
                'signup_url' => route('volunteer.events.show', $event),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
