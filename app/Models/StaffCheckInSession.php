<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffCheckInSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'scope',
        'sector_id',
        'department_id',
        'checklist_items',
        'custom_field_ids',
        'collect_signature',
        'created_by',
        'ended_at',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'custom_field_ids' => 'array',
        'collect_signature' => 'boolean',
        'ended_at' => 'datetime',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(StaffCheckIn::class);
    }

    public function eligibleUsers(): Builder
    {
        return User::query()
            ->where('active', true)
            ->whereHas('departments', fn (Builder $query) => $query->when(
                $this->scope === 'sector',
                fn (Builder $query) => $query->where('sector_id', $this->sector_id),
                fn (Builder $query) => $query->whereKey($this->department_id)
            ));
    }

    public function groupName(): string
    {
        return $this->scope === 'sector'
            ? $this->sector->name
            : $this->department->sector->name.': '.$this->department->name;
    }

    public function selectedCustomFields(): Builder
    {
        return CustomField::active()
            ->whereIn('id', $this->custom_field_ids ?? [])
            ->ordered();
    }
}
