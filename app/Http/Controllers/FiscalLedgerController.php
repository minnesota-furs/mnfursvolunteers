<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use App\Models\FiscalLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class fiscalLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ledgers = FiscalLedger::orderBy('end_date', 'desc')->get();

        return view('ledgers.index', [
            'ledgers'     => $ledgers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(401);
        }

        return view('ledgers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(401);
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',               // Validate name (required, string, max 255 characters)
            'start_date' => 'required|date|before:end_date',    // Validate start_date (required, must be a valid date, before the end date)
            'end_date' => 'required|date|after:start_date',     // Validate end_date (required, must be a valid date, after the start date)
        ]);

        // Check for overlapping fiscal ledgers
        $overlappingLedger = FiscalLedger::where(function ($query) use ($request) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                ->orWhere(function ($query) use ($request) {
                    $query->where('start_date', '<=', $request->start_date)
                        ->where('end_date', '>=', $request->end_date);
                });
        })->first();  // Instead of checking for existence, get the first overlapping ledger

        // If an overlap is found, return an error message with the ledger name
        if ($overlappingLedger) {
            return back()->withErrors([
                'start_date' => 'The date range overlaps with an existing fiscal ledger: ' . $overlappingLedger->name
            ])->withInput();
        }

        // Create a new Fiscal Ledger with the validated data
        FiscalLedger::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        // Redirect to a desired route (for example, back to a list of fiscal ledgers)
        return redirect()->route('ledger.index')
            ->with('success', [
                'message' => "Ledger Created Successfully"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(401);
        }

        $ledger = FiscalLedger::findOrFail($id);
        return view('ledger.show', [
            'ledger' => $ledger
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(401);
        }
        $ledger = FiscalLedger::findOrFail($id);
        return view('ledgers.edit', [
            'ledger' => $ledger
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(401);
        }
        
        // Validate the incoming request data
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'start_date' => 'required|date|before:end_date',    // Validate start_date (required, must be a valid date, before the end date)
            'end_date'   => 'required|date|after:start_date',     // Validate end_date (required, must be a valid date, after the start date)
        ]);

        // Find the user by ID
        $ledger = FiscalLedger::findOrFail($id);

        // Update the user profile with the validated data
        $ledger->update($validated);

        // Optionally, flash a success message to the session
        return redirect()->route('ledger.index')
            ->with('success', [
                'message' => "Ledger <span class=\"text-brand-green\">{$ledger->name}</span> updated successfully",
                'action_text' => 'Edit Ledger',
                'action_url' => route('ledger.edit', $ledger->id),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function exportCsv($id)
{
    $ledger = FiscalLedger::with('users')->findOrFail($id);

    // Prepare CSV data
    $header = ['User ID', 'Name', 'Email', 'Total Hours'];
    $rows = [];

    foreach ($ledger->users as $user) {
        $totalHours = $user->volunteerHours()
            ->where('fiscal_ledger_id', $ledger->id)
            ->sum('hours');

        $rows[] = [
            $user->id,
            $user->name,
            $user->email,
            $totalHours,
        ];
    }

    // Create the CSV content
    $csvContent = implode(',', $header) . "\n";
    foreach ($rows as $row) {
        $csvContent .= implode(',', $row) . "\n";
    }

    // Return CSV response
    return Response::make($csvContent, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=ledger_{$ledger->id}_users.csv",
    ]);
}
}
