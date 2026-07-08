<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\ShiftController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Identity endpoint for third-party apps authenticating via the OAuth
// (Passport) provider, e.g. "Sign in with OpenVolunteer". The "identity"
// scope is required for any access; "volunteer-info" additionally unlocks
// department/sector/staff fields.
Route::middleware(['auth:api', 'scopes:identity'])->get('/oauth/user', function (Request $request) {
    $user = $request->user();

    $data = [
        'id' => $user->id,
        'name' => $user->name,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
    ];

    if ($user->tokenCan('volunteer-info')) {
        $data['is_admin'] = $user->isAdmin();
        $data['is_staff'] = $user->is_staff;
        $data['department'] = $user->department?->name;
        $data['sector'] = $user->sector?->name;
    }

    return $data;
});


Route::get('/job-listings', [JobListingController::class, 'index']);

// Public feed of upcoming shifts for an event, e.g. for embedding on a
// third-party signage board or external website widget.
Route::get('/events/{event}/shifts/upcoming', [ShiftController::class, 'upcoming']);
