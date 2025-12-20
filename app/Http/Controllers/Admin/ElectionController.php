<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\User;
use App\Models\FiscalLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Parsedown;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::with(['candidates', 'votes'])
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

        return view('admin.elections.index', compact('elections'));
    }

    public function create()
    {
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();
        return view('admin.elections.create', compact('fiscalLedgers'));
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
            'fiscal_ledger_id' => 'nullable|exists:fiscal_ledgers,id',
        ]);

        $election = Election::create($validated);

        return redirect()->route('admin.elections.show', $election)
            ->with('success', [
                'message' => 'Election created successfully.',
            ]);
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

        // Count unique voters instead of total votes for turnout
        $uniqueVoters = $election->votes()->distinct('user_id')->count('user_id');
        
        // Also get total votes for display
        $totalVotes = $election->votes()->count();

        // Calculate eligible voters based on fiscal period and hours requirements
        $eligibleVoters = 0;
        if ($election->min_voter_hours > 0) {
            $allUsers = User::all();
            foreach ($allUsers as $user) {
                if ($election->userCanVote($user)) {
                    $eligibleVoters++;
                }
            }
        } else {
            // If no hours requirement, all users are eligible
            $eligibleVoters = User::count();
        }

        // Convert markdown to HTML using Parsedown
        $parsedown = new Parsedown();
        $election->parsedDescription = $parsedown->text($election->description);

        return view('admin.elections.show', compact('election', 'candidates', 'uniqueVoters', 'totalVotes', 'eligibleVoters'));
    }

    public function edit(Election $election)
    {
        $fiscalLedgers = FiscalLedger::orderBy('start_date', 'desc')->get();
        return view('admin.elections.edit', compact('election', 'fiscalLedgers'));
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
            'fiscal_ledger_id' => 'nullable|exists:fiscal_ledgers,id',
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

        // Count unique voters
        $uniqueVoters = $election->votes()->distinct('user_id')->count('user_id');
        
        // Count total votes for percentage calculations
        $totalVotes = $election->votes()->count();
        
        $eligibleVoters = User::where('active', true)->count();

        return view('admin.elections.results', compact('election', 'candidates', 'uniqueVoters', 'totalVotes', 'eligibleVoters'));
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
                // Get user's hours for the relevant fiscal period
                $userHours = $election->fiscal_ledger_id 
                    ? $user->getHoursForFiscalLedger($election->fiscal_ledger_id)
                    : $user->getCurrentFiscalYearHours();
                
                $fiscalPeriodName = $election->fiscal_ledger_id 
                    ? $election->fiscalLedger->name 
                    : 'current fiscal year';
                
                return back()
                    ->withInput()
                    ->with('warning', "This user only has {$userHours} volunteer hours in {$fiscalPeriodName} but {$election->min_candidate_hours} hours are required. Check 'Force Create' to add them anyway.");
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

    public function voters(Election $election)
    {
        // Get unique voters with their first vote timestamp (when they voted)
        // We don't show who they voted for, just that they voted and when
        $voters = $election->votes()
            ->select('user_id', 'election_id', DB::raw('MIN(created_at) as voted_at'), DB::raw('COUNT(*) as vote_count'))
            ->groupBy('user_id', 'election_id')
            ->with('user:id,name,email,vol_code')
            ->get()
            ->map(function($vote) {
                return [
                    'user' => $vote->user,
                    'voted_at' => $vote->voted_at,
                    'vote_count' => $vote->vote_count
                ];
            })
            ->sortBy('voted_at');

        $totalVoters = $voters->count();
        $totalVotes = $election->votes()->count();

        return view('admin.elections.voters', compact('election', 'voters', 'totalVoters', 'totalVotes'));
    }

    public function editCandidate(Election $election, Candidate $candidate)
    {
        // Ensure the candidate belongs to this election
        if ($candidate->election_id !== $election->id) {
            abort(404);
        }

        return view('admin.elections.edit-candidate', compact('election', 'candidate'));
    }

    public function updateCandidate(Request $request, Election $election, Candidate $candidate)
    {
        // Ensure the candidate belongs to this election
        if ($candidate->election_id !== $election->id) {
            abort(404);
        }

        $validated = $request->validate([
            'statement' => 'nullable|string|max:2000',
            'approved' => 'boolean',
        ]);

        $candidate->update($validated);

        return redirect()->route('admin.elections.candidates', $election)
            ->with('success', [
                'message' => "Candidate updated successfully",
            ]);
    }

    public function exportVoterTurnout(Election $election)
    {
        // Load fiscal ledger if configured
        $election->load('fiscalLedger');
        
        // Get unique voter IDs from votes
        $voterIds = Vote::where('election_id', $election->id)
            ->distinct('user_id')
            ->pluck('user_id');

        // Get unique voters with their basic information
        $voters = User::whereIn('id', $voterIds)
            ->with('department.sector')
            ->orderBy('name')
            ->get();

        // Determine fiscal period name for CSV header
        $fiscalPeriodName = $election->fiscal_ledger_id && $election->fiscalLedger
            ? $election->fiscalLedger->fiscal_year_name
            : 'Current Fiscal Year';

        // Create CSV content with volunteer hours column
        $csv = "Name,Department,Sector,Volunteer Code,Volunteer Hours ($fiscalPeriodName),Vote Count\n";
        
        foreach ($voters as $voter) {
            $voteCount = Vote::where('election_id', $election->id)
                ->where('user_id', $voter->id)
                ->count();
            
            // Get volunteer hours for the relevant fiscal period
            $volunteerHours = $election->fiscal_ledger_id
                ? $voter->totalVolunteerHoursForFiscalPeriod($election->fiscal_ledger_id)
                : $voter->totalHoursForCurrentFiscalLedger();
            
            $csv .= sprintf(
                '"%s","%s","%s","%s",%.2f,%d' . "\n",
                $voter->name,
                $voter->department ? $voter->department->name : 'N/A',
                $voter->department && $voter->department->sector ? $voter->department->sector->name : 'N/A',
                $voter->vol_code ?? 'N/A',
                $volunteerHours,
                $voteCount
            );
        }

        $filename = 'voter-turnout-' . Str::slug($election->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportResultsImage(Election $election)
    {
        if (!$election->resultsAreVisible()) {
            return redirect()->route('admin.elections.show', $election)
                ->with('error', 'Results will be available after the voting period ends.');
        }

        $candidates = $election->candidates()
            ->with('user')
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();

        // Create image
        $width = 800;
        $height = 500;
        $image = imagecreatetruecolor($width, $height);

        // Define colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $green = imagecolorallocate($image, 34, 197, 94); // green-500
        $gray = imagecolorallocate($image, 156, 163, 175); // gray-400
        $lightGray = imagecolorallocate($image, 243, 244, 246); // gray-100
        $darkText = imagecolorallocate($image, 31, 41, 55); // gray-800

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        // Add title using GD built-in fonts (more reliable in Docker)
        $title = $election->title . ' - Results';
        imagestring($image, 5, 40, 30, $title, $darkText);

        // Draw separator line
        imagefilledrectangle($image, 40, 70, $width - 40, 72, $lightGray);

        // Display results
        $y = 110;
        $maxDisplay = min(5, $candidates->count()); // Show top 5 candidates
        
        foreach ($candidates->take($maxDisplay) as $index => $candidate) {
            $isElected = $index < $election->max_positions;
            $barColor = $isElected ? $green : $gray;
            
            // Calculate bar width based on votes
            $totalVotes = $election->votes()->count();
            $percentage = $totalVotes > 0 ? ($candidate->votes_count / $totalVotes) * 100 : 0;
            $barWidth = (int)(($width - 160) * ($percentage / 100));
            
            // Draw position number
            $positionText = '#' . ($index + 1);
            imagestring($image, 5, 40, $y - 10, $positionText, $darkText);
            
            // Draw candidate name
            $nameText = $candidate->user->name;
            if (strlen($nameText) > 30) {
                $nameText = substr($nameText, 0, 27) . '...';
            }
            imagestring($image, 4, 80, $y - 8, $nameText, $darkText);
            
            // Draw vote bar
            imagefilledrectangle($image, 280, $y - 15, 280 + $barWidth, $y + 10, $barColor);
            
            // Draw vote count
            $voteText = $candidate->votes_count . ' votes (' . number_format($percentage, 1) . '%)';
            imagestring($image, 3, 290 + $barWidth, $y - 8, $voteText, $darkText);
            
            $y += 70;
        }

        // Add footer with date
        $footerText = 'Results as of ' . now()->format('M j, Y g:i A');
        imagestring($image, 3, 40, $height - 30, $footerText, $gray);

        // Output image
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $filename = 'election-results-' . Str::slug($election->title) . '-' . now()->format('Y-m-d') . '.png';

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
