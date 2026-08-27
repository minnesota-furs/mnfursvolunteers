<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcatSectorRoleMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'sector_id',
        'concat_role_id',
        'concat_role_name',
        'concat_scope',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}
