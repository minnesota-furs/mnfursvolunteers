<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VolunteerPerk extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_hours',
        'fiscal_ledger_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_hours'   => 'decimal:2',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function fiscalLedger(): BelongsTo
    {
        return $this->belongsTo(FiscalLedger::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'volunteer_perk_event')->withTimestamps();
    }

    /**
     * Calculate the total volunteer hours a user has earned toward this perk.
     *
     * - If specific events are linked: sum completed shift durations for those events.
     * - Otherwise: sum VolunteerHours records, optionally filtered by fiscal year.
     */
    public function getUserProgress(User $user): float
    {
        $this->loadMissing('events');

        if ($this->events->isNotEmpty()) {
            $eventIds = $this->events->pluck('id');

            $shifts = Shift::with('event')
                ->whereHas('event', fn ($q) => $q->whereIn('id', $eventIds))
                ->whereHas('users', fn ($q) => $q
                    ->where('users.id', $user->id)
                    ->whereNotNull('shift_signups.hours_logged_at')
                    ->where(fn ($q2) => $q2
                        ->whereNull('shift_signups.no_show')
                        ->orWhere('shift_signups.no_show', false)
                    )
                )
                ->get();

            return (float) $shifts->sum(fn ($shift) =>
                $shift->durationInHours() * ($shift->double_hours ? 2 : 1)
            );
        }

        $query = VolunteerHours::where('user_id', $user->id);

        if ($this->fiscal_ledger_id) {
            $query->where('fiscal_ledger_id', $this->fiscal_ledger_id);
        }

        return (float) $query->sum('hours');
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
