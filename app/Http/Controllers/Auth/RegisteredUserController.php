<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\InviteCode;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Rules\NotBlacklisted;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'first_name'   => ['required', 'string', 'max:255', new NotBlacklisted('name', $request->first_name, $request->last_name)],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, new NotBlacklisted('email')],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
            'invite_code'  => ['nullable', 'string', 'max:32'],
        ]);

        // Resolve invite code if provided
        $inviteCode = null;
        if ($request->filled('invite_code')) {
            $inviteCode = InviteCode::where('code', strtoupper(trim($request->invite_code)))->first();

            if (! $inviteCode || ! $inviteCode->isUsable()) {
                return back()
                    ->withInput()
                    ->withErrors(['invite_code' => 'This invite code is invalid or has expired.']);
            }
        }

        $user = User::create([
            'name'       => $request->name,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        // Apply tags from invite code
        if ($inviteCode) {
            $tagIds = $inviteCode->tags()->pluck('tags.id');
            if ($tagIds->isNotEmpty()) {
                $user->tags()->syncWithoutDetaching($tagIds);
            }
            $inviteCode->recordUse();
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

