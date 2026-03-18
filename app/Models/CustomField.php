<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'field_key',
        'field_type',
        'options',
        'is_required',
        'is_active',
        'user_editable',
        'force_set',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'user_editable' => 'boolean',
        'force_set' => 'boolean',
    ];

    /**
     * Get the custom field values for this field
     */
    public function customFieldValues()
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /**
     * Scope to get only active custom fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to get only user-editable custom fields
     */
    public function scopeUserEditable($query)
    {
        return $query->where('user_editable', true);
    }
}
