<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'start_time',
        'end_time',
        'double_hours',
        'max_volunteers',
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'shift_signups')
                ->withPivot(['hours_logged_at'])
                ->withTimestamps();
                
        return $this->belongsToMany(User::class, 'shift_signups')->withTimestamps();
    }

    public function durationInHours(): float
    {
        return $this->end_time->floatDiffInHours($this->start_time);
    }
}
