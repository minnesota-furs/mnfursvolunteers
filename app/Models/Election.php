<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'nomination_start_date',
        'nomination_end_date',
        'start_date',
        'end_date',
        'active',
        'max_positions',
        'allow_self_nomination',
        'requires_approval',
        'min_candidate_hours',
        'min_voter_hours',
    ];

    protected $casts = [
        'nomination_start_date' => 'datetime',
        'nomination_end_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
        'allow_self_nomination' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function isActive()
    {
        return $this->active && 
               now()->between($this->start_date, $this->end_date);
    }

    public function isVotingPeriod()
    {
        return now()->between($this->start_date, $this->end_date);
    }

    public function isNominationPeriod()
    {
        // If no nomination dates are set, fall back to the election active period
        if (!$this->nomination_start_date || !$this->nomination_end_date) {
            return $this->isActive();
        }
        
        return $this->active && now()->between($this->nomination_start_date, $this->nomination_end_date);
    }

    public function hasNominationPeriod()
    {
        return $this->nomination_start_date && $this->nomination_end_date;
    }

    public function getNominationStatus()
    {
        if (!$this->hasNominationPeriod()) {
            return 'no_period_set';
        }

        $now = now();
        
        if ($now < $this->nomination_start_date) {
            return 'upcoming';
        } elseif ($now->between($this->nomination_start_date, $this->nomination_end_date)) {
            return 'open';
        } else {
            return 'closed';
        }
    }

    public function resultsAreVisible()
    {
        // Results are only visible after voting period ends
        return now() > $this->end_date;
    }

    public function isCompleted()
    {
        return now() > $this->end_date;
    }

    public function userHasVoted(User $user)
    {
        $voteCount = $this->votes()->where('user_id', $user->id)->count();
        return $voteCount >= $this->max_positions;
    }

    public function userVoteCount(User $user)
    {
        return $this->votes()->where('user_id', $user->id)->count();
    }



    public function userRemainingVotes(User $user)
    {
        return max(0, $this->max_positions - $this->userVoteCount($user));
    }

    public function userCanVote(User $user)
    {
        if ($this->min_voter_hours <= 0) {
            return true;
        }

        return $user->getCurrentFiscalYearHours() >= $this->min_voter_hours;
    }

    public function userCanBeCandidate(User $user)
    {
        if ($this->min_candidate_hours <= 0) {
            return true;
        }

        return $user->getCurrentFiscalYearHours() >= $this->min_candidate_hours;
    }

    public function hasHoursRequirements()
    {
        return $this->min_voter_hours > 0 || $this->min_candidate_hours > 0;
    }
}
