<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\User;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::with(['candidates', 'votes'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admin.elections.index', compact('elections'));
    }

    public function create()
    {
        return view('admin.elections.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nomination_start_date' => 'nullable|date',
            'nomination_end_date' => 'nullable|date|after:nomination_start_date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_positions' => 'required|integer|min:1',
            'allow_self_nomination' => 'boolean',
            'requires_approval' => 'boolean',
            'min_candidate_hours' => 'nullable|numeric|min:0',
            'min_voter_hours' => 'nullable|numeric|min:0',
        ]);

        $election = Election::create($validated);

        return redirect()->route('admin.elections.show', $election)
            ->with('success', 'Election created successfully.');
    }

    public function show(Election $election)
    {
        $election->load(['candidates.user', 'votes']);
        
        // Get vote counts for each candidate
        $candidates = $election->candidates()
            ->with('user')
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();

        $totalVotes = $election->votes()->count();

        return view('admin.elections.show', compact('election', 'candidates', 'totalVotes'));
    }

    public function edit(Election $election)
    {
        return view('admin.elections.edit', compact('election'));
    }

    public function update(Request $request, Election $election)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nomination_start_date' => 'nullable|date',
            'nomination_end_date' => 'nullable|date|after:nomination_start_date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_positions' => 'required|integer|min:1',
            'allow_self_nomination' => 'boolean',
            'requires_approval' => 'boolean',
            'active' => 'boolean',
            'min_candidate_hours' => 'nullable|numeric|min:0',
            'min_voter_hours' => 'nullable|numeric|min:0',
        ]);

        $election->update($validated);

        return redirect()->route('admin.elections.show', $election)
            ->with('success', [
                'message' => "Election updated successfully",
            ]);
    }

    public function destroy(Election $election)
    {
        $election->delete();

        return redirect()->route('admin.elections.index')
            ->with('success', [
                'message' => "Election deleted successfully",
            ]);
    }

    public function candidates(Election $election)
    {
                $candidates = $election->candidates()
            ->with('user')
            ->when($election->resultsAreVisible(), function ($query) {
                return $query->withCount('votes');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.elections.candidates', compact('election', 'candidates'));
    }

    public function approveCandidate(Election $election, Candidate $candidate)
    {
        $candidate->update(['approved' => true]);

        return back()->with('success', [
                'message' => "Candidate approved successfully",
            ]);
    }

    public function rejectCandidate(Election $election, Candidate $candidate)
    {
        $candidate->update(['approved' => false]);

        return back()->with('success', [
                'message' => "Candidate rejected successfully",
            ]);
    }

    public function results(Election $election)
    {
        if (!$election->resultsAreVisible()) {
            return redirect()->route('admin.elections.show', $election)
                ->with('error', 'Results will be available after the voting period ends on ' . $election->end_date->format('M j, Y g:i A'));
        }

        $candidates = $election->candidates()
            ->with('user')
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();

        $totalVotes = $election->votes()->count();
        $eligibleVoters = User::where('active', true)->count();

        return view('admin.elections.results', compact('election', 'candidates', 'totalVotes', 'eligibleVoters'));
    }

    public function createCandidate(Election $election)
    {
        // Get users who are not already candidates for this election
        $availableUsers = User::where('active', true)
            ->whereNotIn('id', $election->candidates()->pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('admin.elections.create-candidate', compact('election', 'availableUsers'));
    }

    public function storeCandidate(Request $request, Election $election)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'statement' => 'nullable|string|max:2000',
            'approved' => 'boolean',
        ]);

        // Check if user is already a candidate
        $existingCandidate = $election->candidates()
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingCandidate) {
            return back()->with('error', 'This user is already a candidate for this election.');
        }

        // Get the user to check hours requirements
        $user = User::findOrFail($request->user_id);
        
        // Check hours requirement (admin can override with a warning)
        if ($election->min_candidate_hours > 0 && !$election->userCanBeCandidate($user)) {
            if (!$request->has('force_create')) {
                return back()
                    ->withInput()
                    ->with('warning', "This user only has {$user->getCurrentFiscalYearHours()} volunteer hours but {$election->min_candidate_hours} hours are required. Check 'Force Create' to add them anyway.");
            }
        }

        Candidate::create([
            'election_id' => $election->id,
            'user_id' => $request->user_id,
            'statement' => $request->statement,
            'approved' => $request->has('approved') ? true : !$election->requires_approval,
        ]);

        return redirect()->route('admin.elections.candidates', $election)
            ->with('success', [
                'message' => "Candidate added successfully",
            ]);
    }

    public function removeCandidate(Election $election, Candidate $candidate)
    {
        // Check if voting has started and there are votes for this candidate
        if ($election->votes()->where('candidate_id', $candidate->id)->exists()) {
            return back()->with('error', 'Cannot remove candidate who has received votes.');
        }

        $candidateName = $candidate->user->name;
        $candidate->delete();

        return back()->with('success', [
                'message' => "Removed {$candidateName} successfully",
            ]);
    }
}