<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\Sector;
use App\Models\User;
use App\Models\VolunteerHours;
use App\Models\VolunteerPerkSet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Determine whether any volunteer perks are currently configured, so the
     * "counts toward perks" toggle is only shown when it would have an effect.
     */
    protected function hasActivePerksConfigured(): bool
    {
        if (! feature_enabled('perk_tracking')) {
            return false;
        }

        return VolunteerPerkSet::current()
            ->whereHas('perks', fn ($q) => $q->active())
            ->exists();
    }

    /**
     * Perk sets a staff member can attribute manually-logged hours toward.
     */
    protected function selectablePerkSets(): Collection
    {
        return VolunteerPerkSet::current()
            ->whereHas('perks', fn ($q) => $q->active())
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($user = null)
    {
        if (! Auth::user()->isAdmin()) {
            if ($user == null || $user != Auth::id()) {
                abort(401);
            }
        }

        // If a user is passed, retrieve the user from the database
        $selectedUser = $user ? User::find($user) : null;

        $sectors = Sector::with('departments')->get();
        $recentDepartments = [];

        if ($selectedUser) {
            // Fetch the last 5 departments the user has used, based on volunteer hours
            $recentDepartments = Department::whereIn('id',
                $selectedUser->volunteerHours()
                    ->select('primary_dept_id', 'created_at')
                    ->distinct()
                    ->latest('created_at')
                    ->limit(5)
                    ->pluck('primary_dept_id')
            )->get();
            $users = null;

        } else {
            $users = User::all();
        }

        $hasActivePerks = $this->hasActivePerksConfigured();
        $perkSets = $hasActivePerks ? $this->selectablePerkSets() : collect();

        // Pass the user (if any) to the view
        return view('hours.create', compact('selectedUser', 'users', 'sectors', 'recentDepartments', 'hasActivePerks', 'perkSets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hours' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'volunteer_date' => 'nullable|date',
            'primary_dept_id' => 'integer|exists:departments,id',
            'perk_set_id' => 'nullable|exists:volunteer_perk_sets,id',
        ]);

        // Get the current date
        $currentDate = now();

        // dd($request->all());

        // Find the fiscal ledger that covers the volunteer date
        $fiscalLedger = FiscalLedger::where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->first();

        if (! $fiscalLedger) {
            // If no fiscal ledger is found, return with an error
            return back()->withErrors([
                'volunteer_date' => 'No fiscal ledger is active for the given date.',
            ])->withInput();
        }

        $countsTowardPerks = $this->hasActivePerksConfigured() ? $request->has('counts_toward_perks') : true;

        // Create the hour log entry
        // Create the volunteer hour and assign the fiscal ledger ID
        VolunteerHours::create([
            'user_id' => $validated['user_id'],
            'hours' => $validated['hours'],
            'notes' => $validated['notes'],
            'volunteer_date' => $validated['volunteer_date'],
            'description' => $validated['description'] ?? null,
            'primary_dept_id' => $validated['primary_dept_id'] ?? null,
            'counts_toward_perks' => $countsTowardPerks,
            'perk_set_id' => $countsTowardPerks ? ($validated['perk_set_id'] ?? null) : null,
            'fiscal_ledger_id' => $fiscalLedger->id,  // Assign the fiscal ledger
        ]);

        $username = User::select('name')->find($validated['user_id'])->name;

        // Redirect back with a success message
        return redirect()->route('users.show', $validated['user_id'])
            ->with('success', [
                'message' => "<span class=\"text-brand-green\">{$validated['hours']}</span> volunteer ".($validated['hours'] == 1 ? 'hour' : 'hours')." logged successfully for <span class=\"text-brand-green\">{$username}</span>.",
                'action_text' => 'View User',
                'action_url' => route('users.show', $validated['user_id']),
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hour = VolunteerHours::find($id);

        return view('hours.show', compact('hour'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (! Auth::user()->isAdmin() && Auth::id() != VolunteerHours::find($id)->user_id) {
            abort(401);
        }

        $hour = VolunteerHours::find($id);
        $ledgers = FiscalLedger::all();

        $selectedUser = $hour->user;

        $sectors = Sector::with('departments')->get();
        $recentDepartments = [];

        // Fetch the last 5 departments the user has used, based on volunteer hours
        $recentDepartments = Department::whereIn('id',
            $selectedUser->volunteerHours()
                ->select('primary_dept_id', 'created_at')
                ->distinct()
                ->latest('created_at')
                ->limit(5)
                ->pluck('primary_dept_id')
        )->get();
        $users = null;

        $hasActivePerks = $this->hasActivePerksConfigured();
        $perkSets = $hasActivePerks ? $this->selectablePerkSets() : collect();

        // Pass the user (if any) to the view
        return view('hours.edit', compact('hour', 'selectedUser', 'users', 'sectors', 'recentDepartments', 'ledgers', 'hasActivePerks', 'perkSets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (! Auth::user()->isAdmin() && Auth::id() != VolunteerHours::find($id)->user_id) {
            abort(401);
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hours' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'volunteer_date' => 'nullable|date',
            'primary_dept_id' => 'integer|exists:departments,id',
            'fiscal_ledger_id' => 'integer',
            'perk_set_id' => 'nullable|exists:volunteer_perk_sets,id',
        ]);

        // Find the user by ID
        $hour = VolunteerHours::findOrFail($id);

        // Only touch the perk-eligibility fields when the toggle was actually shown
        // on the form; otherwise leave the record's existing values untouched.
        if ($this->hasActivePerksConfigured()) {
            $countsTowardPerks = $request->has('counts_toward_perks');
            $validated['counts_toward_perks'] = $countsTowardPerks;
            $validated['perk_set_id'] = $countsTowardPerks ? ($validated['perk_set_id'] ?? null) : null;
        } else {
            unset($validated['perk_set_id']);
        }

        // Update the user profile with the validated data
        $hour->update($validated);

        // Optionally, flash a success message to the session
        return redirect()->route('users.show', $hour->user->id)
            ->with('success', [
                'message' => "Hour Record <span class=\"text-brand-green\"># {$hour->id}</span> updated successfully for <span class=\"text-brand-green\">{$hour->user->name}</span>",
                'action_text' => 'View Hour',
                'action_url' => route('hours.show', $hour->id),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Generate a unique token for public hour submission.
     */
    public function generateSubmissionToken(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Only admins or the user themselves can generate tokens
        if (! Auth::user()->isAdmin() && Auth::id() != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $token = $user->generateHourSubmissionToken();
        $url = $user->getHourSubmissionUrl();

        return redirect()->route('users.show', $user->id)
            ->with('success', [
                'message' => "Hour submission link generated successfully for <span class=\"text-brand-green\">{$user->name}</span>. This link will expire on <span class=\"text-brand-green\">{$user->token_expires_at->format('F j, Y')}</span>.",
                'action_text' => 'Copy Link',
                'action_url' => $url,
            ])
            ->with('copy_to_clipboard', $url);
    }

    /**
     * Show the public hour submission form.
     */
    public function showPublicForm($token)
    {
        $user = User::where('hour_submission_token', $token)->firstOrFail();

        if (! $user->hasValidHourSubmissionToken()) {
            abort(403, 'This hour submission link has expired or is invalid.');
        }

        $sectors = Sector::with('departments')->get();

        // Fetch the last 5 departments the user has used
        $recentDepartments = Department::whereIn('id',
            $user->volunteerHours()
                ->select('primary_dept_id', 'created_at')
                ->distinct()
                ->latest('created_at')
                ->limit(5)
                ->pluck('primary_dept_id')
        )->get();

        // Get current fiscal year hours
        $currentFiscalYearHours = $user->totalHoursForCurrentFiscalLedger();

        // Get current fiscal ledger
        $currentDate = now();
        $currentFiscalLedger = FiscalLedger::where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->first();

        // Get recent hour submissions for current fiscal year
        $recentHours = collect();
        if ($currentFiscalLedger) {
            $recentHours = $user->volunteerHours()
                ->where('fiscal_ledger_id', $currentFiscalLedger->id)
                ->with('department')
                ->latest('volunteer_date')
                ->latest('created_at')
                ->limit(10)
                ->get();
        }

        return view('hours.public-create', compact('user', 'token', 'sectors', 'recentDepartments', 'currentFiscalYearHours', 'currentFiscalLedger', 'recentHours'));
    }

    /**
     * Store publicly submitted volunteer hours.
     */
    public function storePublicHours(Request $request, $token)
    {
        $user = User::where('hour_submission_token', $token)->firstOrFail();

        if (! $user->hasValidHourSubmissionToken()) {
            abort(403, 'This hour submission link has expired or is invalid.');
        }

        // Validate the incoming request
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'volunteer_date' => 'required|date',
            'primary_dept_id' => 'required|integer|exists:departments,id',
        ]);

        // Get the volunteer date
        $volunteerDate = Carbon::parse($validated['volunteer_date']);

        // Find the fiscal ledger that covers the volunteer date
        $fiscalLedger = FiscalLedger::where('start_date', '<=', $volunteerDate)
            ->where('end_date', '>=', $volunteerDate)
            ->first();

        if (! $fiscalLedger) {
            return back()->withErrors([
                'volunteer_date' => 'No fiscal ledger is active for the given date.',
            ])->withInput();
        }

        // Create the volunteer hour entry
        VolunteerHours::create([
            'user_id' => $user->id,
            'hours' => $validated['hours'],
            'notes' => $validated['notes'],
            'volunteer_date' => $validated['volunteer_date'],
            'description' => $validated['description'] ?? null,
            'primary_dept_id' => $validated['primary_dept_id'],
            'fiscal_ledger_id' => $fiscalLedger->id,
        ]);

        return redirect()->route('hours.public.show', ['token' => $token])
            ->with('success', [
                'message' => "<span class=\"text-brand-green\">{$validated['hours']}</span> volunteer ".($validated['hours'] == 1 ? 'hour' : 'hours').' logged successfully!',
            ]);
    }
}
