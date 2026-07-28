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
        'location',
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

    public function requiredTags()
    {
        return $this->belongsToMany(Tag::class, 'one_off_event_tag')->withTimestamps();
    }

    /**
     * Required tags scoped to user-eligible types (type = 'user' or null).
     * Mirrors Event::requiredUserTags() so eligibility checks don't get
     * gated by shift-only tags, which don't apply here.
     */
    public function requiredUserTags()
    {
        return $this->belongsToMany(Tag::class, 'one_off_event_tag')->withTimestamps()->forUsers();
    }

    public function requiredDepartments()
    {
        return $this->belongsToMany(Department::class, 'department_one_off_event')->withTimestamps();
    }

    /**
     * Sectors whose departments are eligible to check in. Anyone in any
     * department under a selected sector qualifies, so this stays in sync
     * automatically as departments are added to/removed from the sector.
     */
    public function requiredSectors()
    {
        return $this->belongsToMany(Sector::class, 'sector_one_off_event')->withTimestamps();
    }
}
