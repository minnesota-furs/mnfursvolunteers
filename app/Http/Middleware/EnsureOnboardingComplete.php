<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Routes that are exempt from the onboarding check.
     * Prevents redirect loops and allows users to log out mid-wizard.
     */
    protected array $exempt = [
        'onboarding',
        'onboarding/*',
        'logout',
        'login',
        'password/*',
        'setup',
        'setup/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        foreach ($this->exempt as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if ($request->user()->needsOnboarding()) {
            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}
