<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Parsedown;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'expires_at',
        'volunteers_only',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'volunteers_only' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->withTimestamps();
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class)->withTimestamps();
    }

    public function getHtmlBodyAttribute(): string
    {
        return (new Parsedown)->setSafeMode(true)->text($this->body);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $departmentIds = $user->departments()->pluck('departments.id');
        $sectorIds = $user->departments()->whereNotNull('sector_id')->pluck('sector_id')->unique();

        if ($departmentIds->isEmpty()) {
            return $query->where(function (Builder $query): void {
                $query->where('volunteers_only', true)
                    ->orWhere(function (Builder $query): void {
                        $query->where('volunteers_only', false)
                            ->doesntHave('departments')
                            ->doesntHave('sectors');
                    });
            });
        }

        return $query->where(function (Builder $query) use ($departmentIds, $sectorIds): void {
            $query->where('volunteers_only', false)->where(function (Builder $query) use ($departmentIds, $sectorIds): void {
                $query->where(function (Builder $query): void {
                    $query->doesntHave('departments')->doesntHave('sectors');
                })->orWhereHas('departments', function (Builder $query) use ($departmentIds): void {
                    $query->whereIn('departments.id', $departmentIds);
                })->orWhereHas('sectors', function (Builder $query) use ($sectorIds): void {
                    $query->whereIn('sectors.id', $sectorIds);
                });
            });
        });
    }
}
