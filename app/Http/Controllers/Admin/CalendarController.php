<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date'))->startOfDay()
            : today()->startOfWeek();

        // 14 days schedule matrix
        $daysCount = 14;
        $endDate = $startDate->copy()->addDays($daysCount - 1)->endOfDay();

        $dates = [];
        for ($i = 0; $i < $daysCount; $i++) {
            $dates[] = $startDate->copy()->addDays($i);
        }

        $categories = Category::active()->orderBy('name')->get();

        $itemsQuery = Item::active()->with([
            'category',
            'blackouts' => function ($q) use ($startDate, $endDate) {
                $q->where('start_at', '<=', $endDate)
                  ->where('end_at', '>=', $startDate);
            },
            'bookingItems.booking' => function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', [BookingStatus::CONFIRMED, BookingStatus::PICKED_UP, BookingStatus::PENDING])
                  ->where('start_at', '<=', $endDate)
                  ->where('end_at', '>=', $startDate)
                  ->with('user');
            },
        ])->orderBy('name');

        if ($request->filled('category_id')) {
            $itemsQuery->where('category_id', $request->integer('category_id'));
        }

        $items = $itemsQuery->get();

        return view('admin.calendar', compact('startDate', 'endDate', 'dates', 'categories', 'items'));
    }
}
