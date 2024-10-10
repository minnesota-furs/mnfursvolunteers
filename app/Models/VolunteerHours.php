<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'volunteer_date',
        'primary_dept_id',
        'hours',
        'description',
        'notes',
        'date',
        'fiscal_ledger_id'
    ];

    protected $casts = [
        'volunteer_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'primary_dept_id');
    }

    public function fiscalLedger()
    {
        return $this->belongsTo(FiscalLedger::class);
    }

    /**
     * Check if the volunteer hour entry has notes set.
     *
     * @return bool
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }

    public function hasDepartment()
    {
        return !is_null($this->primary_dept_id);
    }

}
