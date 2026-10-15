<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    public function index(Request $request): View
    {
        $categories = Category::active()->orderBy('name')->get();

        $query = Item::active()->with(['category', 'images']);

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $categorySlug = $request->string('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_day', '>=', $request->integer('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_day', '<=', $request->integer('max_price'));
        }

        $startAt = null;
        $endAt = null;
        if ($request->filled('start_at') && $request->filled('end_at')) {
            try {
                $startAt = Carbon::parse($request->string('start_at'));
                $endAt = Carbon::parse($request->string('end_at'));

                if ($endAt->gt($startAt)) {
                    // Filter items with availability > 0 in this range
                    $allItems = $query->get();
                    $availableItemIds = [];

                    foreach ($allItems as $item) {
                        $availableStock = $this->availabilityService->getAvailableStock($item, $startAt, $endAt);
                        if ($availableStock > 0) {
                            $availableItemIds[] = $item->id;
                            $item->calculated_available_stock = $availableStock;
                        }
                    }

                    $items = Item::with(['category', 'images'])
                        ->whereIn('id', $availableItemIds)
                        ->paginate(12)
                        ->withQueryString();

                    // Re-attach calculated stock
                    foreach ($items as $item) {
                        $item->calculated_available_stock = $this->availabilityService->getAvailableStock($item, $startAt, $endAt);
                    }

                    return view('catalog.index', compact('items', 'categories', 'startAt', 'endAt'));
                }
            } catch (\Throwable $e) {
                // Invalid dates, proceed without date filtering
            }
        }

        $items = $query->latest()->paginate(12)->withQueryString();

        return view('catalog.index', compact('items', 'categories', 'startAt', 'endAt'));
    }

    public function show(string $slug): View
    {
        $item = Item::active()
            ->with(['category', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedItems = Item::active()
            ->with(['category', 'images'])
            ->where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->take(4)
            ->get();

        return view('catalog.show', compact('item', 'relatedItems'));
    }

    public function checkAvailability(Request $request, Item $item): JsonResponse
    {
        $request->validate([
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ]);

        $startAt = Carbon::parse($request->input('start_at'));
        $endAt = Carbon::parse($request->input('end_at'));

        $availableStock = $this->availabilityService->getAvailableStock($item, $startAt, $endAt);

        return response()->json([
            'item_id' => $item->id,
            'stock_total' => $item->stock,
            'stock_available' => $availableStock,
            'is_available' => $availableStock > 0,
            'start_at' => $startAt->toIso8601String(),
            'end_at' => $endAt->toIso8601String(),
        ]);
    }
}
