<?php

namespace App\Http\Controllers;

use App\Models\OneOffEvent;
u                  $ch            return back()->with('error', 'You can only check in during the event.');
        }

        $checkIn = OneOffEventCheckIn::updateOrCreate(
            [
                'one_off_event_id' => $oneOffEvent->id,
                'user_id' => Auth::id(),
            ],
            [
                'checked_in_at' => $now,
            ]
        );

        return back()->with('success', [
            'message' => 'You\'ve been checked in!',
        ]);
    }
}entCheckIn::updateOrCreate(
            [
                'one_off_event_id' => $oneOffEvent->id,
                'user_id' => Auth::id(),
            ],
            [
                'checked_in_at' => $now,
            ]
        );

        return back()->with('success', [
            'message' => 'You\'ve been checked in!',
        ]);
    }
}f_event_id' => $oneOffEvent->id,
                'user_id' => Auth::id(),
            ],
            [
                'checked_in_at' => $now,
            ]
        );

        return back()->with('success', [
            'message' => 'You\'ve been checked in!',
        ]);
    }
}ls\OneOffEventCheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OneOffEventController extends Controller
{
    // Show list of upcoming events
    public function index()
    {
        $events = OneOffEvent::where('end_time', '>=', now())->orderBy('start_time')->get();
        return view('one_off_events.index', compact('events'));
    }

    // Show a single event
    public function show(OneOffEvent $oneOffEvent)
    {
        $checkIn = null;
        if (Auth::check()) {
            $checkIn = OneOffEventCheckIn::where('user_id', Auth::id())
                ->where('one_off_event_id', $oneOffEvent->id)
                ->first();
        }

        return view('one_off_events.show', compact('oneOffEvent', 'checkIn'));
    }

    // Show form to create an event (admin)
    public function create()
    {
        return view('one_off_events.create');
    }

    // Store event (admin)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        OneOffEvent::create($validated);

        return redirect()->route('one-off-events.index')->with('success', [
                'message' => "One Off Event Created Successfully"
            ]);
    }

    // Check in to an event
    public function checkIn(OneOffEvent $oneOffEvent)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to check in.');
        }

        // Only allow check-in if now is within the event timeframe
        $now = now();
        if ($now->lt($oneOffEvent->start_time->subHours(1)) || $now->gt($oneOffEvent->end_time->addHours(12))) {
            return back()->with('error', 'You can only check in during the event.');
        }

        $checkIn = OneOffEventCheckIn::updateOrCreate(
            [
                'one_off_event_id' => $oneOffEvent->id,
                'user_id' => Auth::id(),
            ],
            [
                'checked_in_at' => $now,
            ]
        );

        return back()->with('success', 'You’ve been checked in!');
    }
}
