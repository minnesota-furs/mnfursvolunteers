<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerHours extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'hours', 'description', 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
