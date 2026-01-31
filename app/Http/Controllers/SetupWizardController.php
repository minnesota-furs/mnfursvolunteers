<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\NotBlacklisted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetupWizardController extends Controller
{
    /**
     * Display the setup wizard form.
     */
    public function index()
    {
        // If users already exist, redirect to dashboard
        if (User::count() > 0) {
            return redirect()->route('dashboard');
        }

        return view('setup.wizard');
    }

    /**
     * Process the setup wizard and create the admin account.
     */
    public function store(Request $request)
    {
        // If users already exist, prevent setup
        if (User::count() > 0) {
            return redirect()->route('dashboard')
                ->with('error', 'Setup has already been completed.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255', new NotBlacklisted('name', $request->first_name, $request->last_name)],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', new NotBlacklisted('email')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the admin user without triggering observers
        // This prevents audit log issues when no user is authenticated
        $user = User::withoutEvents(function () use ($validated) {
            return User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'active' => true,
                'admin' => true,
                // Grant all permissions to the admin user
                'permissions' => array_keys(config('permissions')),
            ]);
        });

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your admin account has been created successfully.');
    }
}
