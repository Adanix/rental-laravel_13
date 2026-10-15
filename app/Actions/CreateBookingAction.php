<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingStatusLog;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBookingAction
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected PricingService $pricingService
    ) {}

    /**
     * Create a new booking atomically with pessimistic locking and sweep-line availability verification.
     *
     * @param User $customer
     * @param array $itemsWithQty Array of ['item_id' => int, 'qty' => int]
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @param User|null $actor
     * @param string|null $notes
     * @return Booking
     * @throws DomainException
     */
    public function execute(
        User $customer,
        array $itemsWithQty,
        Carbon $startAt,
        Carbon $endAt,
        ?User $actor = null,
        ?string $notes = null
    ): Booking {
        if ($endAt->lte($startAt)) {
            throw new DomainException('Waktu selesai sewa harus setelah waktu mulai sewa.');
        }

        if (empty($itemsWithQty)) {
            throw new DomainException('Paling sedikit satu unit barang harus dipilih untuk dibooking.');
        }

        return DB::transaction(function () use ($customer, $itemsWithQty, $startAt, $endAt, $actor, $notes) {
            // 1. Extract IDs sorted ascending to avoid deadlock
            $itemIds = collect($itemsWithQty)->pluck('item_id')->unique()->sort()->values()->all();

            // 2. Pessimistic Lock on all involved items (order by ID)
            $lockedItems = Item::whereIn('id', $itemIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 3. Verify availability & build items array
            $itemsForPricing = [];
            foreach ($itemsWithQty as $entry) {
                $itemId = $entry['item_id'];
                $qty = (int) ($entry['qty'] ?? 1);

                if (! isset($lockedItems[$itemId])) {
                    throw new DomainException("Unit dengan ID {$itemId} tidak ditemukan.");
                }

                $item = $lockedItems[$itemId];

                $availableStock = $this->availabilityService->getAvailableStock($item, $startAt, $endAt);

                if ($availableStock < $qty) {
                    throw new DomainException(
                        "Maaf, unit '{$item->name}' tidak tersedia untuk jadwal ini (tersedia: {$availableStock}, diminta: {$qty})."
                    );
                }

                $itemsForPricing[] = [
                    'item' => $item,
                    'item_id' => $item->id,
                    'qty' => $qty,
                ];
            }

            // 4. Calculate prices, duration, and discounts
            $pricing = $this->pricingService->calculate($itemsForPricing, $startAt, $endAt);

            // 5. Generate unique code
            $datePart = now()->format('Ymd');
            do {
                $code = 'RNT-' . $datePart . '-' . strtoupper(Str::random(4));
            } while (Booking::where('code', $code)->exists());

            // 6. Payment expiry
            $expiryHours = (int) Setting::get('payment_expiry_hours', 2);
            $expiresAt = now()->addHours($expiryHours);

            // 7. Insert Booking
            $booking = Booking::create([
                'code' => $code,
                'user_id' => $customer->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => BookingStatus::PENDING,
                'subtotal_rental' => $pricing['subtotal_rental'],
                'deposit_total' => $pricing['deposit_total'],
                'discount_amount' => $pricing['discount_amount'],
                'late_fee' => 0,
                'damage_fee' => 0,
                'grand_total' => $pricing['grand_total'],
                'expires_at' => $expiresAt,
            ]);

            // 8. Insert BookingItems with snapshots
            foreach ($pricing['items'] as $itemData) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'item_id' => $itemData['item_id'],
                    'qty' => $itemData['qty'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_deposit' => $itemData['unit_deposit'],
                    'subtotal' => $itemData['subtotal'],
                ]);
            }

            // 9. Initial status log
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'from_status' => null,
                'to_status' => BookingStatus::PENDING,
                'actor_id' => $actor?->id ?? $customer->id,
                'reason' => $notes ?: 'Pesanan booking baru dibuat',
                'created_at' => now(),
            ]);

            return $booking;
        });
    }
}
