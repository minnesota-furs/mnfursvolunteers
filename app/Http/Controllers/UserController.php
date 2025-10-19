<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;
use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $sort = $request->input('sort', 'name'); // Default sort column
        $direction = $request->input('direction', 'asc'); // Default sort direction

        // Get the current fiscal ledger
        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        // Ensure we have a ledger to work with
        if (!$currentLedger) {
            abort(500, 'Current fiscal ledger not found.');
        }

        // Query users
        $users = User::query()
            ->when($search, function (Builder $query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%') // Search by name
                    ->orWhere('first_name', 'like', '%' . $search . '%') // Search by first legal name
                    ->orWhere('last_name', 'like', '%' . $search . '%') // Search by last legal name
                    ->orWhere('email', 'like', '%' . $search . '%') // Search by email
                    ->orWhere('vol_code', $search); // Search by volunteer code
            })
            ->when($sort === 'hours', function (Builder $query) use ($currentLedger, $direction) {
                $query->withSum(['volunteerHours' => function ($q) use ($currentLedger) {
                    $q->where('fiscal_ledger_id', $currentLedger->id);
                }], 'hours')
                ->orderBy('volunteer_hours_sum_hours', $direction);
            }, function (Builder $query) use ($sort, $direction) {
                $query->orderBy($sort, $direction); // Default sorting
            })
            ->paginate(15);

        // Append the search term to pagination links
        $users->appends(['search' => $search]);

        $trashedUsers = User::onlyTrashed()->get();

        return view('users.index', compact('users', 'sort', 'direction', 'trashedUsers'));
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
        
        // Get audit logs for this user (only for admins)
        $auditLogs = null;
        if (Auth::check() && Auth::user()->admin) {
            $auditLogs = AuditLog::where('auditable_type', User::class)
                ->where('auditable_id', $user->id)
                ->with('user')
                ->latest()
                ->paginate(10, ['*'], 'logs_page');
        }

        return view('users.show', [
            'user' => $user,
            'volunteerHours' => $volunteerHours,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id = ''): View
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            $user = User::find($id);
            // $sectors = Sector::all();
            $departments = [];

            // $departments = Department::orderBy('name')->get(); 

            // Retrieve sectors with their departments, ordered by name
            $sectors = Sector::with(['departments' => function ($query) {
                $query->orderBy('name'); // Sort departments alphabetically within each sector
            }])->orderBy('name') // Sort sectors alphabetically
            ->get();

            // if ($user->sector) {
            //     $departments = Department::where('sector_id', $user->sector->id)->get();
            // }

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
                'admin' => ['boolean'], // Ensures 'admin' is either 0 or 1 (boolean), defaults to 0
                'departments' => 'nullable|array',
                'departments.*' => 'exists:departments,id',
            ]);

            // Find the user by ID
            $user = User::findOrFail($id);

            // Update the user profile with the validated data
            $user->update($validated);

            // Sync departments
            $user->departments()->sync($validated['departments'] ?? []);

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

    public function export() {
        // Fetch all users
        $users = User::all();

        // Define the CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ];

        // Create the CSV content
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Insert CSV column headers
            fputcsv($file, ['ID', 'Name', 'Active', 'Email', 'Departments', 'Sector', 'HoursThisPeriod', 'Created At', 'Updated At']);

            // Insert user data rows
            foreach ($users as $user) {
                $departments = $user->departments->map(function ($department) {
                    return "{$department->name} ({$department->sector->name})";
                })->join(', ');

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->active,
                    $user->email,
                    $departments ?? null,
                    $user->primary_sector_id ?? null,
                    $user->totalHoursForCurrentFiscalLedger(),
                    $user->created_at,
                    $user->updated_at
                ]);
            }

            fclose($file);
        };

        // Return the CSV file as a stream
        return FacadeResponse::stream($callback, 200, $headers);
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

    public function search(Request $request)
    {
        $searchTerm = $request->input('term');

        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return response()->json([]);
        }

        try {
            $users = User::where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%" . $searchTerm . "%")
                      ->orWhere('first_name', 'LIKE', "%" . $searchTerm . "%")
                      ->orWhere('last_name', 'LIKE', "%" . $searchTerm . "%")
                      ->orWhere('email', 'LIKE', "%" . $searchTerm . "%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%" . $searchTerm . "%"]);
            })
            ->where('active', true) // Only show active users
            ->select('id', 'first_name', 'last_name', 'name', 'email') // Select desired fields
            ->orderBy('name') // Order by name for consistent results
            ->take(10) // Limit results to a reasonable number
            ->get();

            Log::info('User search', [
                'term' => $searchTerm,
                'results_count' => $users->count(),
                'results' => $users->pluck('name')->toArray()
            ]);

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('User search error', [
                'term' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    public function orgChart()
    {
        $sectors = Sector::with('departments.users')->get();

        $nodes = [];
        foreach ($sectors as $sector) {
            // Add sector (no parent)
            $nodes[] = ['key' => 'S'.$sector->id, 'name' => $sector->name, 'type' => 'sector', 'fillColor' => '#efe8e1', 'textColor' => '#007848'];
    
            foreach ($sector->departments as $department) {
                // Add department with parent sector
                $nodes[] = ['key' => 'D'.$department->id, 'name' => $department->name, 'parent' => 'S'.$sector->id, 'type' => 'department', 'fillColor' => '#44392b', 'textColor' => '#FFF'];
    
                foreach ($department->users as $user) {
                    // Add user with parent department
                    $nodes[] = ['key' => $user->id, 'name' => $user->name, 'parent' => 'D'.$department->id, 'type' => 'user', 'fillColor' => '#007848', 'textColor' => '#FFF'];
                }
            }
        }

        return view('users.orgchart', ['nodes' => $nodes]);
    }

    public function restore($id)
    {
        $userListing = User::onlyTrashed()->findOrFail($id);

        // Restore the soft-deleted record
        $userListing->restore();

        return redirect()->route('users.index')
            ->with('success', [
                'message' => "User Restored Successfully"
            ]);
    }

}
