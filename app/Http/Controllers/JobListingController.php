<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Parsedown;

class JobListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if the user is an admin
        $isAdmin = auth()->user()->isAdmin(); // c
        // Query job listings
        $jobListings = JobListing::query()
            ->when(!$isAdmin, function ($query) {
                // For non-admins, exclude drafts
                $query->where('visibility', '!=', 'draft');
            })
            ->with('department')
            ->paginate(15);
        return view('job-listings.index', compact('jobListings'));
    }

    /**
     * Display a listing of the resource.
     */
    public function guestIndex()
    {
        // Query job listings
        $jobListings = JobListing::query()
            ->where('visibility', 'public')
            ->with('department')
            ->paginate(2);
        return view('job-listings-guest.index', compact('jobListings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        return view('job-listings.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_title' => 'required|string|max:255',
            'visibility' => 'required|in:draft,public,internal',
            'description' => 'required|string',
            'number_of_openings' => 'required|integer|min:1',
            'closing_date' => 'nullable|date|after:today',
        ]);

        JobListing::create($validated);

        return redirect()->route('job-listings.index')
            ->with('success', [
                'message' => "Position Created Successfully"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobListing = JobListing::with('department')->findOrFail($id);

        // Convert markdown to HTML using Parsedown
        $parsedown = new Parsedown();
        $jobListing->parsedDescription = $parsedown->text($jobListing->description);
        
        return view('job-listings.show', compact('jobListing'));
    }

    public function guestShow(string $id)
    {
        $jobListing = JobListing::with('department')->findOrFail($id);

        // Convert markdown to HTML using Parsedown
        $parsedown = new Parsedown();
        $jobListing->parsedDescription = $parsedown->text($jobListing->description);
        
        return view('job-listings-guest.show', compact('jobListing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jobListing = JobListing::findOrFail($id);
        $departments = Department::all(); // Fetch all departments for the dropdown
        return view('job-listings.edit', compact('jobListing', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_title' => 'required|string|max:255',
            'visibility' => 'required|in:draft,public,internal',
            'description' => 'required|string',
            'number_of_openings' => 'required|integer|min:1',
            'closing_date' => 'nullable|date|after:today',
        ]);
    
        $jobListing = JobListing::findOrFail($id);
        $jobListing->update($validated);
    
        return redirect()->route('job-listings.show', $jobListing->id)
            ->with('success', [
                'message' => "Position Updated Successfully"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the job listing by ID or fail
        $jobListing = JobListing::findOrFail($id);

        // Delete the job listing
        $jobListing->delete();

        // Redirect back to the index page with a success message
        return redirect()->route('job-listings.index')
            ->with('success', [
                'message' => "Position Deleted",
                'action_text' => 'Undo',
                'action_url' => route('job-listings.show', $jobListing->id),
            ]);
    }

    public function restore($id)
    {
        $jobListing = JobListing::onlyTrashed()->findOrFail($id);

        // Restore the soft-deleted record
        $jobListing->restore();

        return redirect()->route('job-listings.trash')->with('success', 'Job Listing restored successfully.');
    }
}
