<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $shifts = $event->shifts()->latest()->get();
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

        return redirect()->route('admin.events.shifts.index', $event)->with('success', 'Shift created.');
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
        return view('admin.shifts.edit', compact('event', 'shift'));
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

        return redirect()->route('admin.events.shifts.index', $event)->with('success', 'Shift updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Shift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift deleted.');
    }
}
