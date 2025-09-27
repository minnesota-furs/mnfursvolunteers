<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'user_id',
        'statement',
        'approved',
        'withdrawn',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'withdrawn' => 'boolean',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function voteCount()
    {
        return $this->votes()->count();
    }

    public function isApproved()
    {
        return $this->approved && !$this->withdrawn;
    }
}