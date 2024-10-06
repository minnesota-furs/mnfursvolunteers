<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\VolunteerHours;
use App\Models\FiscalLedger;
Use App\Models\Sector;

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
        // If a user is passed, retrieve the user from the database
        $selectedUser = $user ? User::find($user) : null;

        $sectors = Sector::with('departments')->get();
        $recentDepartments = [];

        if ($selectedUser){
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

        // Pass the user (if any) to the view
        return view('hours.create', compact('selectedUser', 'users', 'sectors', 'recentDepartments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'hours'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'notes'         => 'nullable|string',
            'volunteer_date' => 'nullable|date',
            'primary_dept_id' => 'integer|exists:departments,id',
        ]);

        // Get the current date
        $currentDate = now();

        // Find the fiscal ledger that covers the volunteer date
        $fiscalLedger = FiscalLedger::where('start_date', '<=', $currentDate)
                                    ->where('end_date', '>=', $currentDate)
                                    ->first();

        if (!$fiscalLedger) {
            // If no fiscal ledger is found, return with an error
            return back()->withErrors([
                'volunteer_date' => 'No fiscal ledger is active for the given date.'
            ])->withInput();
        }

        // Create the hour log entry
        // Create the volunteer hour and assign the fiscal ledger ID
        VolunteerHours::create([
            'user_id'   => $validated['user_id'],
            'hours'     => $validated['hours'],
            'notes'     => $validated['notes'],
            'volunteer_date' => $validated['volunteer_date'],
            'description' => $validated['description'] ?? null,
            'primary_dept_id' => $validated['primary_dept_id'] ?? null,
            'fiscal_ledger_id' => $fiscalLedger->id,  // Assign the fiscal ledger

        ]);

        $username = User::select('name')->find($validated['user_id'])->name;

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
