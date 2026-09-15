<?php

use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\ShiftController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

        // "department"/"sector" reflect only the user's designated primary
        // department, which is frequently unset (e.g. admins spanning
        // multiple departments). "departments"/"sectors" report every
        // department the user actually belongs to, which is what
        // eligibility-style checks should use instead.
        $data['department'] = $user->department?->name;
        $data['sector'] = $user->sector?->name;

        $departments = $user->departments()->with('sector')->get();
        $data['departments'] = $departments->pluck('name')->values();
        $data['sectors'] = $departments->pluck('sector.name')->filter()->unique()->values();

        // Values for every active custom field the user has answered, keyed
        // by field_key — e.g. a third-party integration confirming a
        // volunteer's on-file t-shirt size. See GET /api/custom-fields for
        // field definitions (name, type, options).
        $data['custom_fields'] = $user->customFieldValues()
            ->whereHas('customField', fn ($query) => $query->where('is_active', true))
            ->with('customField')
            ->get()
            ->mapWithKeys(fn ($value) => [$value->customField->field_key => $value->value]);
    }

    return $data;
});

Route::get('/job-listings', [JobListingController::class, 'index']);

// Public feed of upcoming shifts for an event, e.g. for embedding on a
// third-party signage board or external website widget.
Route::get('/events/{event}/shifts/upcoming', [ShiftController::class, 'upcoming']);

// Public list of sectors, e.g. for a third-party "Sign in with
// OpenVolunteer" integration building an eligibility allow-list.
Route::get('/sectors', [SectorController::class, 'index']);

// Public list of active custom field definitions, e.g. for a third-party
// integration's admin screen to pick which field to surface.
Route::get('/custom-fields', [CustomFieldController::class, 'index']);
