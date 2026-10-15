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
    
    // Payment proof upload
    Route::post('/my-bookings/{code}/payment', [BookingController::class, 'uploadPayment'])->name('my-bookings.payment');
});

// Staff & Admin Protected Routes
Route::middleware(['auth', 'role:admin,staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Categories CRUD
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    // Items CRUD + Image management
    Route::resource('items', ItemController::class)->except(['show']);
    Route::post('/items/{item}/images/{image}/primary', [ItemController::class, 'setPrimaryImage'])->name('items.images.primary');
    Route::delete('/items/{item}/images/{image}', [ItemController::class, 'destroyImage'])->name('items.images.destroy');

    // Blackouts CRUD
    Route::resource('blackouts', BlackoutController::class)->only(['index', 'store', 'destroy']);

    // Staff & Admin Bookings Management
    Route::get('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/walk-in', [\App\Http\Controllers\Admin\BookingController::class, 'walkInCreate'])->name('bookings.walk-in');
    Route::post('/bookings/walk-in', [\App\Http\Controllers\Admin\BookingController::class, 'walkInStore'])->name('bookings.walk-in.store');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/payments/{payment}/verify', [\App\Http\Controllers\Admin\BookingController::class, 'verifyPayment'])->name('bookings.payments.verify');
    Route::post('/bookings/{booking}/payments/{payment}/reject', [\App\Http\Controllers\Admin\BookingController::class, 'rejectPayment'])->name('bookings.payments.reject');
    Route::post('/bookings/{booking}/payments/{payment}/settle', [\App\Http\Controllers\Admin\BookingController::class, 'settlePayment'])->name('bookings.payments.settle');
    Route::post('/bookings/{booking}/pickup', [\App\Http\Controllers\Admin\BookingController::class, 'pickup'])->name('bookings.pickup');
    Route::post('/bookings/{booking}/return', [\App\Http\Controllers\Admin\BookingController::class, 'return'])->name('bookings.return');

    // Visual Schedule Matrix / Calendar
    Route::get('/calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar');

    // Admin Exclusive Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.export');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        Route::get('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->name('staff.store');
        Route::put('/staff/{user}', [\App\Http\Controllers\Admin\StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{user}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('staff.destroy');
    });
});
