<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;
use App\Models\Department;
use App\Models\FiscalLedger;
use App\Rules\NotBlacklisted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Mail\TestEmail;
use App\Models\CommunicationLog;

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

        // Filter inputs
        $departmentFilter = $request->input('departments', []);
        $tagFilter = $request->input('tags', []);
        $statusFilter = $request->input('status');
        $departmentStatus = $request->input('department_status');

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
            ->when(!empty($departmentFilter), function (Builder $query) use ($departmentFilter) {
                $query->whereHas('departments', function ($q) use ($departmentFilter) {
                    $q->whereIn('departments.id', $departmentFilter);
                });
            })
            ->when($departmentStatus === 'no_department', function (Builder $query) {
                $query->doesntHave('departments');
            })
            ->when($departmentStatus === 'has_department', function (Builder $query) {
                $query->has('departments');
            })
            ->when(!empty($tagFilter), function (Builder $query) use ($tagFilter) {
                $query->whereHas('tags', function ($q) use ($tagFilter) {
                    $q->whereIn('tags.id', $tagFilter);
                });
            })
            ->when($statusFilter === 'active', function (Builder $query) {
                $query->where('active', true);
            })
            ->when($statusFilter === 'inactive', function (Builder $query) {
                $query->where('active', false);
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

        // Append the search term and filters to pagination links
        $users->appends($request->except('page'));

        $trashedUsers = User::onlyTrashed()->get();
        
        // Get departments and tags for filter dropdowns
        $departments = Department::orderBy('name')->get();
        $tags = \App\Models\Tag::orderBy('name')->get();

        return view('users.index', compact('users', 'sort', 'direction', 'trashedUsers', 'departments', 'tags'));
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
                'first_name' => ['nullable', 'string', 'max:255', new NotBlacklisted('name', $request->first_name, $request->last_name)],
                'last_name' => ['nullable', 'string', 'max:255'],
                'pronouns' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->withoutTrashed(), new NotBlacklisted('email')],
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
    public function show(Request $request, string $id)
    {

        $user = User::with([
            'volunteerHours.department.sector',
            'shifts.event',
            'auditLogs.user',
            'departments.sector',
            'sector',
            'userNotes.creator',
            'tags'
        ])->findOrFail($id);
        
        // Get filter parameters
        $periodFilter = $request->input('period', 'all');
        $dateFilter = $request->input('date', 'all');
        
        // Build the query
        $query = $user->volunteerHours()
            ->with(['department.sector']);
        
        // Apply fiscal period filter
        if ($periodFilter !== 'all') {
            if ($periodFilter === 'current') {
                // Current fiscal period
                $currentLedger = FiscalLedger::where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
                if ($currentLedger) {
                    $query->where('fiscal_ledger_id', $currentLedger->id);
                }
            } else {
                // Specific fiscal ledger
                $query->where('fiscal_ledger_id', $periodFilter);
            }
        }
        
        // Apply date range filter
        if ($dateFilter === '14days') {
            $query->where(function($q) {
                $q->where('volunteer_date', '>=', now()->subDays(14))
                  ->orWhere(function($q2) {
                      $q2->whereNull('volunteer_date')
                         ->where('created_at', '>=', now()->subDays(14));
                  });
            });
        } elseif ($dateFilter === '30days') {
            $query->where(function($q) {
                $q->where('volunteer_date', '>=', now()->subDays(30))
                  ->orWhere(function($q2) {
                      $q2->whereNull('volunteer_date')
                         ->where('created_at', '>=', now()->subDays(30));
                  });
            });
        }
        
        $volunteerHours = $query
            ->orderByRaw('COALESCE(volunteer_date, created_at) DESC')
            ->paginate(15)
            ->appends($request->query());

        // Get timeline events for the sidebar (limited to 12)
        $timelineEvents = $user->getTimelineEvents()->take(12);

        // Get note counts for admins
        $totalNotes = $user->userNotes()->count();
        $writeupCount = $user->userNotes()->where('type', 'Writeup')->count();
        
        // Get all fiscal ledgers for filter dropdown
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();
        
        // Get current fiscal ledger info
        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        return view('users.show', [
            'user' => $user,
            'volunteerHours' => $volunteerHours,
            'timelineEvents' => $timelineEvents,
            'totalNotes' => $totalNotes,
            'writeupCount' => $writeupCount,
            'fiscalLedgers' => $fiscalLedgers,
            'currentLedger' => $currentLedger,
            'periodFilter' => $periodFilter,
            'dateFilter' => $dateFilter,
        ]);
    }

    /**
     * Display the full timeline for a user with pagination.
     */
    public function timeline(Request $request, string $id): View
    {
        // Only users with manage-users permission or admins can view timelines
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('manage-users')) {
            abort(403, 'Unauthorized to view user timelines.');
        }

        $user = User::with([
            'volunteerHours.department.sector',
            'shifts.event',
            'auditLogs.user',
            'userNotes.creator'
        ])->findOrFail($id);

        // Get all timeline events
        $allEvents = $user->getTimelineEvents();
        
        // Manually paginate the collection
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $timelineEvents = new \Illuminate\Pagination\LengthAwarePaginator(
            $allEvents->forPage($currentPage, $perPage),
            $allEvents->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('users.timeline', [
            'user' => $user,
            'timelineEvents' => $timelineEvents,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id = ''): View
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            $user = User::with([
                'volunteerHours.department.sector',
                'shifts.event',
                'auditLogs.user',
                'departments.sector',
                'sector',
                'tags'
            ])->findOrFail($id);
            
            $departments = [];

            // Retrieve sectors with their departments, ordered by name
            $sectors = Sector::with(['departments' => function ($query) {
                $query->orderBy('name'); // Sort departments alphabetically within each sector
            }])->orderBy('name') // Sort sectors alphabetically
            ->get();

            // Get all tags
            $tags = \App\Models\Tag::orderBy('name')->get();

            // Get timeline events for the sidebar
            $timelineEvents = $user->getTimelineEvents()->take(20);

            return view('users.edit', [
                'user'      => $user,
                'sectors'   => $sectors,
                'departments' => $departments,
                'tags'      => $tags,
                'timelineEvents' => $timelineEvents,
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
                'pronouns' => ['nullable', 'string', 'max:50'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email,' . $id],  // Ensure unique email except for this user
                'active' => ['required', 'boolean'],                     // Ensures 'active' is either 0 or 1 (boolean)
                'notes' => ['nullable', 'string'],                       // 'notes' can be a string, maximum 255 characters, or null
                'primary_sector_id' => ['nullable', 'integer', 'exists:sectors,id'],   // Ensure 'sector' is a valid integer and exists in the sectors table
                'primary_dept_id' => ['nullable', 'integer', 'exists:departments,id'],   // Ensure 'sector' is a valid integer and exists in the sectors table
                'admin' => ['boolean'], // Ensures 'admin' is either 0 or 1 (boolean), defaults to 0
                'departments' => 'nullable|array',
                'departments.*' => 'exists:departments,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
            ]);

            // Find the user by ID
            $user = User::findOrFail($id);

            // Update the user profile with the validated data
            $user->update($validated);

            // Sync departments
            $user->departments()->sync($validated['departments'] ?? []);

            // Sync tags
            $user->tags()->sync($validated['tags'] ?? []);

            // Handle custom fields
            $customFields = \App\Models\CustomField::active()->get();
            foreach ($customFields as $field) {
                $fieldKey = 'custom_field_' . $field->id;
                
                if ($request->has($fieldKey)) {
                    $value = $request->input($fieldKey);
                    
                    // Handle checkbox fields (convert array to comma-separated string)
                    if ($field->field_type === 'checkbox' && is_array($value)) {
                        $value = implode(',', $value);
                    }
                    
                    // Update or create the custom field value
                    \App\Models\CustomFieldValue::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'custom_field_id' => $field->id,
                        ],
                        [
                            'value' => $value,
                        ]
                    );
                } else {
                    // If field is not in request (e.g., unchecked checkboxes), delete the value
                    \App\Models\CustomFieldValue::where('user_id', $user->id)
                        ->where('custom_field_id', $field->id)
                        ->delete();
                }
            }

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

    public function export(Request $request) {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        // Filter inputs - same as index method
        $departmentFilter = $request->input('departments', []);
        $tagFilter = $request->input('tags', []);
        $statusFilter = $request->input('status');
        $departmentStatus = $request->input('department_status');

        // Get the current fiscal ledger
        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        // Ensure we have a ledger to work with
        if (!$currentLedger) {
            abort(500, 'Current fiscal ledger not found.');
        }

        // Apply the same filters as index method
        $users = User::query()
            ->when($search, function (Builder $query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('vol_code', $search);
            })
            ->when(!empty($departmentFilter), function (Builder $query) use ($departmentFilter) {
                $query->whereHas('departments', function ($q) use ($departmentFilter) {
                    $q->whereIn('departments.id', $departmentFilter);
                });
            })
            ->when($departmentStatus === 'no_department', function (Builder $query) {
                $query->doesntHave('departments');
            })
            ->when($departmentStatus === 'has_department', function (Builder $query) {
                $query->has('departments');
            })
            ->when(!empty($tagFilter), function (Builder $query) use ($tagFilter) {
                $query->whereHas('tags', function ($q) use ($tagFilter) {
                    $q->whereIn('tags.id', $tagFilter);
                });
            })
            ->when($statusFilter === 'active', function (Builder $query) {
                $query->where('active', true);
            })
            ->when($statusFilter === 'inactive', function (Builder $query) {
                $query->where('active', false);
            })
            ->when($sort === 'hours', function (Builder $query) use ($currentLedger, $direction) {
                $query->withSum(['volunteerHours' => function ($q) use ($currentLedger) {
                    $q->where('fiscal_ledger_id', $currentLedger->id);
                }], 'hours')
                ->orderBy('volunteer_hours_sum_hours', $direction);
            }, function (Builder $query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            })
            ->get(); // Get all matching users (no pagination for export)

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
                      ->orWhere('vol_code', 'LIKE', "%" . $searchTerm . "%")
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

    /**
     * Display communications sent to a user
     */
    public function communications(string $id): View
    {
        $user = User::findOrFail($id);
        
        // Get communication logs for this user, ordered by most recent first
        $communications = $user->communicationLogs()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('users.communications', [
            'user' => $user,
            'communications' => $communications,
        ]);
    }

    /**
     * Send a test email to the user
     */
    public function sendTestEmail(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        try {
            Mail::to($user->email)->send(new TestEmail($user));
            
            // Log the communication
            CommunicationLog::create([
                'user_id' => $user->id,
                'type' => 'email',
                'subject' => 'Test Email from MNFursVolunteers',
                'message' => 'This is a test email from the MNFursVolunteers system.',
                'recipient_email' => $user->email,
                'status' => 'sent',
                'sent_by' => auth()->id(),
                'metadata' => [
                    'email_type' => 'test_email',
                ],
            ]);
            
            return redirect()->route('users.communications', $user->id)
                ->with('success', [
                    'message' => "Test email sent successfully to {$user->email}"
                ]);
        } catch (\Exception $e) {
            // Log the failed communication
            CommunicationLog::create([
                'user_id' => $user->id,
                'type' => 'email',
                'subject' => 'Test Email from MNFursVolunteers',
                'message' => 'This is a test email from the MNFursVolunteers system.',
                'recipient_email' => $user->email,
                'status' => 'failed',
                'sent_by' => auth()->id(),
                'metadata' => [
                    'email_type' => 'test_email',
                    'error' => $e->getMessage(),
                ],
            ]);
            
            Log::error('Failed to send test email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('users.communications', $user->id)
                ->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

}
