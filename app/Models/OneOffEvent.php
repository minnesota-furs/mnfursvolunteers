<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
use Parsedown;

class OneOffEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'url',
        'type',
        'max_rsvps',
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
        'max_rsvps' => 'integer',
    ];

    /**
     * Convert the Markdown description to HTML.
     */
    public function getHtmlDescriptionAttribute()
    {
        return $this->description ? Parsedown::instance()->text($this->description) : null;
    }

    /**
     * Convert the Markdown description to plain text (strips all HTML), for previews.
     */
    public function getPlainTextDescriptionAttribute()
    {
        return $this->description ? strip_tags(Parsedown::instance()->text($this->description)) : null;
    }

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

    public function rsvps()
    {
        return $this->hasMany(OneOffEventRsvp::class);
    }

    public function isCheckInType(): bool
    {
        return $this->type === 'check_in';
    }

    public function isRsvpType(): bool
    {
        return $this->type === 'rsvp';
    }

    /**
     * Events with no end_time are open-ended and never considered "ended".
     */
    public function hasEnded(): bool
    {
        return $this->end_time !== null && $this->end_time->isPast();
    }

    /**
     * Started but not yet ended. Open-ended events count as happening now
     * once started, since they never "end" on their own.
     */
    public function isHappeningNow(): bool
    {
        return $this->start_time->isPast() && !$this->hasEnded();
    }

    /**
     * Spots left before max_rsvps is reached, or null if uncapped.
     */
    public function rsvpSpotsRemaining(): ?int
    {
        if ($this->max_rsvps === null) {
            return null;
        }

        return max(0, $this->max_rsvps - $this->rsvps()->count());
    }

    public function isRsvpFull(): bool
    {
        return $this->max_rsvps !== null && $this->rsvps()->count() >= $this->max_rsvps;
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
