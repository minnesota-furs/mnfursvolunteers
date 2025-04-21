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
        'location',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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

}
