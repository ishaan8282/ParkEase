<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Driver\SearchController;
use App\Http\Controllers\Driver\BookingController as DriverBookingController;
use App\Http\Controllers\Owner\ParkingSpaceController;
use App\Http\Controllers\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ParkingSpaceController as AdminSpaceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;

// Public
Route::get('/', fn() => inertia('Home'))->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/spaces/{space}', [SearchController::class, 'show'])->name('spaces.show');

// Auth
// Route::middleware(['guest', 'auth', 'role:owner', 'role:driver', 'role:admin'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
// });
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Driver routes
Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/bookings', [DriverBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [DriverBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [DriverBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [DriverBookingController::class, 'cancel'])->name('bookings.cancel');
});

// Owner routes
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('spaces', ParkingSpaceController::class);
    Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/confirm', [OwnerBookingController::class, 'confirm'])->name('bookings.confirm');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('spaces', AdminSpaceController::class);
    Route::patch('/spaces/{space}/verify', [AdminSpaceController::class, 'verify'])->name('spaces.verify');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
});
