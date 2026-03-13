<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Cloudflare cookies are not set by Laravel and must not be touched
        '__cf_bm',
        '__cflb',
        'cf_clearance',
    ];
}
