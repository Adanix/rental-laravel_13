<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Item;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Calculate available stock for an Item in [req_start, req_end) using sweep-line algorithm.
     */
    public function getAvailableStock(Item $item, Carbon $startAt, Carbon $endAt, ?int $ignoreBookingId = null): int
    {
        // 1. If item is not active, availability is 0
        if ($item->status !== ItemStatus::ACTIVE) {
            return 0;
        }

        // 2. Check Blackouts: overlaps if start_at < req_end AND end_at > req_start
        $hasBlackout = $item->blackouts()
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

        if ($hasBlackout) {
            return 0;
        }

        // 3. Query active overlapping bookings:
        // Confirmed, picked_up, or pending (expires_at > now OR has pending payment proof)
        $now = now();
        $overlappingBookings = Booking::query()
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->when($ignoreBookingId, fn($q) => $q->where('id', '!=', $ignoreBookingId))
            ->where(function ($q) use ($now) {
                $q->whereIn('status', [BookingStatus::CONFIRMED->value, BookingStatus::PICKED_UP->value])
                  ->orWhere(function ($sub) use ($now) {
                      $sub->where('status', BookingStatus::PENDING->value)
                          ->where(function ($pendingSub) use ($now) {
                              $pendingSub->where('expires_at', '>', $now)
                                         ->orWhereHas('payments', function ($paySub) {
                                             $paySub->where('status', PaymentStatus::PENDING->value);
                                         });
                          });
                  });
            })
            ->with(['items' => function ($q) use ($item) {
                $q->where('item_id', $item->id);
            }])
            ->get();

        if ($overlappingBookings->isEmpty()) {
            return (int) $item->stock;
        }

        // 4. Sweep-line algorithm over interval event points
        $events = [];
        $reqStartTs = $startAt->timestamp;
        $reqEndTs = $endAt->timestamp;

        foreach ($overlappingBookings as $booking) {
            $qty = (int) $booking->items->sum('qty');
            if ($qty <= 0) {
                continue;
            }

            $bStartTs = $booking->start_at->timestamp;
            $bEndTs = $booking->end_at->timestamp;

            // Clip to requested range
            $eventStart = max($bStartTs, $reqStartTs);
            $eventEnd = min($bEndTs, $reqEndTs);

            if ($eventEnd <= $eventStart) {
                continue;
            }

            // +qty at start of interval
            $events[] = [
                'time' => $eventStart,
                'type' => 1, // Start consumes stock
                'qty' => $qty,
            ];

            // -qty at end of interval
            $events[] = [
                'time' => $eventEnd,
                'type' => -1, // End releases stock
                'qty' => $qty,
            ];
        }

        if (empty($events)) {
            return (int) $item->stock;
        }

        // Sort events: primary by time ascending.
        // If times are identical, type -1 (releases stock) comes before +1 (consumes stock)
        // for half-open intervals [start, end).
        usort($events, function ($a, $b) {
            if ($a['time'] === $b['time']) {
                return $a['type'] <=> $b['type']; // -1 before 1
            }
            return $a['time'] <=> $b['time'];
        });

        $currentConcurrentQty = 0;
        $peakConcurrentQty = 0;

        foreach ($events as $event) {
            $currentConcurrentQty += ($event['type'] * $event['qty']);
            if ($currentConcurrentQty > $peakConcurrentQty) {
                $peakConcurrentQty = $currentConcurrentQty;
            }
        }

        return (int) max(0, $item->stock - $peakConcurrentQty);
    }

    /**
     * Check if a specific quantity is available for an item.
     */
    public function isAvailable(Item $item, Carbon $startAt, Carbon $endAt, int $requestedQty = 1, ?int $ignoreBookingId = null): bool
    {
        if ($requestedQty <= 0) {
            return false;
        }

        return $this->getAvailableStock($item, $startAt, $endAt, $ignoreBookingId) >= $requestedQty;
    }

    /**
     * Batch check multiple items for availability.
     */
    public function checkItemsAvailability(array $itemsWithQty, Carbon $startAt, Carbon $endAt, ?int $ignoreBookingId = null): array
    {
        $results = [];

        foreach ($itemsWithQty as $entry) {
            $item = $entry['item'] instanceof Item ? $entry['item'] : Item::findOrFail($entry['item_id']);
            $qty = (int) ($entry['qty'] ?? 1);
            $stockAvailable = $this->getAvailableStock($item, $startAt, $endAt, $ignoreBookingId);

            $results[$item->id] = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'requested_qty' => $qty,
                'stock_available' => $stockAvailable,
                'is_available' => $stockAvailable >= $qty,
            ];
        }

        return $results;
    }
}
