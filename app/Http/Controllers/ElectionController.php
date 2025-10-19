<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Parsedown;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::where('active', true)
            ->where(function($query) {
                // Show if voting is active
                $query->where(function($q) {
                    $q->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
                })
                // OR if nominations are active
                ->orWhere(function($q) {
                    $q->where('nomination_start_date', '<=', now())
                      ->where('nomination_end_date', '>=', now());
                });
            })
            ->with(['candidates' => function($query) {
                $query->where('approved', true)->where('withdrawn', false);
            }])
            ->orderBy('start_date', 'asc')
            ->get();

        // Convert markdown to HTML using Parsedown for all elections
        // For index, only show content until first line break
        $parsedown = new Parsedown();
        foreach ($elections as $election) {
            $firstParagraph = explode("\n\n", $election->description)[0];
            $firstLine = explode("\n", $firstParagraph)[0];
            $election->parsedDescription = $parsedown->text($firstLine);
        }

        return view('elections.index', compact('elections'));
    }

    public function show(Election $election)
    {
        // Allow viewing during nomination period OR voting period
        if (!$election->isVotingPeriod() && !$election->isNominationPeriod()) {
            return redirect()->route('elections.index')
                ->with('error', 'This election is not currently active.');
        }

        $candidates = $election->candidates()
            ->where('approved', true)
            ->where('withdrawn', false)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        $userHasVoted = $election->userHasVoted(Auth::user());
        $userVoteCount = $election->userVoteCount(Auth::user());
        $remainingVotes = $election->userRemainingVotes(Auth::user());
        $userVotedCandidates = $election->votes()
            ->where('user_id', Auth::id())
            ->pluck('candidate_id')
            ->toArray();

        // Convert markdown to HTML using Parsedown
        $parsedown = new Parsedown();
        $election->parsedDescription = $parsedown->text($election->description);

        return view('elections.show', compact('election', 'candidates', 'userHasVoted', 'userVoteCount', 'remainingVotes', 'userVotedCandidates'));
    }

    public function vote(Request $request, Election $election)
    {
        if (!$election->isVotingPeriod()) {
            return back()->with('error', 'This election is not currently accepting votes.');
        }

        if ($election->userHasVoted(Auth::user())) {
            return back()->with('error', 'You have already used all your votes in this election.');
        }

        if (!$election->userCanVote(Auth::user())) {
            // Get user's hours for the relevant fiscal period
            $userHours = $election->fiscal_ledger_id 
                ? Auth::user()->getHoursForFiscalLedger($election->fiscal_ledger_id)
                : Auth::user()->getCurrentFiscalYearHours();
            
            $fiscalPeriodName = $election->fiscal_ledger_id 
                ? $election->fiscalLedger->name 
                : 'the current fiscal year';
            
            return back()->with('error', "You need at least {$election->min_voter_hours} volunteer hours in {$fiscalPeriodName} to vote in this election.");
        }

        // Handle both single candidate (radio) and multiple candidates (checkbox)
        if ($election->max_positions == 1) {
            $request->validate([
                'candidate_id' => 'required|exists:candidates,id',
            ]);
            $candidateIds = [$request->candidate_id];
        } else {
            $request->validate([
                'candidate_ids' => 'required|array|min:1|max:' . $election->userRemainingVotes(Auth::user()),
                'candidate_ids.*' => 'exists:candidates,id',
            ]);
            $candidateIds = $request->candidate_ids;
        }

        // Check for duplicate votes
        foreach ($candidateIds as $candidateId) {
            if ($election->votes()->where('user_id', Auth::id())->where('candidate_id', $candidateId)->exists()) {
                return back()->with('error', 'You have already voted for one or more of these candidates.');
            }
        }

        // Verify all candidates belong to this election and are approved
        $candidates = Candidate::whereIn('id', $candidateIds)
            ->where('election_id', $election->id)
            ->where('approved', true)
            ->where('withdrawn', false)
            ->get();

        if ($candidates->count() !== count($candidateIds)) {
            return back()->with('error', 'One or more selected candidates are invalid.');
        }

        // Create votes for each selected candidate
        foreach ($candidateIds as $candidateId) {
            Vote::create([
                'election_id' => $election->id,
                'user_id' => Auth::id(),
                'candidate_id' => $candidateId,
            ]);
        }

        $voteCount = count($candidateIds);
        $message = $voteCount == 1 
            ? 'Your vote has been recorded successfully.'
            : "Your {$voteCount} votes have been recorded successfully.";

        return redirect()->route('elections.show', $election)
            ->with('success', [
                    'message' => $message,
                ]);
    }

    public function nominate(Election $election)
    {
        if (!$election->allow_self_nomination) {
            return redirect()->route('elections.show', $election)
                ->with('error', 'Self-nomination is not allowed for this election.');
        }

        if (!$election->isNominationPeriod()) {
            $status = $election->getNominationStatus();
            $message = match($status) {
                'upcoming' => 'Nominations have not opened yet. They will open on ' . $election->nomination_start_date->format('M j, Y g:i A'),
                'closed' => 'The nomination period has ended.',
                'no_period_set' => 'This election is not currently accepting nominations.',
                default => 'Nominations are not currently open.'
            };
            
            return redirect()->route('elections.show', $election)
                ->with('error', $message);
        }

        if (!$election->userCanBeCandidate(Auth::user())) {
            $fiscalPeriodName = $election->fiscal_ledger_id 
                ? $election->fiscalLedger->name 
                : 'the current fiscal year';
            
            return redirect()->route('elections.show', $election)
                ->with('error', "You need at least {$election->min_candidate_hours} volunteer hours in {$fiscalPeriodName} to be a candidate in this election.");
        }

        // Check if user is already a candidate
        $existingCandidate = $election->candidates()
            ->where('user_id', Auth::id())
            ->first();

        if ($existingCandidate) {
            return redirect()->route('elections.show', $election)
                ->with('error', 'You are already nominated for this election.');
        }

        return view('elections.nominate', compact('election'));
    }

    public function storeNomination(Request $request, Election $election)
    {
        if (!$election->allow_self_nomination) {
            return redirect()->route('elections.show', $election)
                ->with('error', 'Self-nomination is not allowed for this election.');
        }

        if (!$election->isNominationPeriod()) {
            return redirect()->route('elections.show', $election)
                ->with('error', 'The nomination period is not currently open.');
        }

        if (!$election->userCanBeCandidate(Auth::user())) {
            $fiscalPeriodName = $election->fiscal_ledger_id 
                ? $election->fiscalLedger->name 
                : 'the current fiscal year';
            
            return redirect()->route('elections.show', $election)
                ->with('error', "You need at least {$election->min_candidate_hours} volunteer hours in {$fiscalPeriodName} to be a candidate in this election.");
        }

        // Check if user is already a candidate
        $existingCandidate = $election->candidates()
            ->where('user_id', Auth::id())
            ->first();

        if ($existingCandidate) {
            return redirect()->route('elections.show', $election)
                ->with('error', 'You are already nominated for this election.');
        }

        Candidate::create([
            'election_id' => $election->id,
            'user_id' => Auth::id(),
            'statement' => null, // Statement will be set by admin
            'approved' => !$election->requires_approval,
        ]);

        $message = $election->requires_approval 
            ? 'Your nomination has been submitted and is pending approval.'
            : 'Your nomination has been submitted successfully.';

        return redirect()->route('elections.show', $election)
            ->with('success', [
                'message' => $message,
            ]);
    }

    public function results(Election $election)
    {
        if (!$election->resultsAreVisible()) {
            return redirect()->route('elections.show', $election)
                ->with('error', 'Results will be available after the voting period ends on ' . $election->end_date->format('M j, Y g:i A'));
        }

        $candidates = $election->candidates()
            ->where('approved', true)
            ->where('withdrawn', false)
            ->with('user')
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();

        $totalVotes = $election->votes()->count();

                // Convert markdown to HTML using Parsedown
        $parsedown = new Parsedown();
        $election->parsedDescription = $parsedown->text($election->description);

        return view('elections.results', compact('election', 'candidates', 'totalVotes'));
    }

    /**
     * Display public elections listing for guests
     */
    public function guestIndex()
    {
        $elections = Election::where('active', true)
            ->where(function($query) {
                // Show if voting is active
                $query->where(function($q) {
                    $q->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
                })
                // OR if nominations are active
                ->orWhere(function($q) {
                    $q->where('nomination_start_date', '<=', now())
                      ->where('nomination_end_date', '>=', now());
                })
                // OR if results are visible (completed elections)
                ->orWhere(function($q) {
                    $q->where('end_date', '<', now());
                });
            })
            ->with(['candidates' => function($query) {
                $query->where('approved', true)->where('withdrawn', false);
            }])
            ->orderBy('start_date', 'desc')
            ->get();

        // Convert markdown to HTML using Parsedown for all elections
        // For index, only show content until first line break
        $parsedown = new Parsedown();
        foreach ($elections as $election) {
            $firstParagraph = explode("\n\n", $election->description)[0];
            $firstLine = explode("\n", $firstParagraph)[0];
            $election->parsedDescription = $parsedown->text($firstLine);
        }

        return view('elections-guest.index', compact('elections'));
    }

    /**
     * Display specific election details for guests
     */
    public function guestShow(Election $election)
    {
        // Only show active elections or completed ones
        if (!$election->active && !$election->isCompleted()) {
            abort(404);
        }

        // Only show if there's something to see (voting period, nomination period, or completed)
        if (!$election->isVotingPeriod() && !$election->isNominationPeriod() && !$election->isCompleted()) {
            abort(404);
        }

        // Get approved candidates
        $candidates = $election->candidates()
            ->where('approved', true)
            ->where('withdrawn', false)
            ->with('user.department.sector')
            ->get();

        // Convert markdown to HTML using Parsedown
        $parsedown = new Parsedown();
        $election->parsedDescription = $parsedown->text($election->description);
        
        // Parse candidate statements
        foreach ($candidates as $candidate) {
            if ($candidate->statement) {
                $candidate->parsedStatement = $parsedown->text($candidate->statement);
            }
        }

        return view('elections-guest.show', compact('election', 'candidates'));
    }
}