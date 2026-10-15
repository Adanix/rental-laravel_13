<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date'))->startOfDay()
            : today()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date'))->endOfDay()
            : today()->endOfMonth();

        // Valid completed / revenue-generating bookings in range
        $bookingsQuery = Booking::whereIn('status', [BookingStatus::CONFIRMED, BookingStatus::PICKED_UP, BookingStatus::RETURNED])
            ->whereBetween('start_at', [$startDate, $endDate]);

        $totalRental = (int) $bookingsQuery->sum('subtotal_rental');
        $totalDiscount = (int) $bookingsQuery->sum('discount_amount');
        $totalLateFee = (int) $bookingsQuery->sum('late_fee');
        $totalDamageFee = (int) $bookingsQuery->sum('damage_fee');
        $netRevenue = $totalRental + $totalLateFee + $totalDamageFee - $totalDiscount;
        $totalCompleted = (clone $bookingsQuery)->where('status', BookingStatus::RETURNED)->count();
        $totalBookingsCount = $bookingsQuery->count();

        // Top rented items
        $topItems = BookingItem::whereHas('booking', function ($q) use ($startDate, $endDate) {
            $q->whereIn('status', [BookingStatus::CONFIRMED, BookingStatus::PICKED_UP, BookingStatus::RETURNED])
              ->whereBetween('start_at', [$startDate, $endDate]);
        })
        ->with('item.category')
        ->select('item_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
        ->groupBy('item_id')
        ->orderByDesc('total_qty')
        ->limit(10)
        ->get();

        $recentBookings = (clone $bookingsQuery)->with(['user', 'items.item'])->latest()->limit(10)->get();

        return view('admin.reports.index', compact(
            'startDate',
            'endDate',
            'totalRental',
            'totalDiscount',
            'totalLateFee',
            'totalDamageFee',
            'netRevenue',
            'totalCompleted',
            'totalBookingsCount',
            'topItems',
            'recentBookings'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date'))->startOfDay()
            : today()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date'))->endOfDay()
            : today()->endOfMonth();

        $fileName = 'laporan-rental-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.csv';

        $bookings = Booking::whereIn('status', [BookingStatus::CONFIRMED, BookingStatus::PICKED_UP, BookingStatus::RETURNED])
            ->whereBetween('start_at', [$startDate, $endDate])
            ->with(['user', 'items.item'])
            ->orderBy('start_at')
            ->get();

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'Kode Pesanan',
                'Nama Pelanggan',
                'No HP',
                'Mulai Sewa',
                'Selesai Sewa',
                'Durasi (Hari)',
                'Subtotal Sewa (Rp)',
                'Diskon (Rp)',
                'Denda Telat (Rp)',
                'Denda Rusak (Rp)',
                'Pendapatan Bersih (Rp)',
                'Deposit (Rp)',
                'Status Pesanan',
            ]);

            foreach ($bookings as $b) {
                $net = ($b->subtotal_rental + $b->late_fee + $b->damage_fee) - $b->discount_amount;
                fputcsv($handle, [
                    $b->code,
                    $b->user->name ?? '-',
                    $b->user->phone ?? '-',
                    $b->start_at->format('Y-m-d H:i'),
                    $b->end_at->format('Y-m-d H:i'),
                    $b->duration_days,
                    $b->subtotal_rental,
                    $b->discount_amount,
                    $b->late_fee,
                    $b->damage_fee,
                    $net,
                    $b->deposit_total,
                    $b->status->label(),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
