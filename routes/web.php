<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Public Home Placeholder (will be replaced by catalog in F1)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Placeholders for routes referenced in layouts
Route::middleware('auth')->group(function () {
    Route::get('/my-bookings', function () {
        return view('welcome');
    })->name('my-bookings.index');
});

Route::middleware(['auth', 'role:admin,staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('dashboard');

    Route::get('/bookings', function () {
        return 'bookings';
    })->name('bookings.index');

    Route::get('/bookings/walk-in', function () {
        return 'walk-in';
    })->name('bookings.walk-in');

    Route::get('/calendar', function () {
        return 'calendar';
    })->name('calendar');

    Route::get('/categories', function () {
        return 'categories';
    })->name('categories.index');

    Route::get('/items', function () {
        return 'items';
    })->name('items.index');

    Route::get('/blackouts', function () {
        return 'blackouts';
    })->name('blackouts.index');

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
