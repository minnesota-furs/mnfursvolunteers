<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerHoursController;
use App\Http\Controllers\FiscalLedgerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\WordPressAuthController;
use App\Http\Controllers\VolunteerEventController;
use App\Http\Controllers\VolunteerListingController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/openings', [JobListingController::class, 'guestIndex'])->name('job-listings-public.index');
Route::get('/openings/{id}', [JobListingController::class, 'guestShow'])->name('job-listings-public.show');

Route::get('/volunteering', [VolunteerListingController::class, 'guestIndex'])->name('vol-listings-public.index');
Route::get('/volunteering/{event}', [VolunteerListingController::class, 'guestShow'])->name('vol-listings-public.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/wordpress-login', [WordPressAuthController::class, 'showLoginForm'])->name('wordpress.login');
Route::post('/wordpress-login', [WordPressAuthController::class, 'login']);
// Route::post('/logout', [WordPressAuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User management
    Route::middleware(['isAdmin'])->group(function () {
        Route::get('/users/{id}/delete', [UserController::class, 'delete'])->name('users.delete_confirm');
        Route::get('/users/import', [UserController::class, 'import_view'])->name('users.import');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import_post');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

        // Sectors
        Route::get('/sectors/{id}/delete', [SectorController::class, 'delete'])->name('sectors.delete_confirm');
        Route::resource('sectors', SectorController::class);
        
        // Ledger
        Route::resource('ledger', FiscalLedgerController::class);
        Route::get('/ledgers/{id}/export-csv', [FiscalLedgerController::class, 'exportCsv'])->name('ledgers.export-csv');
    });

    Route::resource('users', UserController::class);

    // Experimental
    Route::get('/org-chart', [UserController::class, 'orgChart'])->name('orgchart');

    // Departments
    Route::get('/departments/{id}/delete', [DepartmentController::class, 'delete'])->name('departments.delete_confirm');
    Route::resource('departments', DepartmentController::class);
    Route::get('/departments-by-sector', [DepartmentController::class, 'getDepartmentsBySector'])->name('get-departments-by-sector');

    // Hours
    Route::get('/hours/create/{user?}', [VolunteerHoursController::class, 'create'])->name('hours.create');
    Route::resource('hours', VolunteerHoursController::class)->except(['create']);

    // Job Listings
    Route::resource('job-listings', JobListingController::class);
    Route::post('/job-listings/{id}/restore', [JobListingController::class, 'restore'])->name('job-listings.restore');

    Route::middleware(['isAdmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
        Route::resource('events.shifts', \App\Http\Controllers\Admin\ShiftController::class)->except(['show']);
        Route::post('events/{event}/shifts/{shift}/duplicate', [\App\Http\Controllers\Admin\ShiftController::class, 'duplicate'])->name('events.shifts.duplicate');

    });

    Route::prefix('volunteer')->name('volunteer.')->group(function () {
        Route::get('events', [VolunteerEventController::class, 'index'])->name('events.index');
        Route::get('events/{event}', [VolunteerEventController::class, 'show'])->name('events.show');
    });
    
    Route::post('/shifts/{shift}/signup', [\App\Http\Controllers\Volunteer\ShiftSignupController::class, 'store'])->name('shifts.signup');
    Route::delete('/shifts/{shift}/signup', [\App\Http\Controllers\Volunteer\ShiftSignupController::class, 'destroy'])->name('shifts.cancel');
});

require __DIR__.'/auth.php';
