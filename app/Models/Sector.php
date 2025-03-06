<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'description'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'primary_sector_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get total staff count across all departments in this sector.
     */
    public function getTotalStaffCountAttribute()
    {
        return $this->departments()
            ->withCount(['users' => function ($query) {
                $query->where('active', true); // Only count active users
            }])->get()->sum('users_count');
    }
}
