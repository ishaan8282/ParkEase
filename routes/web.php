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
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn() => inertia('Home'))->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/spaces/{space}', [SearchController::class, 'show'])->name('spaces.show');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Driver ────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {

    // Booking list & detail
    Route::get('/bookings', [DriverBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [DriverBookingController::class, 'show'])->name('bookings.show');

    // Two-step booking flow
    // Step 1: lock the slot for 5 minutes and get pricing back
    Route::post('/bookings/reserve', [DriverBookingController::class, 'reserve'])->name('bookings.reserve');

    // Step 2: confirm after successful Razorpay payment
    Route::post('/bookings/confirm', [DriverBookingController::class, 'confirm'])->name('bookings.confirm');

    // Legacy store (remove once frontend migrates to two-step flow)
    Route::post('/bookings', [DriverBookingController::class, 'store'])->name('bookings.store');

    // Cancel
    Route::patch('/bookings/{booking}/cancel', [DriverBookingController::class, 'cancel'])->name('bookings.cancel');

    // Slot live-status polling (used until Phase 4 WebSockets are wired up)
    Route::get('/slots/{slot}/availability', [DriverBookingController::class, 'checkSlotAvailability'])
        ->name('slots.availability');
});

// ── Owner ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('spaces', ParkingSpaceController::class);
    Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/confirm', [OwnerBookingController::class, 'confirm'])->name('bookings.confirm');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/edit', [DashboardController::class, 'editProfile'])->name('profile.edit');
    Route::patch('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');

    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');

    Route::resource('spaces', AdminSpaceController::class);
    Route::patch('/spaces/{space}/verify', [AdminSpaceController::class, 'verify'])->name('spaces.verify');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
});
