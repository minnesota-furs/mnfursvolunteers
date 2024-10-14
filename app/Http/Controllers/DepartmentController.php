<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sector;
use App\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::all();

        return view('departments.index', [
            'departments'     => $departments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) : View
    {
        if($request->user()->isAdmin())
        {
            return view('departments.create');
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
        $validated = $reqe
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $department = Department::findOrFail($id);
        return view('departments.show', [
            'department' => $department
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $department = Department::findOrFail($id);
        return view('departments.edit', [
            'department' => $department
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
