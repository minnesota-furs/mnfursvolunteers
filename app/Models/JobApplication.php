<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_listing_id',
        'user_id',
        'name',
        'email',
        'comments',
        'status',
        'claimed_by',
        'claimed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * Get the job listing this application is for.
     */
    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    /**
     * Get the user who claimed this application.
     */
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * Get the user who submitted this application (if logged in).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the internal comments for this application.
     */
    public function internalComments()
    {
        return $this->hasMany(ApplicationComment::class)->orderBy('created_at', 'desc');
    }
}
