<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class OneOffEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'auto_credit_hours',
        'checkin_hours_before',
        'checkin_hours_after',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'auto_credit_hours' => 'boolean',
        'checkin_hours_before' => 'integer',
        'checkin_hours_after' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            $event->slug = Str::slug($event->name);
        });

        static::updating(function ($event) {
            if ($event->isDirty('name')) {
                $event->slug = Str::slug($event->name);
            }
        });
    }

    public function checkIns()
    {
        return $this->hasMany(OneOffEventCheckIn::class);
    }
}
