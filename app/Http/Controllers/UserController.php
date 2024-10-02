<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;
use App\Models\User as Snails;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Check if a search term exists and filter users, otherwise return all users
        $users = User::when($search, function($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
        })->paginate(10);

        // Append the search term to pagination links
        $users->appends(['search' => $search]);

        return view('users.index', [
            'users'     => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('users.create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        return view('users.show', [
            'user'     => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);
        $sectors = Sector::all();
        return view('users.edit', [
            'user'      => $user,
            'sectors'   => $sectors
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,  // Ensure unique email except for this user
            'active' => 'required|boolean',                     // Ensures 'active' is either 0 or 1 (boolean)
            'notes' => 'nullable|string',                       // 'notes' can be a string, maximum 255 characters, or null
            'primary_sector_id' => 'integer|exists:sectors,id',   // Ensure 'sector' is a valid integer and exists in the sectors table
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user profile with the validated data
        $user->update($validated);

        // Optionally, flash a success message to the session
        return redirect()->route('users.show', $user->id)
                        ->with('success', 'Profile updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
