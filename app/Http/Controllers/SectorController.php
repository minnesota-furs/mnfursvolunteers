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
        ]);

        // Create a new Fiscal Ledger with the validated data
        Sector::create([
            'name' => $validated['name']
        ]);

        // Redirect to a desired route (for example, back to a list of fiscal ledgers)
        return redirect()->route('sector.index')
            ->with('success', [
                'message' => "Sector Created Successfully"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sector = Sector::findOrFail($id);
        return view('ledger.show', [
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
