<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'custom_field_id',
        'value',
    ];

    /**
     * Get the user that owns the custom field value
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the custom field definition
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
