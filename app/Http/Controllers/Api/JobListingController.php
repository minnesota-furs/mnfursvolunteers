<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\JobListing;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $query = JobListing::query()
            ->where('visibility', 'public') // Only public listings
            ->where(function ($query) {
                $query->whereNull('closing_date') // No closing date
                    ->orWhere('closing_date', '>=', now()); // Still open
            });

        // Apply optional additional data
        if ($request->has('with_dept')) {
            $query->with('department.sector');
        }

        // Apply optional filters
        if ($request->has('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->has('title')) {
            $query->where('position_title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->has('sector_id')) {
            $query->whereHas('department', function ($query) use ($request) {
                $query->where('sector_id', $request->input('sector_id'));
            });
        }

        $jobListings = $query->get();

        if ($request->has('with_html_markdown')) {
            $jobListings->each(function ($listing) {
                $listing->description_html = $listing->html_description; // Replace with HTML version
            });
        }

        // Return the data in JSON format
        return response()->json([
            'status' => 'success',
            'data' => $jobListings,
        ]);
    }
}
