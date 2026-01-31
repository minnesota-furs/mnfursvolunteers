<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckSetupCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if any users exist in the database
        if (User::count() === 0) {
            // If no users exist and we're not already on the setup route
            if (!$request->is('setup') && !$request->is('setup/*')) {
                return redirect()->route('setup.index');
            }
        } else {
            // If users exist and we're trying to access setup, redirect to dashboard
            if ($request->is('setup') || $request->is('setup/*')) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
