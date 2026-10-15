<?php

namespace Tests\Feature;

use App\Actions\CreateBookingAction;
use App\Actions\TransitionBookingStatusAction;
use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class F2BookingCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('payment_expiry_hours', 2);
        Setting::set('cancellation_refund_percent', 80);
    }

    public function test_customer_can_view_booking_form(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create(['status' => ItemStatus::ACTIVE]);

        $response = $this->actingAs($customer)->get("/bookings/create?item_id={$item->id}");
        $response->assertStatus(200);
        $response->assertSee($item->name);
        $response->assertSee('Formulir Pemesanan Rental');
    }

    public function test_customer_can_create_booking_successfully(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create([
            'status' => ItemStatus::ACTIVE,
            'price_per_day' => 150000,
            'deposit' => 100000,
            'stock' => 2,
        ]);

        $start = Carbon::now()->addDays(2)->setHour(10)->setMinute(0);
        $end = Carbon::now()->addDays(4)->setHour(10)->setMinute(0); // 2 days

        $response = $this->actingAs($customer)->post('/bookings', [
            'item_id' => $item->id,
            'qty' => 1,
            'start_at' => $start->format('Y-m-d\TH:i'),
            'end_at' => $end->format('Y-m-d\TH:i'),
            'notes' => 'Harap dicek sebelum serah terima',
        ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'status' => BookingStatus::PENDING->value,
            'subtotal_rental' => 300000,
            'deposit_total' => 100000,
            'grand_total' => 400000,
        ]);

        $booking = Booking::where('user_id', $customer->id)->first();
        $response->assertRedirect(route('my-bookings.show', $booking->code));

        $this->assertDatabaseHas('booking_items', [
            'booking_id' => $booking->id,
            'item_id' => $item->id,
            'qty' => 1,
            'unit_price' => 150000,
            'unit_deposit' => 100000,
        ]);

        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'to_status' => BookingStatus::PENDING->value,
        ]);
    }

    public function test_anti_double_booking_rejects_insufficient_stock(): void
    {
        $customerA = User::factory()->customer()->create();
        $customerB = User::factory()->customer()->create();

        $item = Item::factory()->create([
            'stock' => 1,
            'status' => ItemStatus::ACTIVE,
        ]);

        $start = Carbon::now()->addDays(2);
        $end = Carbon::now()->addDays(4);

        // Booking A takes the only unit (confirmed)
        $action = app(CreateBookingAction::class);
        $bookingA = $action->execute(
            customer: $customerA,
            itemsWithQty: [['item_id' => $item->id, 'qty' => 1]],
            startAt: $start,
            endAt: $end
        );

        $transitionAction = app(TransitionBookingStatusAction::class);
        $transitionAction->execute($bookingA, BookingStatus::CONFIRMED);

        // Customer B tries to book the same item on overlapping dates
        $response = $this->actingAs($customerB)->post('/bookings', [
            'item_id' => $item->id,
            'qty' => 1,
            'start_at' => $start->format('Y-m-d\TH:i'),
            'end_at' => $end->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Booking::count()); // No second booking created
    }

    public function test_transition_booking_status_action_validates_transitions(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create();

        $action = app(CreateBookingAction::class);
        $booking = $action->execute(
            customer: $customer,
            itemsWithQty: [['item_id' => $item->id, 'qty' => 1]],
            startAt: Carbon::now()->addDays(2),
            endAt: Carbon::now()->addDays(4)
        );

        $transition = app(TransitionBookingStatusAction::class);

        // Illegal: pending -> returned directly (must go confirmed -> picked_up -> returned)
        $this->expectException(DomainException::class);
        $transition->execute($booking, BookingStatus::RETURNED);
    }

    public function test_customer_can_cancel_pending_booking(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create(['stock' => 1]);

        $action = app(CreateBookingAction::class);
        $booking = $action->execute(
            customer: $customer,
            itemsWithQty: [['item_id' => $item->id, 'qty' => 1]],
            startAt: Carbon::now()->addDays(3),
            endAt: Carbon::now()->addDays(5)
        );

        $response = $this->actingAs($customer)->post("/my-bookings/{$booking->code}/cancel", [
            'reason' => 'Perubahan rencana liburan',
        ]);

        $response->assertRedirect(route('my-bookings.show', $booking->code));
        $this->assertEquals(BookingStatus::CANCELLED, $booking->fresh()->status);
        $this->assertDatabaseHas('booking_status_logs', [
            'booking_id' => $booking->id,
            'to_status' => BookingStatus::CANCELLED->value,
            'actor_id' => $customer->id,
        ]);
    }

    public function test_customer_cancelling_confirmed_booking_generates_refund(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create([
            'price_per_day' => 100000,
            'deposit' => 50000,
        ]);

        $start = Carbon::parse('2026-11-10 10:00:00');
        $end = Carbon::parse('2026-11-12 10:00:00'); // exactly 48 hours = 2 days

        $action = app(CreateBookingAction::class);
        $booking = $action->execute(
            customer: $customer,
            itemsWithQty: [['item_id' => $item->id, 'qty' => 1]],
            startAt: $start,
            endAt: $end
        );

        $transition = app(TransitionBookingStatusAction::class);
        $transition->execute($booking, BookingStatus::CONFIRMED);

        // Cancel confirmed booking before start_at
        $response = $this->actingAs($customer)->post("/my-bookings/{$booking->code}/cancel", [
            'reason' => 'Batal acara mendadak',
        ]);

        $response->assertRedirect(route('my-bookings.show', $booking->code));
        $this->assertEquals(BookingStatus::CANCELLED, $booking->fresh()->status);

        // 80% refund of 250,000 = 200,000
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => PaymentType::REFUND->value,
            'amount' => 200000,
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_cancel_booking_after_start_at(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create();

        // Booking that starts in the past
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'start_at' => Carbon::now()->subHours(2),
            'end_at' => Carbon::now()->addDays(2),
            'status' => BookingStatus::CONFIRMED,
        ]);

        $response = $this->actingAs($customer)->post("/my-bookings/{$booking->code}/cancel");
        $response->assertStatus(403);
    }

    public function test_customer_can_view_invoice_page(): void
    {
        $customer = User::factory()->customer()->create();
        $item = Item::factory()->create();

        $action = app(CreateBookingAction::class);
        $booking = $action->execute(
            customer: $customer,
            itemsWithQty: [['item_id' => $item->id, 'qty' => 1]],
            startAt: Carbon::now()->addDays(2),
            endAt: Carbon::now()->addDays(4)
        );

        $response = $this->actingAs($customer)->get("/my-bookings/{$booking->code}/invoice");
        $response->assertStatus(200);
        $response->assertSee('FAKTUR SEWA');
        $response->assertSee($booking->code);
    }
}
