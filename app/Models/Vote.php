<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'user_id',
        'candidate_id',
    ];

    /**
     * Check if this vote is for a specific candidate
     */
    public function isForCandidate($candidateId)
    {
        return $this->candidate_id == $candidateId;
    }



    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}