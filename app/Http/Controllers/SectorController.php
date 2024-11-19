<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Sector;
use App\Models\Department;


class SectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sectors = Sector::withCount('users')->get();

        return view('sectors.index', [
            'sectors'     => $sectors
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            return view('sectors.create');
        }
        else
        {
            abort(401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Auth::check() && Auth::user()->isAdmin())
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
        else
        {
            abort(401);
        }
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
    public function edit(Request $request, string $id)
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $sector = Sector::findOrFail($id);
            return view('sectors.edit', [
                'sector' => $sector
            ]);
        }
        else
        {
            abort(401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',               // Validate name (required, string, max 255 characters)
                'url' => 'nullable|string|max:2555',
                'description' => 'nullable|string|max:255'
            ]);

            // Find the department by ID
            $sector = Sector::findOrFail($id);

            // Update the department profile with the validated data
            $sector->update($validated);

            // Optionally, flash a success message to the session
            return redirect()->route('sectors.index')
                ->with('success', [
                    'message' => "Sector <span class=\"text-brand-green\">{$sector->name}</span> updated successfully",
                    'action_text' => 'View Sector',
                    'action_url' => route('sectors.show', $sector->id),
                ]);
        }
        else
        {
            abort(401);
        }
    }

    public function delete(Request $request, string $id)
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $sector = Sector::findOrFail($id);
            return view('sectors.delete_confirm', [
                'sector' => $sector
            ]);
        }
        else
        {
            abort(401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $sector = Sector::findOrFail($id);
            $sector->delete();
            return redirect()->route('sectors.index')
            ->with('success', [
                'message' => "Sector <span class=\"text-brand-red\">{$sector->name}</span> deleted successfully",
            ]);
        }
        else
        {
            abort(401);
        }
    }
}
