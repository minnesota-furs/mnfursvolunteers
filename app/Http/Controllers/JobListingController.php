<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobListing;
use App\Models\Sector;
use App\Models\JobApplication;
use App\Models\ApplicationComment;

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

    /**
     * Handle job application submission from guests.
     */
    public function submitApplication(Request $request, string $id)
    {
        // Validate the application
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comments' => 'nullable|string|max:2000',
        ]);

        // Verify the job listing exists and is public
        $jobListing = JobListing::where('id', $id)
            ->where('visibility', 'public')
            ->where(function ($query) {
                $query->whereNull('closing_date')
                      ->orWhere('closing_date', '>=', now());
            })
            ->firstOrFail();

        // Create the application
        JobApplication::create([
            'job_listing_id' => $jobListing->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'comments' => $validated['comments'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->back()
            ->with('application_success', 'Thank you for your application! We\'ll review it and get back to you soon.');
    }

    /**
     * Show application form for authenticated users
     */
    public function showApplyForm(JobListing $jobListing)
    {
        $jobListing->load('department.sector');
        
        // Convert markdown to HTML
        $parsedown = new Parsedown();
        $jobListing->parsedDescription = $parsedown->text($jobListing->description);

        // Get user's current departments
        $userDepartments = auth()->user()->departments;

        return view('job-listings.apply', compact('jobListing', 'userDepartments'));
    }

    /**
     * Submit application from authenticated user
     */
    public function submitAuthenticatedApplication(Request $request, JobListing $jobListing)
    {
        $validated = $request->validate([
            'comments' => 'nullable|string|max:2000',
        ]);

        // Check if user has already applied
        $existingApplication = JobApplication::where('job_listing_id', $jobListing->id)
            ->where('email', auth()->user()->email)
            ->first();

        if ($existingApplication) {
            return redirect()->back()
                ->withErrors(['email' => 'You have already applied for this position.']);
        }

        // Create the application
        JobApplication::create([
            'job_listing_id' => $jobListing->id,
            'user_id' => auth()->id(),
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'comments' => $validated['comments'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('job-listings.show', $jobListing)
            ->with('success', [
                'message' => 'Application submitted successfully! We\'ll review it and get back to you soon.'
            ]);
    }

    /**
     * Display list of all job applications for admins.
     */
    public function applicantsList(Request $request)
    {
        $status = $request->input('status');
        $jobListingId = $request->input('job_listing');
        $sectorId = $request->input('sector');
        $showMine = $request->input('show_mine');
        $viewType = $request->input('view', 'individuals'); // Default to individuals view

        $applicationsQuery = JobApplication::with(['jobListing.department.sector', 'claimedBy', 'user'])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($jobListingId, function ($query, $jobListingId) {
                $query->where('job_listing_id', $jobListingId);
            })
            ->when($sectorId, function ($query, $sectorId) {
                $query->whereHas('jobListing.department', function ($q) use ($sectorId) {
                    $q->where('sector_id', $sectorId);
                });
            })
            ->when($showMine, function ($query) {
                $query->where('claimed_by', auth()->id());
            });

        if ($viewType === 'grouped') {
            // For grouped view, get all applications (no pagination) and group by job listing
            $applications = $applicationsQuery
                ->orderBy('job_listing_id')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('job_listing_id');
            
            $jobListings = JobListing::orderBy('position_title')->get();
            $sectors = \App\Models\Sector::orderBy('name')->get();
            
            return view('job-listings.applicants', compact('applications', 'jobListings', 'sectors', 'viewType'));
        } else {
            // For individuals view, paginate as before
            $applications = $applicationsQuery
                ->orderBy('created_at', 'desc')
                ->paginate(25)
                ->appends($request->query());

            $jobListings = JobListing::orderBy('position_title')->get();
            $sectors = \App\Models\Sector::orderBy('name')->get();

            return view('job-listings.applicants', compact('applications', 'jobListings', 'sectors', 'viewType'));
        }
    }

    /**
     * Show detailed application page.
     */
    public function showApplication(JobApplication $application)
    {
        $application->load(['jobListing.department.sector', 'claimedBy', 'internalComments.user', 'user']);
        
        return view('job-listings.applicants-show', compact('application'));
    }

    /**
     * Update application status.
     */
    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected',
        ]);

        $application->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', [
                'message' => 'Application status updated successfully.'
            ]);
    }

    /**
     * Delete an application.
     */
    public function deleteApplication(JobApplication $application)
    {
        $application->delete();

        return redirect()->back()
            ->with('success', [
                'message' => 'Application deleted successfully.'
            ]);
    }

    /**
     * Show form to manually create an application
     */
    public function createApplication()
    {
        $jobListings = JobListing::with('department')
            ->orderBy('position_title')
            ->get();

        return view('job-listings.applicants-create', compact('jobListings'));
    }

    /**
     * Store a manually created application
     */
    public function storeManualApplication(Request $request)
    {
        $validated = $request->validate([
            'job_listing_id' => 'required|exists:job_listings,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comments' => 'nullable|string',
            'status' => 'required|in:pending,reviewed,accepted,rejected',
            'internal_note' => 'nullable|string',
        ]);

        $application = JobApplication::create([
            'job_listing_id' => $validated['job_listing_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'comments' => $validated['comments'],
            'status' => $validated['status'],
        ]);

        // If an internal note was provided, create a comment
        if (!empty($validated['internal_note'])) {
            $application->internalComments()->create([
                'user_id' => auth()->id(),
                'comment' => $validated['internal_note'],
            ]);
        }

        return redirect()->route('job-listings.applicants.show', $application)
            ->with('success', [
                'message' => 'Application created successfully.'
            ]);
    }

    /**
     * Claim an application (assign to current admin).
     */
    public function claimApplication(JobApplication $application)
    {
        $application->update([
            'claimed_by' => auth()->id(),
            'claimed_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', [
                'message' => 'Application claimed successfully.'
            ]);
    }

    /**
     * Unclaim an application.
     */
    public function unclaimApplication(JobApplication $application)
    {
        // Only allow unclaiming if the current user claimed it or is an admin
        if ($application->claimed_by === auth()->id() || auth()->user()->isAdmin()) {
            $application->update([
                'claimed_by' => null,
                'claimed_at' => null,
            ]);

            return redirect()->back()
                ->with('success', [
                    'message' => 'Application unclaimed successfully.'
                ]);
        }

        return redirect()->back()
            ->with('error', 'You do not have permission to unclaim this application.');
    }

    /**
     * Store a new comment for an application.
     */
    public function storeComment(Request $request, JobApplication $application)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        ApplicationComment::create([
            'job_application_id' => $application->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()->back()
            ->with('success', [
                'message' => 'Comment added successfully.'
            ]);
    }
}
