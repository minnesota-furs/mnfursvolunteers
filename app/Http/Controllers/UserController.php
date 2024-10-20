<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
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
        $users = User::when($search, function ($query, $search) {
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
        if (Auth::check() && Auth::user()->isAdmin()) {
            $sectors = Sector::all();
            $departments = [];
            return view('users.create', [
                'sectors'   => $sectors,
                'departments' => $departments
            ]);
        } else {
            abort(401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'first_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->withoutTrashed()],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'active' => ['required', 'boolean'], // Ensures 'active' is either 0 or 1 (boolean)
                'notes' => ['nullable', 'string', 'max:255'], // 'notes' can be a string, maximum 255 characters, or null
                'primary_sector_id' => ['nullable', 'integer', 'exists:sectors,id'],   // Ensure 'sector' is a valid integer and exists in the sectors table
                'primary_dept_id' => ['nullable', 'integer', 'exists:departments,id'], // Ensure 'sector' is a valid integer and exists in the sectors table
                'admin' => ['boolean'] // Ensures 'admin' is either 0 or 1 (boolean), defaults to 0
            ]);

            // Check if a user with the same email exists and is soft-deleted
            $existingUser = User::where('email', $validated['email'])->withTrashed()->first();

            if ($existingUser && $existingUser->trashed()) {
                // User found but not soft-deleted, update instead of create
                $user = $existingUser;
                $user->restore();
                $user->update($validated);
            } else {
                // Create new user
                $user = User::create($validated);
            }

            // Optionally, flash a success message to the session
            return redirect()->route('users.index')
                ->with('success', [
                    'message' => "Volunteer <span class=\"text-brand-green\">{$user->name}</span> created successfully",
                    'action_text' => 'View User',
                    'action_url' => route('users.show', $user->id),
                ]);
        } else {
            abort(401);
        }
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

        return view('users.show', [
            'user' => $user,
            'volunteerHours' => $volunteerHours,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id = ''): View
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
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
        } else {
            abort(401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'first_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email,' . $id],  // Ensure unique email except for this user
                'active' => ['required', 'boolean'],                     // Ensures 'active' is either 0 or 1 (boolean)
                'notes' => ['nullable', 'string'],                       // 'notes' can be a string, maximum 255 characters, or null
                'primary_sector_id' => ['nullable', 'integer', 'exists:sectors,id'],   // Ensure 'sector' is a valid integer and exists in the sectors table
                'primary_dept_id' => ['nullable', 'integer', 'exists:departments,id'],   // Ensure 'sector' is a valid integer and exists in the sectors table
                'admin' => ['boolean'] // Ensures 'admin' is either 0 or 1 (boolean), defaults to 0
            ]);

            // Find the user by ID
            $user = User::findOrFail($id);

            // Update the user profile with the validated data
            $user->update($validated);

            // Optionally, flash a success message to the session
            return redirect()->route('users.show', $user->id)
                ->with('success', [
                    'message' => "Volunteer <span class=\"text-brand-green\">{$user->name}</span> updated successfully",
                    // 'action_text' => 'View User',
                    // 'action_url' => route('users.show', $user->id),
                ]);
        } else {
            abort(401);
        }
    }

    public function delete(Request $request, string $id): View
    {
        if ($request->user()->isAdmin()) {
            $user = User::findOrFail($id);
            return view('users.delete_confirm', [
                'user'   => $user,
            ]);
        } else {
            abort(401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            $user = User::findOrFail($id);
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', [
                    'message' => "Volunteer <span class=\"text-brand-red\">{$user->name}</span> deleted successfully",
                ]);
        } else {
            abort(401);
        }
    }

    public function import_view(Request $request)
    {
        return view('users.import', []);
    }

    public function import(Request $request)
    {
        \Log::debug('CSV Import Started');
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $newUsersCount = 0;
        $skippedUsersCount = 0;

        // Read the CSV file
        if (($handle = fopen($request->file('csv_file'), 'r')) !== false) {
            // Get the first row (headers) if needed
            $header = fgetcsv($handle, 1000, ',');

            // Loop through each row of the CSV
            while (($col = fgetcsv($handle, 1000, ',')) !== false) {
                if (!filter_var($col[1], FILTER_VALIDATE_EMAIL) || empty($col[0])) {
                    \Log::debug('Skipping Invalid Row');
                    continue;
                }

                // Assuming the CSV has the colimns in the correct order
                $name       = $col[0];
                $email      = $col[1];
                $password   = $col[2];
                $fname      = $col[3];
                $lname      = $col[4];
                $sector     = $col[5];
                $dept       = $col[6];

                if ($sector == '') {
                    $sector = null;
                }

                if ($dept == '') {
                    $dept = null;
                }

                if ($password == '') {
                    $password = $fname . $lname . '!';
                    $password = strtolower($password);
                }

                \Log::info('Creating User', [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'fname' => $fname,
                    'lname' => $lname,
                    'sector' => $sector,
                    'dept' => $dept
                ]);

                // Check if the user already exists by email
                $existingUser = User::where('email', $email)->first();

                // Create a new user or update if the user already exists
                if (!$existingUser) {
                    // Create new user if the email doesn't exist
                    User::create(
                        [
                            'name' => $name,
                            'email' => $email,
                            'first_name' => $fname,
                            'last_name' => $lname,
                            'primary_sector_id' => $sector,
                            'primary_dept_id' => $dept,
                            'password' => Hash::make($password) // Hash the password
                        ]
                    );
                    $newUsersCount++; // Increment new users counter
                } else {
                    $skippedUsersCount++; // Increment skipped users counter
                }
            }

            fclose($handle);

            return redirect()->route('users.index')
                ->with('success', [
                    'message' => "Import complete: {$newUsersCount} users added, {$skippedUsersCount} users skipped.",
                ]);
        }
    }
}
