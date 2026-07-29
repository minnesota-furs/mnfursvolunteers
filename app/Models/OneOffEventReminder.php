<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OneOffEventReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'one_off_event_id',
        'remind_morning_of_email',
        'remind_morning_of_telegram',
        'remind_hour_before_email',
        'remind_hour_before_telegram',
        'morning_reminder_sent_at',
        'hour_before_reminder_sent_at',
    ];

    protected $casts = [
        'remind_morning_of_email' => 'boolean',
        'remind_morning_of_telegram' => 'boolean',
        'remind_hour_before_email' => 'boolean',
        'remind_hour_before_telegram' => 'boolean',
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
