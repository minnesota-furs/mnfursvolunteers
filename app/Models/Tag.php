<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'type',
    ];

    // type = null  → applies to both users and shifts
    // type = user  → user tags only
    // type = shift → shift tags only

    public function scopeForUsers($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'user')->orWhereNull('type');
        });
    }

    public function scopeForShifts($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'shift')->orWhereNull('type');
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && !$tag->isDirty('slug')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class)->withTimestamps();
    }
}
