<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneOffEventRsvp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'one_off_event_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(OneOffEvent::class, 'one_off_event_id');
    }
}
