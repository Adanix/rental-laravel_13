<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Setting;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate duration days from start and end dates.
     * Hari sewa = max(1, ceil(durasi_jam / 24)).
     */
    public function calculateDays(Carbon $startAt, Carbon $endAt): int
    {
        $hours = $startAt->diffInHours($endAt, false);
        if ($hours <= 0) {
            return 1;
        }

        return (int) max(1, ceil($hours / 24));
    }

    /**
     * Calculate rental pricing, discounts, and deposit for items.
     *
     * @param array $itemsWithQty Array of ['item' => Item, 'qty' => int] or ['item_id' => int, 'qty' => int]
     */
    public function calculate(array $itemsWithQty, Carbon $startAt, Carbon $endAt): array
    {
        $days = $this->calculateDays($startAt, $endAt);
        $hours = (int) $startAt->diffInHours($endAt, false);

        $rawSubtotal = 0;
        $depositTotal = 0;
        $processedItems = [];

        foreach ($itemsWithQty as $entry) {
            $item = $entry['item'] ?? Item::findOrFail($entry['item_id']);
            $qty = (int) ($entry['qty'] ?? 1);

            $unitPrice = (int) $item->price_per_day;
            $unitDeposit = (int) $item->deposit;

            $itemSubtotal = $days * $unitPrice * $qty;
            $itemDeposit = $unitDeposit * $qty;

            $rawSubtotal += $itemSubtotal;
            $depositTotal += $itemDeposit;

            $processedItems[] = [
                'item' => $item,
                'item_id' => $item->id,
                'name' => $item->name,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'unit_deposit' => $unitDeposit,
                'subtotal' => $itemSubtotal,
                'deposit' => $itemDeposit,
            ];
        }

        // Check long rental discount from settings
        $weeklyMinDays = (int) Setting::get('weekly_discount_min_days', 7);
        $weeklyPercent = (int) Setting::get('weekly_discount_percent', 15);

        $discountAmount = 0;
        $appliedDiscountPercent = 0;

        if ($days >= $weeklyMinDays && $weeklyPercent > 0) {
            $appliedDiscountPercent = $weeklyPercent;
            $discountAmount = (int) round($rawSubtotal * ($weeklyPercent / 100));
        }

        $finalRentalSubtotal = max(0, $rawSubtotal - $discountAmount);
        $grandTotal = $finalRentalSubtotal + $depositTotal;

        return [
            'duration_hours' => $hours,
            'duration_days' => $days,
            'subtotal_rental_raw' => $rawSubtotal,
            'discount_amount' => $discountAmount,
            'discount_percent' => $appliedDiscountPercent,
            'subtotal_rental' => $finalRentalSubtotal,
            'deposit_total' => $depositTotal,
            'grand_total' => $grandTotal,
            'items' => $processedItems,
        ];
    }
}
