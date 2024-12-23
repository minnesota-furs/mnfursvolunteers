<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalLedger extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'volunteer_hours', 'fiscal_ledger_id', 'user_id')
                    ->withPivot('hours')
                    ->withTimestamps();
    }

    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHours::class);
    }

    /**
     * Get the total volunteer hours for this fiscal ledger.
     *
     * @return float
     */
    public function totalVolunteerHours()
    {
        return $this->volunteerHours()->sum('hours');
    }
}
