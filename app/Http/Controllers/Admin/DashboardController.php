<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        // Key operational metrics
        $todayPickupsQuery = Booking::where('status', BookingStatus::CONFIRMED)
            ->whereDate('start_at', $today)
            ->with(['user', 'items.item.images', 'items.item.category']);
        $todayPickupsCount = $todayPickupsQuery->count();
        $todayPickups = $todayPickupsQuery->limit(5)->get();

        $todayReturnsQuery = Booking::where('status', BookingStatus::PICKED_UP)
            ->whereDate('end_at', $today)
            ->with(['user', 'items.item.images', 'items.item.category']);
        $todayReturnsCount = $todayReturnsQuery->count();
        $todayReturns = $todayReturnsQuery->limit(5)->get();

        $activeRentalsCount = Booking::where('status', BookingStatus::PICKED_UP)->count();

        $overdueQuery = Booking::overdue()->with(['user', 'items.item.images', 'items.item.category']);
        $overdueCount = $overdueQuery->count();
        $overdueBookings = $overdueQuery->limit(5)->get();

        $pendingPaymentsQuery = Payment::where('status', PaymentStatus::PENDING)
            ->where('type', PaymentType::RENTAL_AND_DEPOSIT)
            ->with(['booking.user']);
        $pendingPaymentsCount = $pendingPaymentsQuery->count();
        $pendingPayments = $pendingPaymentsQuery->limit(5)->get();

        $totalItems = Item::where('status', ItemStatus::ACTIVE)->count();
        $totalCustomers = User::where('role', \App\Enums\UserRole::CUSTOMER)->count();

        return view('admin.dashboard', compact(
            'todayPickupsCount',
            'todayPickups',
            'todayReturnsCount',
            'todayReturns',
            'activeRentalsCount',
            'overdueCount',
            'overdueBookings',
            'pendingPaymentsCount',
            'pendingPayments',
            'totalItems',
            'totalCustomers'
        ));
    }
}
