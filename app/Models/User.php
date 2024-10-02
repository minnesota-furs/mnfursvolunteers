<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'wordpress_user_id',
        'primary_dept_id',
        'primary_sector_id',
        'is_linked_to_wp',
        'notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function volunteerHours()
    {
        return $this->hasMany(VolunteerHours::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'primary_dept_id');
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'primary_sector_id');
    }

    public function totalVolunteerHours()
    {
        if (!$this->relationLoaded('volunteerHours')) {
            $this->load('volunteerHours');
        }

        return $this->volunteerHours->sum('hours');
    }

    /**
     * Check if the volunteer entry has notes set.
     *
     * @return bool
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }
}
