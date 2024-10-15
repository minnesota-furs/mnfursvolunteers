<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerHoursController;
use App\Http\Controllers\FiscalLedgerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SectorController;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/users/{id}/delete', [UserController::class, 'delete'])->name('users.delete_confirm');
    Route::resource('users', UserController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::post('/departments/edit/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::get('/departments/{department}/delete', [DepartmentController::class, 'delete'])->name('departments.delete');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/sectors/create', [SectorController::class, 'create'])->name('sectors.create');
    Route::post('/sectors', [SectorController::class, 'store'])->name('sectors.store');
    Route::get('/sectors', [SectorController::class, 'edit'])->name('sectors.edit');
    Route::post('/sectors/{id}', [SectorController::class, 'update'])->name('sectors.update');
    Route::get('/sectors/{id}/delete', [SectorController::class, 'delete'])->name('sectors.delete');
    Route::post('/sectors/{id}', [SectorController::class, 'destroy'])->name('sectors.destroy');
});

Route::resource('sector', SectorController::class);
Route::resource('department', DepartmentController::class);
Route::resource('ledger', FiscalLedgerController::class);

Route::get('/departments-by-sector', [DepartmentController::class, 'getDepartmentsBySector'])->name('get-departments-by-sector');

Route::get('/hours/create/{user?}', [VolunteerHoursController::class, 'create'])->name('hours.create');
Route::resource('hours', VolunteerHoursController::class)->except(['create']);

require __DIR__.'/auth.php';
