<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'auto_credit_hours',
        'location',
        'created_by',
        'visibility',
        'hide_past_shifts',
        'signup_open_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'hide_past_shifts' => 'boolean',
        'signup_open_date' => 'datetime',
    ];
    

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function getRemainingVolunteerSpotsAttribute()
    {
        return $this->shifts->sum(function ($shift) {
            return max(0, $shift->max_volunteers - $shift->users->count());
        });
    }

    public function isPublic()
    {
        return $this->visibility === 'public';
    }

    public function isUnlisted()
    {
        return $this->visibility === 'unlisted';
    }

    public function isDraft()
    {
        return $this->visibility === 'draft';
    }

    public function scopeVisibleToPublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeNotDraft($query)
    {
        return $query->where('visibility', '!=', 'draft');
    }

    public function isMultiDay(): bool
    {
        return $this->start_date->startOfDay() != $this->end_date->startOfDay();
    }

    public function hasPast(): bool
    {
        return $this->end_date < now();
    }

}
