<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Corcel\Model\User as WPUser;
use App\Helpers\WordPressHasher;

class WordPressAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.wordpress-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Find the WordPress user by username or email
        $wpUser = WPUser::where('user_login', $request->email)
                        ->orWhere('user_email', $request->email)
                        ->first();

        // If no WordPress user is found
        if (!$wpUser || !WordPressHasher::check($request->password, $wpUser->user_pass)) {
            return back()->withErrors(['email' => 'Invalid MNFurs.org credentials']);
        }

        // Check if the user exists in Laravel's users table
        $user = User::where('wordpress_user_id', $wpUser->ID)->first();

        // If not found, create a Laravel user
        if (!$user) {
            $user = User::create([
                'name'          => $wpUser->user_login, 
                'email'         => $wpUser->user_email ?? $wpUser->user_login . '@wordpress.local',
                'password'      => Hash::make(str()->random(16)), // Laravel does not store WordPress passwords
                'wordpress_user_id' => $wpUser->ID, // Link WordPress account
                'is_linked_to_wp' => 1,
            ]);
        }

        // Authenticate the user in Laravel
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', [
            'message' => "Welcome <span class=\"text-brand-red\">{$wpUser->user_login}</span>! Successfully logged in with MNFurs.org account.",
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('wordpress.login');
    }
}
