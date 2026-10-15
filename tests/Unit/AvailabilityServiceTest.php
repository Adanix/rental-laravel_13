<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentStatus;
use App\Models\Blackout;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Item;
use App\Models\Payment;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailabilityService();
    }

    public function test_available_stock_returns_full_stock_when_no_bookings(): void
    {
        $item = Item::factory()->create(['stock' => 5, 'status' => ItemStatus::ACTIVE]);
        $start = Carbon::parse('2026-11-01 10:00:00');
        $end = Carbon::parse('2026-11-03 10:00:00');

        $available = $this->service->getAvailableStock($item, $start, $end);
        $this->assertEquals(5, $available);
        $this->assertTrue($this->service->isAvailable($item, $start, $end, 5));
        $this->assertFalse($this->service->isAvailable($item, $start, $end, 6));
    }

    public function test_inactive_item_returns_zero_availability(): void
    {
        $item = Item::factory()->create(['stock' => 5, 'status' => ItemStatus::MAINTENANCE]);
        $start = Carbon::parse('2026-11-01 10:00:00');
        $end = Carbon::parse('2026-11-03 10:00:00');

        $this->assertEquals(0, $this->service->getAvailableStock($item, $start, $end));
    }

    public function test_blackout_returns_zero_availability(): void
    {
        $item = Item::factory()->create(['stock' => 5, 'status' => ItemStatus::ACTIVE]);
        Blackout::factory()->create([
            'item_id' => $item->id,
            'start_at' => Carbon::parse('2026-11-02 00:00:00'),
            'end_at' => Carbon::parse('2026-11-04 00:00:00'),
        ]);

        $start = Carbon::parse('2026-11-01 10:00:00');
        $end = Carbon::parse('2026-11-03 10:00:00');

        $this->assertEquals(0, $this->service->getAvailableStock($item, $start, $end));
    }

    public function test_sweep_line_distinguishes_sequential_bookings_from_concurrent(): void
    {
        // Stock = 2.
        // Booking A: Mon 08:00 - Tue 17:00 (qty 1)
        // Booking B: Thu 08:00 - Fri 17:00 (qty 1)
        // Customer searches: Mon 08:00 - Fri 17:00.
        // Naive sum = 2 bookings * 1 = 2 -> 2 - 2 = 0 available (WRONG).
        // Sweep-line: Peak concurrent qty = 1 -> 2 - 1 = 1 available (CORRECT).
        $user = User::factory()->customer()->create();
        $item = Item::factory()->create(['stock' => 2, 'status' => ItemStatus::ACTIVE]);

        // Booking A
        $bA = Booking::factory()->create([
            'user_id' => $user->id,
            'start_at' => Carbon::parse('2026-11-02 08:00:00'), // Mon
            'end_at' => Carbon::parse('2026-11-03 17:00:00'),   // Tue
            'status' => BookingStatus::CONFIRMED,
        ]);
        BookingItem::factory()->create(['booking_id' => $bA->id, 'item_id' => $item->id, 'qty' => 1]);

        // Booking B
        $bB = Booking::factory()->create([
            'user_id' => $user->id,
            'start_at' => Carbon::parse('2026-11-05 08:00:00'), // Thu
            'end_at' => Carbon::parse('2026-11-06 17:00:00'),   // Fri
            'status' => BookingStatus::CONFIRMED,
        ]);
        BookingItem::factory()->create(['booking_id' => $bB->id, 'item_id' => $item->id, 'qty' => 1]);

        // Search Mon to Fri
        $reqStart = Carbon::parse('2026-11-02 08:00:00');
        $reqEnd = Carbon::parse('2026-11-06 17:00:00');

        $availableStock = $this->service->getAvailableStock($item, $reqStart, $reqEnd);
        $this->assertEquals(1, $availableStock);
        $this->assertTrue($this->service->isAvailable($item, $reqStart, $reqEnd, 1));
        $this->assertFalse($this->service->isAvailable($item, $reqStart, $reqEnd, 2));
    }

    public function test_concurrent_bookings_properly_deplete_stock(): void
    {
        // Stock = 3.
        // Booking A: Tue 10:00 - Thu 10:00 (qty 2)
        // Booking B: Wed 10:00 - Fri 10:00 (qty 1)
        // On Wednesday, peak concurrent qty is 2 + 1 = 3!
        // Searching Tue to Fri on stock=3 should leave 0 available.
        $user = User::factory()->customer()->create();
        $item = Item::factory()->create(['stock' => 3, 'status' => ItemStatus::ACTIVE]);

        $bA = Booking::factory()->create([
            'user_id' => $user->id,
            'start_at' => Carbon::parse('2026-11-03 10:00:00'),
            'end_at' => Carbon::parse('2026-11-05 10:00:00'),
            'status' => BookingStatus::CONFIRMED,
        ]);
        BookingItem::factory()->create(['booking_id' => $bA->id, 'item_id' => $item->id, 'qty' => 2]);

        $bB = Booking::factory()->create([
            'user_id' => $user->id,
            'start_at' => Carbon::parse('2026-11-04 10:00:00'),
            'end_at' => Carbon::parse('2026-11-06 10:00:00'),
            'status' => BookingStatus::PICKED_UP,
        ]);
        BookingItem::factory()->create(['booking_id' => $bB->id, 'item_id' => $item->id, 'qty' => 1]);

        $reqStart = Carbon::parse('2026-11-03 08:00:00');
        $reqEnd = Carbon::parse('2026-11-06 18:00:00');

        $this->assertEquals(0, $this->service->getAvailableStock($item, $reqStart, $reqEnd));
    }

    public function test_expired_pending_booking_does_not_block_stock(): void
    {
        $user = User::factory()->customer()->create();
        $item = Item::factory()->create(['stock' => 1, 'status' => ItemStatus::ACTIVE]);

        // Pending booking expired 1 hour ago with no payment
        $b = Booking::factory()->create([
            'user_id' => $user->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'status' => BookingStatus::PENDING,
            'expires_at' => Carbon::now()->subHour(),
        ]);
        BookingItem::factory()->create(['booking_id' => $b->id, 'item_id' => $item->id, 'qty' => 1]);

        $start = Carbon::now()->addDays(2);
        $end = Carbon::now()->addDays(4);

        $this->assertEquals(1, $this->service->getAvailableStock($item, $start, $end));
    }

    public function test_pending_booking_with_pending_payment_proof_blocks_stock(): void
    {
        $user = User::factory()->customer()->create();
        $item = Item::factory()->create(['stock' => 1, 'status' => ItemStatus::ACTIVE]);

        // Pending booking with expired time but has uploaded payment proof
        $b = Booking::factory()->create([
            'user_id' => $user->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'status' => BookingStatus::PENDING,
            'expires_at' => Carbon::now()->subHour(),
        ]);
        BookingItem::factory()->create(['booking_id' => $b->id, 'item_id' => $item->id, 'qty' => 1]);

        Payment::factory()->create([
            'booking_id' => $b->id,
            'status' => PaymentStatus::PENDING,
        ]);

        $start = Carbon::now()->addDays(2);
        $end = Carbon::now()->addDays(4);

        $this->assertEquals(0, $this->service->getAvailableStock($item, $start, $end));
    }
}
