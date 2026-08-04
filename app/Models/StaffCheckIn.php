<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_check_in_session_id',
        'user_id',
        'completed_items',
        'signature_data',
        'checked_in_by',
        'checked_in_at',
    ];

    protected $casts = [
        'completed_items' => 'array',
        'checked_in_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(StaffCheckInSession::class, 'staff_check_in_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
