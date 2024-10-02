<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerHours extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'volunteer_date', 'primary_dept_id', 'hours', 'description', 'notes', 'date'];

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

    /**
     * Check if the volunteer hour entry has notes set.
     *
     * @return bool
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }
}
