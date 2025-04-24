<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::orderBy('start_date', 'asc')->get();
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'location'    => 'nullable|string',
            'visibility' => 'required|in:public,unlisted,draft',
            'hide_past_shifts' => 'nullable|boolean',
        ]);

        // Normalize checkbox (unchecked checkboxes don't get sent)
        $validated['hide_past_shifts'] = $request->has('hide_past_shifts');

        Event::create([
            ...$request->only(['name', 'description', 'start_date', 'end_date', 'location', 'visibility', 'hide_past_shifts']),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', [
                'message' => "Event <span class=\"text-brand-green\">{$request->name}</span> created successfully",
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
    public function edit(Event $event)
    {
        return view('admin.events.create', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'location'    => 'nullable|string',
            'visibility' => 'required|in:public,unlisted,draft',
            'hide_past_shifts' => 'nullable|boolean',
        ]);

        // Normalize checkbox (unchecked checkboxes don't get sent)
        $validated['hide_past_shifts'] = $request->has('hide_past_shifts');

        $event->update($request->only(['name', 'description', 'start_date', 'end_date', 'location', 'visibility', 'hide_past_shifts']));

        return redirect()->route('admin.events.index')
            ->with('success', [
                'message' => "Event <span class=\"text-brand-green\">{$event->name}</span> updated successfully"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', [
            'message' => "Event <span class=\"text-brand-green\">{$event->name}</span> deleted"
        ]);
    }
}
