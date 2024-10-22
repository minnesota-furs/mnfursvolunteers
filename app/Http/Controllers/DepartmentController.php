<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Sector;
use App\Models\Department;


use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::join('sectors', 'departments.sector_id', '=', 'sectors.id')
                             ->select('departments.*')
                             ->orderBy('sectors.name')
                             ->orderBy('departments.name')
                             ->get();
        $sectors = Sector::all();

        return view('departments.index', [
            'departments'     => $departments,
            'sectors'         => $sectors
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) : View
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $sectors = Sector::all();
            return view('departments.create', [
                'sectors'   => $sectors,
            ]);
        }
        else
        {
            abort(401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : RedirectResponse
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['required','string','max:255'], // required string, max len 255
                'description' => ['nullable','string','max:255'],  // optional string, max len 255
                'sector_id' => ['required','integer','exists:sectors,id']     // Ensure 'sector' is a valid integer and exists in the sectors table
            ]);

            // Create the department
            $department = Department::create($validated);

            // Redirect user to departments list
            // Optionally, flash a success message to the session
            return redirect()->route('departments.index')
                ->with('success', [
                    'message' => "Department <span class=\"text-brand-green\">{$department->name}</span> created successfully",
                    'action_text' => 'View Department',
                    'action_url' => route('departments.show', $department->id),
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
        $department = Department::findOrFail($id);
        $department['parent_sector_name'] = Sector::where('id', $department->sector_id)->first()->name ?? "Name Error";
        return view('departments.show', [
            'department' => $department
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $department = Department::findOrFail($id);
            $sectors = Sector::all();
            return view('departments.edit', [
                'department' => $department,
                'sectors' => $sectors
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
    public function update(Request $request, string $id) : RedirectResponse
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            // Validate the incoming request data
            $validated = $request->validate([
                'name' => ['required','string','max:255'], // required string, max len 255
                'description' => ['nullable','string','max:255'],  // optional string, max len 255
                'sector_id' => ['required','integer','exists:sectors,id']     // Ensure 'sector' is a valid integer and exists in the sectors table
            ]);

            // Find the department by ID
            $department = Department::findOrFail($id);

            // Update the department profile with the validated data
            $department->update($validated);

            // Optionally, flash a success message to the session
            return redirect()->route('departments.index')
                ->with('success', [
                    'message' => "Department <span class=\"text-brand-green\">{$department->name}</span> updated successfully",
                    'action_text' => 'View Department',
                    'action_url' => route('departments.show', $department->id),
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
    public function delete(Request $request, string $id): View
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $department = Department::findOrFail($id);
            return view('departments.delete_confirm', [
                'department'   => $department,
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
    public function destroy(Request $request, string $id) : RedirectResponse
    {
        if(Auth::check() && Auth::user()->isAdmin())
        {
            $department = Department::findOrFail($id);
            $department->delete();
            return redirect()->route('departments.index')
            ->with('success', [
                'message' => "Department <span class=\"text-brand-red\">{$department->name}</span> deleted successfully",
            ]);
        }
        else
        {
            abort(401);
        }
    }

    public function getDepartmentsBySector(Request $request)
    {
        // Validate the sector_id parameter
        $sectorId = $request->get('sector_id');

        // Fetch the departments that belong to the selected sector
        $departments = Department::where('sector_id', $sectorId)->get();

        // Return the departments as JSON
        return response()->json($departments);
    }
}
