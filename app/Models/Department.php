<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->hasMany(User::class, 'primary_dept_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHours::class);
    }
}
