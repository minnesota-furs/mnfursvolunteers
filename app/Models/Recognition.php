<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recognition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'granted_by_user_id',
        'sector_id',
        'name',
        'type',
        'date',
        'description',
        'is_private',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'is_private' => 'boolean',
    ];

    /**
     * Get the user who received the recognition.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who granted the recognition.
     */
    public function grantedByUser()
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    /**
     * Get the sector that granted the recognition.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Scope to get only non-private recognitions or recognitions visible to the given user.
     */
    public function scopeVisible($query, ?User $user = null)
    {
        if ($user && $user->hasPermission('manage-recognition')) {
            // Users with permission to manage recognition can see all
            return $query;
        }

        if ($user) {
            // Users can see public recognitions or their own recognitions (private or not)
            return $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                  ->orWhere('user_id', $user->id);
            });
        }

        // Unauthenticated users can only see public recognitions
        return $query->where('is_private', false);
    }
}
