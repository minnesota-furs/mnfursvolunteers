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
        $departments = Department::all();
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
        if($request->user()->isAdmin())
        {
            $sectors = Sector::all();
            return view('departments.create', [
                'sectors'   => $sectors,
            ]);
        }
        else
        {
            return view('dashboard');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : RedirectResponse
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name' => ['required','string','max:255'], // required string, max len 255
            'description' => ['nullable','string','max:255'],  // optional string, max len 255
            'sector_id' => ['required','integer','exists:sectors,id']     // Ensure 'sector' is a valid integer and exists in the sectors table
        ]);

        // Programmatically determine the department ID of this new department
        $lastDepartmentId = Department::latest()->first()->id ?? -1; //if no other department exists, start at -1+1 (0)
        $newDepartmentId = $lastDepartmentId + 1;
        $validated['id'] = $newDepartmentId; 

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
        if($request->user()->isAdmin())
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
            return view('dashboard');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) : RedirectResponse
    {
        dd("reached department.update function");

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
        return redirect()->route('dashboard')
            ->with('success', [
                'message' => "Department <span class=\"text-brand-green\">{$department->name}</span> updated successfully",
                'action_text' => 'View Department',
                'action_url' => route('departments.show', $department->id),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, string $id): View
    {
        if($request->user()->isAdmin())
        {
            $department = Department::find($id);
            return view('departments.delete', [
                'department'   => $department,
            ]);
        }
        else
        {
            return $this->index($request);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id) : RedirectResponse
    {
        if($request->user()->isAdmin())
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
            return $this->index($request);
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
