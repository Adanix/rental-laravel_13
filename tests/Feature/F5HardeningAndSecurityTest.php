<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class F5HardeningAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer1;
    protected User $customer2;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer1 = User::factory()->create([
            'role' => UserRole::CUSTOMER,
            'phone' => '081111111111',
            'password' => bcrypt('password123'),
        ]);

        $this->customer2 = User::factory()->create([
            'role' => UserRole::CUSTOMER,
            'phone' => '082222222222',
        ]);

        $category = Category::create([
            'name' => 'Kamera',
            'slug' => 'kamera',
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'category_id' => $category->id,
            'name' => 'Hardening Test Item',
            'slug' => 'hardening-test-item',
            'price_per_day' => 100000,
            'deposit' => 200000,
            'stock' => 5,
            'status' => \App\Enums\ItemStatus::ACTIVE,
        ]);
    }

    public function test_login_rate_limiting_triggers_too_many_requests(): void
    {
        // 5 allowed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => '081111111111',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be rate limited (429)
        $response = $this->post('/login', [
            'login' => '081111111111',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    public function test_customer_cannot_view_or_cancel_another_customer_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer1->id,
            'status' => BookingStatus::PENDING,
        ]);

        // Customer 2 tries to view Customer 1's booking
        $viewResponse = $this->actingAs($this->customer2)->get("/my-bookings/{$booking->code}");
        $viewResponse->assertStatus(403);

        // Customer 2 tries to cancel Customer 1's booking
        $cancelResponse = $this->actingAs($this->customer2)->post("/my-bookings/{$booking->code}/cancel", [
            'reason' => 'Unauthorized cancellation',
        ]);
        $cancelResponse->assertStatus(403);

        // Customer 2 tries to view invoice
        $invoiceResponse = $this->actingAs($this->customer2)->get("/my-bookings/{$booking->code}/invoice");
        $invoiceResponse->assertStatus(403);
    }

    public function test_payment_proof_upload_validates_mime_and_size(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer1->id,
            'status' => BookingStatus::PENDING,
        ]);

        // Disallowed PHP file extension
        $maliciousFile = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');

        $response = $this->actingAs($this->customer1)->post("/my-bookings/{$booking->code}/payment", [
            'method' => 'transfer',
            'proof' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors(['proof']);
    }
}
