<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_key',
        'html_content',
        'css_content',
        'gjs_data',
    ];

    protected $casts = [
        'gjs_data' => 'array',
    ];
}
