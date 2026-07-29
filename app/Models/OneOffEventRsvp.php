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
        'remind_morning_of',
        'remind_hour_before',
        'morning_reminder_sent_at',
        'hour_before_reminder_sent_at',
    ];

    protected $casts = [
        'remind_morning_of' => 'boolean',
        'remind_hour_before' => 'boolean',
        'morning_reminder_sent_at' => 'datetime',
        'hour_before_reminder_sent_at' => 'datetime',
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
