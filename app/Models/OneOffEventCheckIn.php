<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneOffEventCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'one_off_event_id',
        'checked_in_at',
    ];

    // setup casts
    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
