<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VolunteerHours;

class VolunteerHoursController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($user = null)
    {
        \Log::debug('Hey!');
        // If a user is passed, retrieve the user from the database
        $selectedUser = $user ? User::find($user) : null;
        $users = User::all();

        // Pass the user (if any) to the view
        return view('hours.create', compact('selectedUser', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'hours'     => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $username = User::select('name')->find($validated['user_id'])->name;

        // Create the hour log entry
        VolunteerHours::create([
            'user_id'   => $validated['user_id'],
            'hours'     => $validated['hours'],
            'description' => $validated['description'],
            'notes'     => $validated['notes'],
        ]);

        // Redirect back with a success message
        return redirect()->route('users.index')
            ->with('success', [
                'message' => "<span class=\"text-brand-green\">{$validated['hours']}</span> volunteer " . ($validated['hours'] == 1 ? 'hour' : 'hours') . " logged successfully for <span class=\"text-brand-green\">{$username}</span>.",
                'action_text' => 'View User',
                'action_url' => route('users.show', $validated['user_id']),
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
