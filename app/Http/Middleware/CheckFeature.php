<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature  The feature name to check (without the "feature_" prefix)
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!feature_enabled($feature)) {
            abort(404, 'This feature is currently disabled.');
        }

        return $next($request);
    }
}
