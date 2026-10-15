<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\BookingStatusChangedNotification;
use App\Notifications\RentalReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class F3OperationalAndRentalCycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $customer;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Notification::fake();

        Setting::set('expires_hours', 2);
        Setting::set('late_fee_per_day_percent', 100);
        Setting::set('cancellation_refund_percent', 80);

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'phone' => '081200000001',
        ]);

        $this->staff = User::factory()->create([
            'role' => UserRole::STAFF,
            'phone' => '081200000002',
        ]);

        $this->customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
            'phone' => '081200000003',
        ]);

        $category = Category::create([
            'name' => 'Kamera & Lensa',
            'slug' => 'kamera-lensa',
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'category_id' => $category->id,
            'name' => 'Sony FX3 Cinema Camera',
            'slug' => 'sony-fx3-cinema-camera',
            'price_per_day' => 500000,
            'deposit' => 1000000,
            'stock' => 3,
            'specs' => ['sensor' => 'Full Frame 4K'],
            'status' => \App\Enums\ItemStatus::ACTIVE,
        ]);
    }

    public function test_customer_can_upload_payment_proof(): void
    {
        $start = now()->addDay()->setHour(9)->setMinute(0);
        $end = now()->addDays(2)->setHour(9)->setMinute(0);

        // Create booking
        $this->actingAs($this->customer)->post('/bookings', [
            'item_id' => $this->item->id,
            'qty' => 1,
            'start_at' => $start->format('Y-m-d\TH:i'),
            'end_at' => $end->format('Y-m-d\TH:i'),
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertEquals(BookingStatus::PENDING, $booking->status);

        $file = UploadedFile::fake()->create('struk-transfer.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->customer)->post("/my-bookings/{$booking->code}/payment", [
            'method' => 'transfer',
            'proof' => $file,
            'notes' => 'Transfer via BCA KlikBCA',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => PaymentType::RENTAL_AND_DEPOSIT->value,
            'method' => PaymentMethod::TRANSFER->value,
            'amount' => $booking->grand_total,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_staff_can_view_bookings_list_and_details(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PENDING,
        ]);

        $response = $this->actingAs($this->staff)->get('/admin/bookings');
        $response->assertStatus(200);
        $response->assertSee($booking->code);

        $detailResponse = $this->actingAs($this->staff)->get("/admin/bookings/{$booking->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($booking->code);
    }

    public function test_staff_verifies_payment_and_booking_becomes_confirmed(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PENDING,
            'grand_total' => 1500000,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => PaymentType::RENTAL_AND_DEPOSIT,
            'method' => PaymentMethod::TRANSFER,
            'amount' => 1500000,
            'status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->staff)->post("/admin/bookings/{$booking->id}/payments/{$payment->id}/verify", [
            'notes' => 'Dana masuk rekening BCA',
        ]);

        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PaymentStatus::VERIFIED, $payment->status);
        $this->assertEquals($this->staff->id, $payment->verified_by);

        $booking->refresh();
        $this->assertEquals(BookingStatus::CONFIRMED, $booking->status);

        Notification::assertSentTo($this->customer, BookingStatusChangedNotification::class);
    }

    public function test_staff_rejects_payment_and_booking_becomes_rejected(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PENDING,
            'grand_total' => 1500000,
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'type' => PaymentType::RENTAL_AND_DEPOSIT,
            'method' => PaymentMethod::TRANSFER,
            'amount' => 1500000,
            'status' => PaymentStatus::PENDING,
        ]);

        $response = $this->actingAs($this->staff)->post("/admin/bookings/{$booking->id}/payments/{$payment->id}/reject", [
            'reason' => 'Mutasi rekening tidak ditemukan',
        ]);

        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PaymentStatus::REJECTED, $payment->status);

        $booking->refresh();
        $this->assertEquals(BookingStatus::REJECTED, $booking->status);
    }

    public function test_staff_performs_pickup_handover(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::CONFIRMED,
        ]);

        $response = $this->actingAs($this->staff)->post("/admin/bookings/{$booking->id}/pickup", [
            'condition_notes' => 'Unit lengkap, tas dan charger diserahkan.',
        ]);

        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals(BookingStatus::PICKED_UP, $booking->status);
        $this->assertNotNull($booking->picked_up_at);
        $this->assertArrayHasKey('pickup', $booking->handover_checklist);
    }

    public function test_staff_performs_return_with_deposit_refund(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PICKED_UP,
            'start_at' => now()->subDays(2),
            'end_at' => now()->addHours(2), // on time return
            'picked_up_at' => now()->subDays(2),
            'deposit_total' => 1000000,
        ]);

        $booking->items()->create([
            'item_id' => $this->item->id,
            'qty' => 1,
            'unit_price' => 500000,
            'unit_deposit' => 1000000,
            'subtotal' => 1000000,
        ]);

        $response = $this->actingAs($this->staff)->post("/admin/bookings/{$booking->id}/return", [
            'condition_notes' => 'Unit kembali dalam kondisi sangat baik.',
            'damage_fee' => 0,
        ]);

        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals(BookingStatus::RETURNED, $booking->status);
        $this->assertNotNull($booking->returned_at);
        $this->assertEquals(0, $booking->late_fee);
        $this->assertEquals(0, $booking->damage_fee);

        // Sisa deposit 1.000.000 dicatat sebagai refund pending
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => PaymentType::REFUND->value,
            'amount' => 1000000,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_staff_performs_return_with_late_fee_and_damage_fee_exceeding_deposit(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PICKED_UP,
            'start_at' => now()->subDays(4),
            'end_at' => now()->subDays(2), // 2 days late
            'picked_up_at' => now()->subDays(4),
            'deposit_total' => 500000,
        ]);

        $booking->items()->create([
            'item_id' => $this->item->id,
            'qty' => 1,
            'unit_price' => 500000, // 500k/day -> 2 days late = 1.000.000 late fee
            'unit_deposit' => 500000,
            'subtotal' => 1000000,
        ]);

        $response = $this->actingAs($this->staff)->post("/admin/bookings/{$booking->id}/return", [
            'condition_notes' => 'Tutup lensa hilang dan bodi tergores.',
            'damage_fee' => 200000,
        ]);

        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals(BookingStatus::RETURNED, $booking->status);
        $this->assertEquals(1000000, $booking->late_fee);
        $this->assertEquals(200000, $booking->damage_fee);

        // Total deduction = 1.000.000 + 200.000 = 1.200.000. Deposit = 500.000.
        // Kekurangan 700.000 dicatat sebagai additional_fee pending
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'type' => PaymentType::ADDITIONAL_FEE->value,
            'amount' => 700000,
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_walk_in_booking_creates_confirmed_or_picked_up_booking(): void
    {
        $start = now()->setHour(10)->setMinute(0);
        $end = now()->addDays(2)->setHour(10)->setMinute(0);

        $response = $this->actingAs($this->staff)->post('/admin/bookings/walk-in', [
            'customer_name' => 'Walk In Customer Test',
            'customer_phone' => '087788990011',
            'item_id' => $this->item->id,
            'qty' => 1,
            'start_at' => $start->format('Y-m-d\TH:i'),
            'end_at' => $end->format('Y-m-d\TH:i'),
            'payment_method' => 'cash',
            'direct_pickup' => 1,
            'notes' => 'Walk-in cash counter',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'phone' => '087788990011',
            'name' => 'Walk In Customer Test',
        ]);

        $booking = Booking::whereHas('user', fn ($q) => $q->where('phone', '087788990011'))->first();
        $this->assertNotNull($booking);
        $this->assertEquals(BookingStatus::PICKED_UP, $booking->status);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'method' => PaymentMethod::CASH->value,
            'status' => PaymentStatus::VERIFIED->value,
            'verified_by' => $this->staff->id,
        ]);
    }

    public function test_process_rental_schedules_command_expires_unpaid_bookings_and_notifies_overdue(): void
    {
        // 1. Expired candidate: pending, expired 1 hour ago, no payments
        $expiredBooking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PENDING,
            'expires_at' => now()->subHour(),
        ]);

        // 2. Overdue candidate: picked up, end_at passed
        $overdueBooking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PICKED_UP,
            'end_at' => now()->subDay(),
        ]);

        $this->artisan('rental:process-schedules')
            ->expectsOutputToContain("Pesanan {$expiredBooking->code} telah dialihkan ke EXPIRED.")
            ->assertSuccessful();

        $expiredBooking->refresh();
        $this->assertEquals(BookingStatus::EXPIRED, $expiredBooking->status);

        Notification::assertSentTo($this->customer, RentalReminderNotification::class);
    }
}
