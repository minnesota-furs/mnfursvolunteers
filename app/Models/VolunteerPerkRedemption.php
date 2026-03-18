<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerPerkRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'volunteer_perk_id',
        'redeemed_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function perk(): BelongsTo
    {
        return $this->belongsTo(VolunteerPerk::class, 'volunteer_perk_id');
    }
}
