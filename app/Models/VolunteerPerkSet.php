<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteerPerkSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'fiscal_ledger_id',
        'visible_from',
        'visible_until',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'visible_from'  => 'date',
        'visible_until' => 'date',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function fiscalLedger(): BelongsTo
    {
        return $this->belongsTo(FiscalLedger::class);
    }

    public function perks(): HasMany
    {
        return $this->hasMany(VolunteerPerk::class, 'perk_set_id');
    }

    /**
     * Scope: active sets currently visible to volunteers (within visibility window).
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('visible_from')->orWhereDate('visible_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('visible_until')->orWhereDate('visible_until', '>=', $today));
    }

    /**
     * Scope: sets that have passed their visible_until date (archived/historical).
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('visible_until')
            ->whereDate('visible_until', '<', Carbon::today());
    }
}
