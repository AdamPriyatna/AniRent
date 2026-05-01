<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\BookingController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminUnitController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminBundleController;
use App\Http\Controllers\Admin\AdminBookingController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $featuredUnits = \App\Models\Unit::with('categories')
        ->where('status', 'tersedia')
        ->inRandomOrder()
        ->limit(3)
        ->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'featuredUnits' => $featuredUnits,
    ]);
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (pakai controller, bukan closure)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/detail', [ProfileController::class, 'updateProfile'])->name('profile.detail');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== USER / ANGGOTA =====
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::get('/units/{unit}', [UnitController::class, 'show'])->name('units.show');

    Route::get('/bundles', [BundleController::class, 'index'])->name('bundles.index');
    Route::get('/bundles/{bundle}', [BundleController::class, 'show'])->name('bundles.show');

    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('bookings.mine');

    // ===== ADMIN =====
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        // CRUD Unit
        Route::resource('units', AdminUnitController::class);

        // CRUD Kategori
        Route::resource('categories', AdminCategoryController::class);

        // CRUD User
        Route::resource('users', AdminUserController::class);

        // CRUD Bundle
        Route::resource('bundles', AdminBundleController::class);

        // Booking Management
        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('bookings/{booking}/pickup', [AdminBookingController::class, 'confirmPickup'])->name('bookings.pickup');
        Route::patch('bookings/{booking}/return', [AdminBookingController::class, 'processReturn'])->name('bookings.return');
        Route::get('bookings/history', [AdminBookingController::class, 'history'])->name('bookings.history');
        Route::get('bookings/report', [AdminBookingController::class, 'report'])->name('bookings.report');
    });

});