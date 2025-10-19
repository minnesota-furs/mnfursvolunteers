<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerHoursController;
use App\Http\Controllers\FiscalLedgerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\WordPressAuthController;
use App\Http\Controllers\VolunteerEventController;
use App\Http\Controllers\VolunteerGuestController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\OneOffEventController;
use App\Http\Controllers\Volunteer\ShiftSignupController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\ElectionController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

// Public job listings
Route::prefix('openings')->name('job-listings-public.')->group(function () {
    Route::get('/', [JobListingController::class, 'guestIndex'])->name('index');
    Route::get('/{id}', [JobListingController::class, 'guestShow'])->name('show');
});

// Public volunteer listings
Route::prefix('volunteering')->name('vol-listings-public.')->group(function () {
    Route::get('/', [VolunteerGuestController::class, 'guestIndex'])->name('index');
    Route::get('/{event}', [VolunteerGuestController::class, 'guestShow'])->name('show');
});

// Public elections
Route::prefix('pub-elections')->name('elections-public.')->group(function () {
    Route::get('/', [ElectionController::class, 'guestIndex'])->name('index');
    Route::get('/{election}', [ElectionController::class, 'guestShow'])->name('show');
});

// TEST ROUTE - Remove this in production!
Route::get('/test-500-error', function () {
    throw new Exception('This is a test 500 error to verify the error page works correctly.');
})->name('test.500');

// Dashboard (requires auth & verified)
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::post('/link-wordpress', [ProfileController::class, 'linkWordPress'])->name('link-wordpress');
        Route::delete('/unlink-wordpress', [ProfileController::class, 'unlinkWordPress'])->name('unlink-wordpress');
        Route::patch('/email-preferences', [ProfileController::class, 'updateEmailPreferences'])->name('email-preferences');
    });

    // One Off Events
    Route::prefix('one-off-events')->name('one-off-events.')->group(function () {
        Route::get('/', [OneOffEventController::class, 'index'])->name('index');
        Route::get('/create', [OneOffEventController::class, 'create'])->middleware('can:manage-events')->name('create');
        Route::post('/', [OneOffEventController::class, 'store'])->middleware('can:manage-events')->name('store');
        Route::get('/{oneOffEvent}', [OneOffEventController::class, 'show'])->name('show');
        Route::get('/{oneOffEvent}/edit', [OneOffEventController::class, 'edit'])->middleware('can:manage-events')->name('edit');
        Route::put('/{oneOffEvent}', [OneOffEventController::class, 'update'])->middleware('can:manage-events')->name('update');
        Route::delete('/{oneOffEvent}', [OneOffEventController::class, 'destroy'])->middleware('can:manage-events')->name('destroy');
        Route::get('/{oneOffEvent}/check-ins', [OneOffEventController::class, 'checkIns'])->middleware('can:manage-events')->name('check-ins');
        Route::post('/{oneOffEvent}/check-ins/{checkIn}/credit', [OneOffEventController::class, 'manualCreditHours'])->middleware('can:manage-events')->name('check-ins.credit');
        Route::post('/{oneOffEvent}/check-in', [OneOffEventController::class, 'checkIn'])->name('check-in');
    });
    
    // Users
    Route::middleware(['can:manage-users'])->group(function () {
        Route::get('/users/import', [UserController::class, 'import_view'])->name('users.import');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import_post');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->middleware('isAdmin')->name('users.restore');
    });

    Route::resource('users', UserController::class)->only(['create', 'edit', 'store', 'destroy', 'update'])->middleware(['can:manage-users']);
    Route::resource('users', UserController::class)->only(['index', 'show']);
    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'edit'])->middleware('isAdmin')->name('users.permissions.edit');
    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'update'])->middleware('isAdmin')->name('users.permissions.update');

    // Admin User Management
    Route::middleware(['can:manage-users'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    });

    // Org chart view
    Route::get('/org-chart', [UserController::class, 'orgChart'])->name('orgchart');

    // Application Settings
    Route::middleware('isAdmin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\SettingsController::class, 'update'])->name('update');
        Route::delete('/reset-logo', [\App\Http\Controllers\SettingsController::class, 'resetLogo'])->name('reset-logo');
        Route::delete('/reset-favicon', [\App\Http\Controllers\SettingsController::class, 'resetFavicon'])->name('reset-favicon');
    });

    // Reports
    Route::prefix('report')->name('report.')->middleware('can:view-reports')->group(function () {
        Route::get('/users-without-departments', [ReportsController::class, 'usersWithoutDepartments'])->name('usersWithoutDepartments');
        Route::get('/users-without-hours', [ReportsController::class, 'usersWithoutHoursThisPeriod'])->name('usersWithoutHoursThisPeriod');
    });

    // Departments
    Route::get('/departments-by-sector', [DepartmentController::class, 'getDepartmentsBySector'])->name('get-departments-by-sector');
    Route::get('/departments/{id}/delete', [DepartmentController::class, 'delete'])->middleware('isAdmin')->name('departments.delete_confirm');

    Route::resource('departments', DepartmentController::class)->only(['create', 'edit', 'store', 'destroy', 'update'])->middleware(['isAdmin']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'show']);

    // Sectors & Ledger
    Route::middleware('isAdmin')->group(function () {
        // Sectors
        Route::get('/sectors/{id}/delete', [SectorController::class, 'delete'])->name('sectors.delete_confirm');
        Route::resource('sectors', SectorController::class);
        // Ledger
        Route::get('/ledgers/{id}/export-csv', [FiscalLedgerController::class, 'exportCsv'])->name('ledgers.export-csv');
        Route::resource('ledger', FiscalLedgerController::class);
    });

    // Volunteer Hours
    Route::get('/hours/create/{user?}', [VolunteerHoursController::class, 'create'])->name('hours.create');
    Route::resource('hours', VolunteerHoursController::class)->except(['create']);

    // Joblistings
    Route::middleware('can:manage-job-listings')->group(function () {
        Route::resource('job-listings', JobListingController::class)->only(['create', 'edit', 'store', 'destroy', 'update']);
        Route::post('/job-listings/{id}/restore', [JobListingController::class, 'restore'])->name('job-listings.restore');
    });
    Route::resource('job-listings', JobListingController::class)->only(['index', 'show']);

    // Volunteer Events
    Route::middleware('can:manage-volunteer-events')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('events', EventController::class);
        Route::resource('events.shifts', ShiftController::class)->except(['show']);
        Route::post('events/{event}/shifts/{shift}/duplicate', [ShiftController::class, 'duplicate'])->name('events.shifts.duplicate');
        Route::post('events/{event}/shifts/{shift}/advanced-duplicate', [ShiftController::class, 'advancedDuplicate'])->name('events.shifts.advanced-duplicate');
        Route::post('events/{event}/shifts/import', [ShiftController::class, 'importCsv'])->name('events.shifts.import');
        Route::delete('events/{event}/shifts/{shift}/remove-volunteer/{user}', [ShiftController::class, 'removeVolunteer'])->name('events.shifts.remove-volunteer');
        Route::post('events/{event}/shifts/{shift}/add-volunteer/{user}', [ShiftController::class, 'addVolunteer'])->name('events.shifts.add-volunteer');
        Route::get('/events/{event}/volunteers', [EventController::class, 'volunteerList'])->name('events.volunteers');
        Route::get('/events/{event}/allShifts', [EventController::class, 'indexWithShifts'])->name('events.allShifts');
        Route::get('/events/{event}/allShifts/print', [EventController::class, 'indexWithShiftsPrint'])->name('events.allShifts.print');
        Route::get('/events/{event}/agenda', [EventController::class, 'agenda'])->name('events.agenda');
        Route::get('/events/{event}/log', [EventController::class, 'log'])->name('events.log');
        Route::get('/events/{event}/editors', [EventController::class, 'editors'])->name('events.editors');
        Route::post('/events/{event}/editors', [EventController::class, 'addEditor'])->name('events.editors.add');
        Route::delete('/events/{event}/editors/{user}', [EventController::class, 'removeEditor'])->name('events.editors.remove');
    });

    Route::prefix('volunteer')->name('volunteer.')->group(function () {
        Route::get('events', [VolunteerEventController::class, 'index'])->name('events.index');
        Route::get('events/my-shifts', [VolunteerEventController::class, 'myShiftsAll'])->name('events.my-shifts-all');
        Route::get('events/{event}', [VolunteerEventController::class, 'show'])->name('events.show');
        Route::get('events/{event}/my-shifts', [VolunteerEventController::class, 'myShifts'])->name('events.my-shifts');
    });

    
    // Shift Signups
    Route::post('/shifts/{shift}/quick-add', [ShiftSignupController::class, 'storeByVolCode'])->name('shifts.quick-add');
    Route::post('/shifts/{shift}/signup', [ShiftSignupController::class, 'store'])->name('shifts.signup');
    Route::delete('/shifts/{shift}/signup', [ShiftSignupController::class, 'destroy'])->name('shifts.cancel');

    // Board Elections - Admin Management
    Route::middleware('can:manage-elections')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('elections', \App\Http\Controllers\Admin\ElectionController::class);
        Route::get('elections/{election}/candidates', [\App\Http\Controllers\Admin\ElectionController::class, 'candidates'])->name('elections.candidates');
        Route::get('elections/{election}/candidates/create', [\App\Http\Controllers\Admin\ElectionController::class, 'createCandidate'])->name('elections.candidates.create');
        Route::post('elections/{election}/candidates', [\App\Http\Controllers\Admin\ElectionController::class, 'storeCandidate'])->name('elections.candidates.store');
        Route::get('elections/{election}/candidates/{candidate}/edit', [\App\Http\Controllers\Admin\ElectionController::class, 'editCandidate'])->name('elections.candidates.edit');
        Route::put('elections/{election}/candidates/{candidate}', [\App\Http\Controllers\Admin\ElectionController::class, 'updateCandidate'])->name('elections.candidates.update');
        Route::post('elections/{election}/candidates/{candidate}/approve', [\App\Http\Controllers\Admin\ElectionController::class, 'approveCandidate'])->name('elections.candidates.approve');
        Route::post('elections/{election}/candidates/{candidate}/reject', [\App\Http\Controllers\Admin\ElectionController::class, 'rejectCandidate'])->name('elections.candidates.reject');
        Route::delete('elections/{election}/candidates/{candidate}', [\App\Http\Controllers\Admin\ElectionController::class, 'removeCandidate'])->name('elections.candidates.destroy');
        Route::get('elections/{election}/voters', [\App\Http\Controllers\Admin\ElectionController::class, 'voters'])->name('elections.voters');
        Route::get('elections/{election}/results', [\App\Http\Controllers\Admin\ElectionController::class, 'results'])->name('elections.results');
    });

    // Board Elections - Public Voting
    Route::prefix('elections')->name('elections.')->group(function () {
        Route::get('/', [ElectionController::class, 'index'])->name('index');
        Route::get('/{election}', [ElectionController::class, 'show'])->name('show');
        Route::get('/{election}/results', [ElectionController::class, 'results'])->name('results');
        Route::post('/{election}/vote', [ElectionController::class, 'vote'])->name('vote');
        Route::get('/{election}/nominate', [ElectionController::class, 'nominate'])->name('nominate');
        Route::post('/{election}/nominate', [ElectionController::class, 'storeNomination'])->name('nominate.store');
    });
});


require __DIR__.'/auth.php';
