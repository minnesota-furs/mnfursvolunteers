<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPublicSiteEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if public site is disabled
        if (app_setting('disable_public_site', false)) {
            // If user is authenticated, redirect to dashboard
            if (auth()->check()) {
                return redirect()->route('dashboard');
            }
            
            // If not authenticated, redirect to login
            return redirect()->route('login');
        }

        return $next($request);
    }
}
