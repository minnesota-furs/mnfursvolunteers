<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class InviteCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'max_uses',
        'uses_count',
        'is_active',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
        'max_uses'   => 'integer',
        'uses_count' => 'integer',
    ];

    // ─── Boot ────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (InviteCode $model) {
            if (empty($model->code)) {
                $model->code = static::generateUniqueCode();
            } else {
                $model->code = strtoupper($model->code);
            }
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Determine if this invite code can currently be used.
     */
    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Increment use count and deactivate if limit reached.
     */
    public function recordUse(): void
    {
        $this->increment('uses_count');

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            $this->update(['is_active' => false]);
        }
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'invite_code_tag');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
