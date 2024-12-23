<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'position_title',
        'visibility',
        'description',
        'number_of_openings',
        'closing_date',
    ];

    protected $casts = [
        'closing_date' => 'datetime'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
