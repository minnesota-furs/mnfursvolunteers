<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        return $this->hasManyThrough(User::class, Department::class, 'sector_id', 'id', 'id', 'id')
                    ->distinct();
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

    /**
     * Get total staff count across all departments in this sector.
     */
    public function getUniqueStaffCountAttribute()
    {
        return User::whereHas('departments', function ($query) {
                $query->whereIn('departments.id', $this->departments()->pluck('id'));
            })->where('active', true) // Only count active users
            ->distinct('users.id') // Ensure unique count
            ->count('users.id');
    }
    
    /**
     * Get all job listings under this sector through its departments.
     */
    public function jobListings()
    {
        return $this->hasManyThrough(JobListing::class, Department::class, 'sector_id', 'department_id', 'id', 'id');
    }
}
