<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\WordPressLoginRequest;
use App\Models\User;
use Corcel\Model\User as WPUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class WordPressAuthController extends Controller
{
    private const PENDING_USER_SESSION_KEY = 'wordpress.pending_user';

    public function showLoginForm(): View
    {
        session()->forget(self::PENDING_USER_SESSION_KEY);

        return view('auth.wordpress-login');
    }

    public function login(WordPressLoginRequest $request): RedirectResponse|View
    {
        $wpUser = WPUser::where('user_login', $request->string('email')->toString())
            ->orWhere('user_email', $request->string('email')->toString())
            ->first();

        if (! $wpUser) {
            return back()->withErrors(['email' => 'Invalid MNFurs.org credentials']);
        }

        // Implemented workaround from https://github.com/corcel/corcel/issues/655#issuecomment-2818424369
        $passwordIsValid = false;

        if (str_starts_with($wpUser->user_pass, '$wp')) {
            $passwordToVerify = base64_encode(hash_hmac('sha384', $request->string('password')->toString(), 'wp-sha384', true));
            $passwordIsValid = password_verify($passwordToVerify, substr($wpUser->user_pass, 3));
        }

        if (! $passwordIsValid) {
            return back()->withErrors(['email' => 'Invalid MNFurs.org credentials']);
        }

        $user = User::where('wordpress_user_id', $wpUser->ID)
            ->orWhere('email', $wpUser->user_email)
            ->first();

        if (! $user) {
            session()->put(self::PENDING_USER_SESSION_KEY, [
                'id' => $wpUser->ID,
                'name' => $wpUser->user_login,
                'email' => $wpUser->user_email ?: $wpUser->user_login.'@wordpress.local',
            ]);

            return view('auth.wordpress-account-warning');
        }

        Auth::login($user);

        return $this->redirectAfterLogin($wpUser->user_login);
    }

    public function createAccount(): RedirectResponse
    {
        /** @var array{id: int, name: string, email: string}|null $pendingUser */
        $pendingUser = session()->pull(self::PENDING_USER_SESSION_KEY);

        if (! $pendingUser) {
            return redirect()->route('wordpress.login')
                ->withErrors(['email' => 'Your WordPress account confirmation expired. Please log in again.']);
        }

        $user = User::where('wordpress_user_id', $pendingUser['id'])
            ->orWhere('email', $pendingUser['email'])
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $pendingUser['name'],
                'email' => $pendingUser['email'],
                'password' => Hash::make(str()->random(16)),
                'wordpress_user_id' => $pendingUser['id'],
                'is_linked_to_wp' => true,
                'admin' => false,
                'active' => true,
            ]);
        }

        Auth::login($user);

        return $this->redirectAfterLogin($pendingUser['name']);
    }

    public function cancelAccountCreation(): RedirectResponse
    {
        session()->forget(self::PENDING_USER_SESSION_KEY);

        return redirect()->route('login');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('wordpress.login');
    }

    private function redirectAfterLogin(string $wordpressUsername): RedirectResponse
    {
        return redirect()->route('dashboard')->with('success', [
            'message' => "Welcome <span class=\"text-brand-red\">{$wordpressUsername}</span>! Successfully logged in with MNFurs.org account.",
        ]);
    }
}
