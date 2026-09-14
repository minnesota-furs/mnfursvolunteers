<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteerPerk extends Model
{
    use HasFactory;

    protected $fillable = [
        'perk_set_id',
        'name',
        'description',
        'min_hours',
        'is_active',
        'is_mystery',
        'sort_order',
        'has_pass',
        'pass_label',
        'has_physical_reward',
        'reward_label',
    ];

    protected $casts = [
        'min_hours' => 'decimal:2',
        'is_active' => 'boolean',
        'is_mystery' => 'boolean',
        'sort_order' => 'integer',
        'has_pass' => 'boolean',
        'has_physical_reward' => 'boolean',
    ];

    public function perkSet(): BelongsTo
    {
        return $this->belongsTo(VolunteerPerkSet::class, 'perk_set_id');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'volunteer_perk_event')->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(VolunteerPerkRedemption::class, 'volunteer_perk_id');
    }

    /**
     * Calculate the total volunteer hours a user has toward this perk.
     *
     * - If specific events are linked: sum committed (signed-up, non-no-show) shift durations.
     * - Otherwise: sum VolunteerHours records, filtered by the perk set's fiscal year if set.
     */
    public function getUserProgress(User $user): float
    {
        $breakdown = $this->getUserProgressBreakdown($user);

        return $breakdown['completed'] + $breakdown['upcoming'];
    }

    /**
     * Break down a user's progress toward this perk into completed vs. upcoming hours.
     *
     * Returns ['completed' => float, 'upcoming' => float]
     * - completed: hours from shifts whose end_time is in the past, plus any logged
     *   VolunteerHours (see below)
     * - upcoming:  hours from shifts that haven't ended yet (0 for VolunteerHours-based perks)
     */
    public function getUserProgressBreakdown(User $user): array
    {
        $this->loadMissing(['events', 'perkSet']);

        $fiscalLedgerId = $this->perkSet?->fiscal_ledger_id;

        // Manually-logged hours a staff member explicitly attributed to this perk's
        // set. These count toward this perk whether or not it has linked events,
        // so event-linked perks can still be credited for hours logged outside of
        // shift sign-ups.
        $attributedHours = 0.0;
        if ($this->perk_set_id) {
            $attributedHours = (float) VolunteerHours::where('user_id', $user->id)
                ->where('counts_toward_perks', true)
                ->where('perk_set_id', $this->perk_set_id)
                ->when($fiscalLedgerId, fn ($q) => $q->where('fiscal_ledger_id', $fiscalLedgerId))
                ->sum('hours');
        }

        if ($this->events->isNotEmpty()) {
            $eventIds = $this->events->pluck('id');

            $shifts = Shift::with('event')
                ->whereHas('event', fn ($q) => $q->whereIn('id', $eventIds))
                ->whereHas('users', fn ($q) => $q
                    ->where('users.id', $user->id)
                    ->where(fn ($q2) => $q2
                        ->whereNull('shift_signups.no_show')
                        ->orWhere('shift_signups.no_show', false)
                    )
                )
                ->get();

            $now = now();

            $completed = (float) $shifts
                ->filter(fn ($s) => $s->end_time->lte($now))
                ->sum(fn ($s) => $s->durationInHours() * ($s->double_hours ? 2 : 1));

            $upcoming = (float) $shifts
                ->filter(fn ($s) => $s->end_time->gt($now))
                ->sum(fn ($s) => $s->durationInHours() * ($s->double_hours ? 2 : 1));

            return ['completed' => $completed + $attributedHours, 'upcoming' => $upcoming];
        }

        // Hours not explicitly attributed to any perk set count toward every
        // general (non-event-linked) perk, matching the pre-existing behavior.
        $unattributedHours = (float) VolunteerHours::where('user_id', $user->id)
            ->where('counts_toward_perks', true)
            ->whereNull('perk_set_id')
            ->when($fiscalLedgerId, fn ($q) => $q->where('fiscal_ledger_id', $fiscalLedgerId))
            ->sum('hours');

        return ['completed' => $unattributedHours + $attributedHours, 'upcoming' => 0.0];
    }

    /**
     * Returns progress as a percentage (0–100), capped at 100.
     */
    public function getUserProgressPercentage(User $user): float
    {
        if ($this->min_hours <= 0) {
            return 100.0;
        }

        return min(100.0, ($this->getUserProgress($user) / $this->min_hours) * 100);
    }

    /**
     * Whether the user has met the hour threshold for this perk.
     */
    public function hasEarned(User $user): bool
    {
        return $this->getUserProgress($user) >= $this->min_hours;
    }

    /**
     * Scope: only active perks, ordered by sort_order then min_hours.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('min_hours');
    }
}
