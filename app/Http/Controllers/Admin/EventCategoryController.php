<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventCategoryRequest;
use App\Http\Requests\UpdateEventCategoryRequest;
use App\Models\Event;
use App\Models\EventCategory;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $categories = $event->categories()->withCount('shifts')->get();

        return view('admin.events.categories.index', compact('event', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        return view('admin.events.categories.create', compact('event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventCategoryRequest $request, Event $event)
    {
        $event->categories()->create($request->validated());

        return redirect()->route('admin.events.categories.index', $event)
            ->with('success', [
                'message' => 'Event category created successfully.',
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event, EventCategory $category)
    {
        return view('admin.events.categories.edit', compact('event', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventCategoryRequest $request, Event $event, EventCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('admin.events.categories.index', $event)
            ->with('success', [
                'message' => 'Event category updated successfully.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, EventCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.events.categories.index', $event)
            ->with('success', [
                'message' => 'Event category deleted successfully.',
            ]);
    }
}
