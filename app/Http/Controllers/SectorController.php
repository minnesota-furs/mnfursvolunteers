<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sector;
use App\Models\Department;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sectors = Sector::all();

        return view('sectors.index', [
            'sectors'     => $sectors
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sectors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       // Validate the incoming request data
       $validated = $request->validate([
            'name' => 'required|string|max:255',               // Validate name (required, string, max 255 characters)
            'url' => 'nullable|string|max:2555',
            'description' => 'nullable|string|max:255'
        ]);

        // Create a new Fiscal Ledger with the validated data
        $sector = Sector::create($validated);

        // Redirect to a desired route (for example, back to a list of fiscal ledgers)
        return redirect()->route('sectors.index')
            ->with('success', [
                'message' => "Sector <span class=\"text-brand-green\">{$sector->name}</span> created successfully",
                'action_text' => 'View Sector',
                'action_url' => route('sectors.show', $sector->id),
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sector = Sector::findOrFail($id);
        return view('sectors.show', [
            'sector' => $sector
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sector = Sector::findOrFail($id);
        return view('sectors.edit', [
            'sector' => $sector
        ]);
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
