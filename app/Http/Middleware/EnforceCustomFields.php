<?php

namespace App\Http\Middleware;

use App\Models\CustomField;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceCustomFields
{
    /**
     * Routes that are exempt from the custom field enforcement.
     * Prevents redirect loops and allows users to log out or submit the form.
     */
    protected array $exempt = [
        'required-fields',
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

        // Allow exempt routes through without checking
        foreach ($this->exempt as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        $user = $request->user()->load('customFieldValues');

        $missingFields = CustomField::active()
            ->where('force_set', true)
            ->get()
            ->filter(function (CustomField $field) use ($user) {
                $value = $user->customFieldValues
                    ->firstWhere('custom_field_id', $field->id)
                    ?->value;

                return is_null($value) || $value === '';
            });

        if ($missingFields->isNotEmpty()) {
            return redirect()->route('profile.required-fields');
        }

        return $next($request);
    }
}
