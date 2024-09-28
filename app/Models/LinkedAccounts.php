<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedAccounts extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'wordpress_user_id', 'linked_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
