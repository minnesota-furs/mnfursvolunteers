<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Corcel\Model\User as WPUser;
// use App\Helpers\WordPressHasher;

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

        if (!$wpUser) {
            return back()->withErrors(['email' => 'Invalid MNFurs.org credentials']);
        }

        // Implemented workaround from https://github.com/corcel/corcel/issues/655#issuecomment-2818424369
        $check = false; // Initialize to false
        
        if($wpUser !== null) {
            if ( str_starts_with( $wpUser->user_pass, '$wp' ) ) {
                // Check the password using the current prefixed hash.
                $password_to_verify = base64_encode( hash_hmac( 'sha384', $request->password, 'wp-sha384', true ) );
                $check              = password_verify( $password_to_verify, substr( $wpUser->user_pass, 3 ) );
            }
        }

        if (!$check) {
            return back()->withErrors(['email' => 'Invalid MNFurs.org credentials']);
        }

        // Check if the user exists in Laravel's users table
        $user = User::where('wordpress_user_id', $wpUser->ID)
            ->orWhere('email', $wpUser->email)
            ->first();

        // If not found, create a Laravel user
        if (!$user) {
            $user = User::create([
                'name'          => $wpUser->user_login, 
                'email'         => $wpUser->user_email ?? $wpUser->user_login . '@wordpress.local',
                'password'      => Hash::make(str()->random(16)), // Laravel does not store WordPress passwords
                'wordpress_user_id' => $wpUser->ID, // Link WordPress account
                'is_linked_to_wp' => 1,
                'admin'         => false, // Explicitly set admin to false for WordPress users
                'active'        => true,  // Set new WordPress users as active
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
