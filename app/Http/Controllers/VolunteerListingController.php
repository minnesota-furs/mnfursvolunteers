<?php

namespace App\Http\Controllers;

use App\Models\Event;

use Illuminate\Http\Request;
use Parsedown;

class VolunteerListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function guestIndex()
    {
        $events = Event::orderBy('start_date', 'asc')
            ->where('end_date', '>=', now())
            ->get();

        return view('vol-listings-guest.index', compact('events'));
    }

    public function guestShow(Event $event)
    {
        $shifts = $event->shifts()->orderBy('start_time')->get();

        // $jobListing = JobListing::with('department')
        //     ->where('id', $id)
        //     ->where('visibility', 'public')
        //     ->where(function ($query) {
        //         $query->whereNull('closing_date') // No closing date
        //               ->orWhere('closing_date', '>=', now()); // Closing date not passed
        //     })
        //     ->firstOrFail();

        // // Convert markdown to HTML using Parsedown
        // $parsedown = new Parsedown();
        // $jobListing->parsedDescription = $parsedown->text($jobListing->description);
        
        return view('vol-listings-guest.show', compact('event', 'shifts'));
    }
}
