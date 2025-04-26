<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $shifts = $event->shifts()->orderBy('start_time', 'asc')->get();
        return view('admin.shifts.index', compact('event', 'shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        return view('admin.shifts.create', compact('event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'max_volunteers' => 'required|integer|min:1',
        ]);

        $event->shifts()->create($request->only(['name', 'description', 'start_time', 'end_time', 'max_volunteers']));

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Shift <span class=\"text-brand-green\">{$request->name}</span> created successfully",
            ]);
        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event, Shift $shift)
    {
        return view('admin.shifts.create', compact('event', 'shift'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event, Shift $shift)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'max_volunteers' => 'required|integer|min:1',
        ]);

        $shift->update($request->only(['name', 'description', 'start_time', 'end_time', 'max_volunteers']));

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Shift <span class=\"text-brand-green\">{$event->name}</span> updated successfully",
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Shift $shift)
    {
        $shift->delete();
        return back()->with('success', [
            'message' => "Shift <span class=\"text-brand-green\">{$shift->name}</span> deleted",
        ]);
    }

    public function duplicate(Event $event, Shift $shift)
    {
        $newShift = $shift->replicate();
        $newShift->name = $shift->name . ' (Copy)';
        $newShift->start_time = $newShift->start_time->addHour();
        $newShift->end_time = $newShift->end_time->addHour(1);
        $newShift->save();

        $event->shifts()->save($newShift);

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Shift <span class=\"text-brand-green\">{$shift->name}</span> duplicated successfully",
            ]); 
    }

    public function removeVolunteer(Event $event, Shift $shift, User $user)
    {
        $shift->users()->detach($user->id);

        return redirect()->back()
            ->with('success', [
                'message' => "<span class=\"text-brand-green\">{$user->name}</span> removed from shift",
            ]); 
    }


}
