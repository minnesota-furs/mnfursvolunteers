<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
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
            'name' => $request->name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

        return redirect()->route('onboarding.index');
    }
}
