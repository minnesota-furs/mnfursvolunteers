<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    use HasFactory;

    /**
     * @var array<int, float>
     */
    protected array $userHoursCache = [];

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
        'fiscal_ledger_id',
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

    public function milestones(): HasMany
    {
        return $this->hasMany(ElectionMilestone::class)->orderBy('starts_at');
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function fiscalLedger()
    {
        return $this->belongsTo(FiscalLedger::class)->withTrashed();
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
        if (! $this->nomination_start_date || ! $this->nomination_end_date) {
            return $this->isActive();
        }

        return $this->active && now()->between($this->nomination_start_date, $this->nomination_end_date);
    }

    /**
     * Between the close of nominations and the start of voting.
     */
    public function isReviewPeriod(): bool
    {
        return $this->hasNominationPeriod()
            && now() > $this->nomination_end_date
            && now() < $this->start_date;
    }

    /**
     * Elections that have started and not yet ended: nominations, the review
     * gap between nominations and voting, or voting.
     */
    public function scopeCurrentlyRunning(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(fn (Builder $voting) => $voting->where('start_date', '<=', now())->where('end_date', '>=', now()))
                ->orWhere(fn (Builder $nominations) => $nominations->where('nomination_start_date', '<=', now())->where('nomination_end_date', '>=', now()))
                ->orWhere(fn (Builder $review) => $review->where('nomination_end_date', '<', now())->where('start_date', '>', now()));
        });
    }

    public function hasNominationPeriod()
    {
        return $this->nomination_start_date && $this->nomination_end_date;
    }

    public function getNominationStatus()
    {
        if (! $this->hasNominationPeriod()) {
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

    /**
     * The stage the election is currently in, for display.
     *
     * @return array{label: string, color: string, detail: ?string}
     */
    public function currentStage(): array
    {
        $countdown = fn ($date): string => $date->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE]);
        $nominationStatus = $this->getNominationStatus();

        return match (true) {
            $this->isVotingPeriod() => ['label' => 'Voting Open', 'color' => 'text-blue-600', 'detail' => 'Closes in '.$countdown($this->end_date)],
            $nominationStatus === 'open' => ['label' => 'Nominations Open', 'color' => 'text-green-600', 'detail' => 'Closes in '.$countdown($this->nomination_end_date)],
            $nominationStatus === 'upcoming' => ['label' => 'Nominations Soon', 'color' => 'text-yellow-600', 'detail' => 'Opens in '.$countdown($this->nomination_start_date)],
            $nominationStatus === 'closed' && now() < $this->start_date => ['label' => 'Nominations Closed', 'color' => 'text-orange-600', 'detail' => 'Voting opens in '.$countdown($this->start_date)],
            now() < $this->start_date => ['label' => 'Upcoming', 'color' => 'text-yellow-600', 'detail' => 'Voting opens in '.$countdown($this->start_date)],
            default => ['label' => 'Ended', 'color' => 'text-gray-600', 'detail' => null],
        };
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
        return $this->min_voter_hours <= 0
            || $this->hoursForUser($user) >= $this->min_voter_hours;
    }

    /**
     * The user's hours in the period this election counts: the attached
     * fiscal ledger, or the current fiscal year. Looked up once per user
     * so repeated eligibility checks while rendering a page stay cheap.
     */
    public function hoursForUser(User $user): float
    {
        return $this->userHoursCache[$user->id] ??= (float) ($this->fiscal_ledger_id
            ? $user->getHoursForFiscalLedger($this->fiscal_ledger_id)
            : $user->getCurrentFiscalYearHours());
    }

    /**
     * How many more hours the user needs to meet the voter requirement.
     */
    public function userVoterHoursShortfall(User $user): float
    {
        return $this->hoursShortfall($user, $this->min_voter_hours);
    }

    /**
     * How many more hours the user needs to meet the candidate requirement.
     */
    public function userCandidateHoursShortfall(User $user): float
    {
        return $this->hoursShortfall($user, $this->min_candidate_hours);
    }

    protected function hoursShortfall(User $user, int|float|null $requiredHours): float
    {
        if ($requiredHours <= 0) {
            return 0;
        }

        return max(0, $requiredHours - $this->hoursForUser($user));
    }

    /**
     * Whether the user is currently nominated (approved or pending, not withdrawn).
     */
    public function userIsNominated(User $user): bool
    {
        return $this->candidates()
            ->where('user_id', $user->id)
            ->where('withdrawn', false)
            ->exists();
    }

    /**
     * Whether people can still self-nominate (nominations not yet closed).
     */
    public function selfNominationStillPossible(): bool
    {
        return $this->allow_self_nomination && $this->getNominationStatus() !== 'closed';
    }

    /**
     * The fiscal ledger hours are counted against: the attached one, or the current fiscal year.
     */
    public function reportingFiscalLedger(): ?FiscalLedger
    {
        if ($this->fiscal_ledger_id) {
            return $this->fiscalLedger;
        }

        return FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Whether hours can still be reported against the ledger hours count towards.
     */
    public function reportingFiscalLedgerIsOpen(): bool
    {
        $ledger = $this->reportingFiscalLedger();

        return $ledger !== null && $ledger->end_date->copy()->endOfDay()->isFuture();
    }

    public function userCanBeCandidate(User $user)
    {
        return $this->min_candidate_hours <= 0
            || $this->hoursForUser($user) >= $this->min_candidate_hours;
    }

    /**
     * Segments of the election (nominations, review, voting, results) with the
     * data needed to draw a proportional progress bar and a "now" marker.
     *
     * Each segment has: label, start, end, color, isMilestone, weight (relative
     * width) and fill (0-100, how much has elapsed). nowFraction is the marker's
     * position as 0-1 of the total weight, nowSegmentIndex the segment holding it.
     *
     * @return array{segments: array<int, array<string, mixed>>, nowFraction: ?float, nowSegmentIndex: int, currentLabel: ?string}
     */
    public function timeline(): array
    {
        $now = now();
        $segments = [];

        if ($this->hasNominationPeriod()) {
            $nominationEnd = $this->nomination_end_date->min($this->start_date);
            $segments[] = ['label' => 'Nominations', 'start' => $this->nomination_start_date, 'end' => $nominationEnd, 'color' => 'bg-green-500', 'isMilestone' => false];

            if ($nominationEnd < $this->start_date) {
                $segments[] = ['label' => 'Review', 'start' => $nominationEnd, 'end' => $this->start_date, 'color' => 'bg-gray-400', 'isMilestone' => false];
            }
        }

        $segments[] = ['label' => 'Voting', 'start' => $this->start_date, 'end' => $this->end_date, 'color' => 'bg-blue-500', 'isMilestone' => false];
        $segments[] = ['label' => 'Election Results', 'start' => $this->end_date, 'end' => $this->end_date, 'color' => 'bg-slate-500', 'isMilestone' => true];

        $total = max(1, $this->end_date->timestamp - $segments[0]['start']->timestamp);

        foreach ($segments as $index => $segment) {
            $duration = max(1, $segment['end']->timestamp - $segment['start']->timestamp);

            $segments[$index]['weight'] = $segment['isMilestone']
                ? $total * 0.08
                : max($duration, $total * 0.15);
            $segments[$index]['fill'] = $segment['isMilestone']
                ? ($now > $segment['start'] ? 100 : 0)
                : round(min(1, max(0, ($now->timestamp - $segment['start']->timestamp) / $duration)) * 100, 2);
        }

        $weightTotal = array_sum(array_column($segments, 'weight'));
        $nowFraction = null;
        $nowSegmentIndex = 0;
        $currentLabel = null;
        $weightBefore = 0;

        foreach ($segments as $index => $segment) {
            $duration = max(1, $segment['end']->timestamp - $segment['start']->timestamp);
            $isCurrent = $segment['isMilestone']
                ? $now > $segment['start']
                : ($now >= $segment['start'] && $now <= $segment['end']);

            if ($isCurrent) {
                $fraction = $segment['isMilestone'] ? 0.5 : ($now->timestamp - $segment['start']->timestamp) / $duration;
                $nowFraction = ($weightBefore + $fraction * $segment['weight']) / $weightTotal;
                $nowSegmentIndex = $index;
                $currentLabel = $segment['label'];
                break;
            }

            $weightBefore += $segment['weight'];
        }

        return [
            'segments' => $segments,
            'nowFraction' => $nowFraction,
            'nowSegmentIndex' => $nowSegmentIndex,
            'currentLabel' => $currentLabel,
        ];
    }

    public function hasHoursRequirements()
    {
        return $this->min_voter_hours > 0 || $this->min_candidate_hours > 0;
    }

    /**
     * Get all users eligible to vote who haven't voted yet
     */
    public function getEligibleNonVoters()
    {
        $allUsers = User::where('active', true)->get();
        $eligibleNonVoters = [];
        $optedOutUsers = [];

        foreach ($allUsers as $user) {
            // Check if user can vote (meets hour requirements) and hasn't cast any votes yet
            if ($this->userCanVote($user) && $this->userVoteCount($user) === 0) {
                if ($user->email_election_reminders) {
                    $eligibleNonVoters[] = $user;
                } else {
                    $optedOutUsers[] = $user;
                }
            }
        }

        return [
            'eligible' => collect($eligibleNonVoters),
            'opted_out' => collect($optedOutUsers),
        ];
    }
}
