<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Dotlogics\Grapesjs\App\Traits\EditableTrait;
use Dotlogics\Grapesjs\App\Contracts\Editable;

class Page extends Model implements Editable
{
    use HasFactory, EditableTrait;

    protected $primaryKey = 'id';

    protected $fillable = [
        'slug',
        'gjs_data'
    ];

    /**
     * Override trait's accessor to handle edge cases
     */
    public function getGjsDataAttribute($value): array
    {
        // If already an array (shouldn't happen but just in case)
        if (is_array($value)) {
            return $value;
        }
        
        // If null or empty, return empty array
        if (empty($value)) {
            return [];
        }
        
        // Decode JSON string to array
        $decoded = json_decode($value, true);
        
        // Ensure we always return an array
        return is_array($decoded) ? $decoded : [];
    }
}
