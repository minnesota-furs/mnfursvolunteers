<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobListing;
use App\Models\Sector;

use Illuminate\Http\Request;
use Parsedown;

class JobListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $isAdmin = auth()->user() && auth()->user()->isAdmin();

        // Query job listings
        $jobListings = JobListing::query()
            ->when(!$isAdmin, function ($query) {
                // For non-admins, exclude drafts
                $query->where('visibility', '!=', 'draft');
            })
            ->when($request->filled('sector'), function ($query) use ($request) {
                // Filter by sector
                $query->whereHas('department', function ($q) use ($request) {
                    $q->where('sector_id', $request->input('sector'));
                });
            })
            ->with('department')
            ->orderBy(
                in_array($request->input('sort'), ['position_title', 'closing_date']) ? $request->input('sort') : 'position_title',
                $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc'
            )
            ->paginate(15);

        $trashedListings = JobListing::onlyTrashed()->get();
        $sectors = Sector::all(); // Fetch all sectors for the filter dropdown
        $selectedSector = $request->input('sector');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        return view('job-listings.index', compact('jobListings', 'trashedListings', 'sectors', 'selectedSector', 'sort', 'direction'));
    }

    /**
     * Display a listing of the resource.
     */
    public function guestIndex(Request $request)
    {
        // Get all sectors for filter dropdown
        $sectors = Sector::orderBy('name')->get();
        
        // Get sectors with their departments that have open public job listings (grouped)
        $sectorsWithDepartments = Sector::with(['departments' => function ($query) {
                $query->whereHas('jobListings', function ($q) {
                    $q->where('visibility', 'public')
                        ->where(function ($subQ) {
                            $subQ->whereNull('closing_date')
                                ->orWhere('closing_date', '>=', now());
                        });
                })
                ->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->filter(function ($sector) {
                return $sector->departments->isNotEmpty();
            });

        // Query job listings
        $jobListings = JobListing::query()
            ->where('visibility', 'public')
            ->with(['department.sector'])
            ->where(function ($query) {
                $query->whereNull('closing_date') // No closing date
                    ->orWhere('closing_date', '>=', now()); // Still open
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('position_title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            })
            ->when($request->filled('sector'), function ($query) use ($request) {
                // Filter by sector
                $query->whereHas('department', function ($q) use ($request) {
                    $q->where('sector_id', $request->input('sector'));
                });
            })
            ->when($request->filled('department'), function ($query) use ($request) {
                // Filter by department
                $query->where('department_id', $request->input('department'));
            })
            ->orderBy('position_title', 'asc')
            ->paginate(15)
            ->appends($request->query()); // Preserve query parameters in pagination links

        return view('job-listings-guest.index', compact('jobListings', 'sectors', 'sectorsWithDepartments'));
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
        $jobListing = JobListing::with('department')
            ->where('id', $id)
            ->where('visibility', 'public')
            ->where(function ($query) {
                $query->whereNull('closing_date') // No closing date
                      ->orWhere('closing_date', '>=', now()); // Closing date not passed
            })
            ->firstOrFail();

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
                'action_text' => 'Undo Deletion',
                'action_url' => route('job-listings.restore', $jobListing->id),
            ]);
    }

    public function restore($id)
    {
        $jobListing = JobListing::onlyTrashed()->findOrFail($id);

        // Restore the soft-deleted record
        $jobListing->restore();

        return redirect()->route('job-listings.index')
            ->with('success', [
                'message' => "Listing Restored Successfully"
            ]);
    }
}
