<?php

use App\Http\Controllers\Admin\BlackoutController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

// Public Catalog
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/items/{slug}', [CatalogController::class, 'show'])->name('items.show');
Route::post('/items/{item}/check-availability', [CatalogController::class, 'checkAvailability'])->name('items.check-availability');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer Protected Routes (F2 & F3)
Route::middleware('auth')->group(function () {
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('my-bookings.index');
    Route::get('/my-bookings/{code}', [BookingController::class, 'show'])->name('my-bookings.show');
    Route::post('/my-bookings/{code}/cancel', [BookingController::class, 'cancel'])->name('my-bookings.cancel');
    Route::get('/my-bookings/{code}/invoice', [BookingController::class, 'invoice'])->name('my-bookings.invoice');
    
    // Placeholder for payment proof upload (completed in F3)
    Route::post('/my-bookings/{code}/payment', function () {
        return back()->with('info', 'Sistem pembayaran sedang diproses.');
    })->name('my-bookings.payment');
});

// Staff & Admin Protected Routes
Route::middleware(['auth', 'role:admin,staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('dashboard');

    // Categories CRUD
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    // Items CRUD + Image management
    Route::resource('items', ItemController::class)->except(['show']);
    Route::post('/items/{item}/images/{image}/primary', [ItemController::class, 'setPrimaryImage'])->name('items.images.primary');
    Route::delete('/items/{item}/images/{image}', [ItemController::class, 'destroyImage'])->name('items.images.destroy');

    // Blackouts CRUD
    Route::resource('blackouts', BlackoutController::class)->only(['index', 'store', 'destroy']);

    // Placeholders for remaining admin routes
    Route::get('/bookings', function () {
        return 'bookings';
    })->name('bookings.index');

    Route::get('/bookings/walk-in', function () {
        return 'walk-in';
    })->name('bookings.walk-in');

    Route::get('/calendar', function () {
        return 'calendar';
    })->name('calendar');

    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', function () {
            return 'reports';
        })->name('reports.index');

        Route::get('/settings', function () {
            return 'settings';
        })->name('settings.index');

        Route::get('/staff', function () {
            return 'staff';
        })->name('staff.index');
    });
});
