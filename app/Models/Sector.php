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

    public function users_alt()
    {
        return $this->departments()
                    ->with('users') // Load users for each department
                    ->get()
                    ->flatMap(function ($department) {
                        return $department->users; // Flatten users into a single collection
                    });
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
