<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

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
     * ADMINS ONLY; if the user is not an admin, redirect them to the users index
     */
    public function create(Request $request): View
    {
        if(!($request->user()->isadmin()))
        {
            return view('users.create', [
                'user'      => $request->user(),
            ]);
        }
        else
        {
            return $this->index($request);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    { 
        
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,',  // Ensure unique email except for this user
            'active' => 'required|boolean',                     // Ensures 'active' is either 0 or 1 (boolean)
            'notes' => 'nullable|string',                       // 'notes' can be a string, maximum 255 characters, or null
            'primary_sector_id' => 'integer|exists:sectors,id',   // Ensure 'sector' is a valid integer and exists in the sectors table
            'primary_dept_id' => 'integer|exists:departments,id',   // Ensure 'sector' is a valid integer and exists in the sectors table
            'isadmin' => 'required|boolean',
            'password' => 'required|string|max:255'
        ]);

        //dd($validated);

        $user = User::create($validated);

        // Optionally, flash a success message to the session
        return redirect()->route('users.index')
            ->with('success', [
                'message' => "Volunteer <span class=\"text-brand-green\">{$user->name}</span> created successfully",
                'action_text' => 'View User',
                'action_url' => route('users.show', $user->id),
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $user = User::findOrFail($id);
        $volunteerHours = $user->volunteerHours()
            ->orderByRaw('COALESCE(volunteer_date, created_at) DESC')
            ->paginate(15);

        return view('users.show', compact('user', 'volunteerHours'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);
        $sectors = Sector::all();
        $departments = [];

        if ($user->sector) {
            $departments = Department::where('sector_id', $user->sector->id)->get();
        }

        return view('users.edit', [
            'user'      => $user,
            'sectors'   => $sectors,
            'departments' => $departments
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
            'primary_dept_id' => 'integer|exists:departments,id',   // Ensure 'sector' is a valid integer and exists in the sectors table
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user profile with the validated data
        $user->update($validated);

        // Optionally, flash a success message to the session
        return redirect()->route('users.show', $user->id)
            ->with('success', [
                'message' => "Volunteer <span class=\"text-brand-green\">{$user->name}</span> updated successfully",
                'action_text' => 'View User',
                'action_url' => route('users.show', $user->id),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
