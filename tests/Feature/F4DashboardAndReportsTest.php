<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class F4DashboardAndReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $customer;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

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
            'name' => 'Audio & Mic',
            'slug' => 'audio-mic',
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'category_id' => $category->id,
            'name' => 'Rode Wireless Pro Mic',
            'slug' => 'rode-wireless-pro-mic',
            'price_per_day' => 150000,
            'deposit' => 300000,
            'stock' => 5,
            'specs' => ['channel' => '2 Channel 32-bit float'],
            'status' => ItemStatus::ACTIVE,
        ]);
    }

    public function test_dashboard_renders_metrics_for_staff_and_admin(): void
    {
        // 1. Pickup today
        Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::CONFIRMED,
            'start_at' => today()->setHour(10),
            'end_at' => today()->addDays(2),
        ]);

        // 2. Return today
        Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::PICKED_UP,
            'start_at' => today()->subDays(2),
            'end_at' => today()->setHour(17),
        ]);

        $response = $this->actingAs($this->staff)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Operasional');
        $response->assertSee('Jadwal Pickup');
        $response->assertSee('Jadwal Return');

        // Customer blocked
        $custResponse = $this->actingAs($this->customer)->get('/admin/dashboard');
        $custResponse->assertStatus(403);
    }

    public function test_calendar_matrix_renders_for_staff(): void
    {
        $response = $this->actingAs($this->staff)->get('/admin/calendar');
        $response->assertStatus(200);
        $response->assertSee('Kalender Ketersediaan &amp; Jadwal', false);
        $response->assertSee($this->item->name);
    }

    public function test_admin_can_view_reports_and_metrics(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::RETURNED,
            'start_at' => today()->subDays(3),
            'end_at' => today()->subDay(),
            'subtotal_rental' => 300000,
            'discount_amount' => 0,
            'late_fee' => 50000,
            'damage_fee' => 0,
            'grand_total' => 600000,
        ]);

        $booking->items()->create([
            'item_id' => $this->item->id,
            'qty' => 1,
            'unit_price' => 150000,
            'unit_deposit' => 300000,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Analisis Pendapatan');
        $response->assertSee('Peringkat Unit Terlaris');

        // Staff is forbidden from admin-only reports
        $staffResponse = $this->actingAs($this->staff)->get('/admin/reports');
        $staffResponse->assertStatus(403);
    }

    public function test_admin_can_export_csv_report(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'status' => BookingStatus::RETURNED,
            'start_at' => today()->subDays(2),
            'end_at' => today(),
            'subtotal_rental' => 300000,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reports/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Kode Pesanan', $response->streamedContent());
        $this->assertStringContainsString($booking->code, $response->streamedContent());
    }

    public function test_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/settings', [
            'store_name' => 'RentalPro Maju Jaya',
            'store_phone' => '089988776655',
            'store_address' => 'Jl. Pahlawan No. 100, Surabaya',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1410012345678',
            'bank_account_holder' => 'PT RentalPro Nusantara',
            'expires_hours' => 3,
            'late_fee_per_day_percent' => 120,
            'cancellation_refund_percent' => 75,
            'weekly_discount_percent' => 20,
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('RentalPro Maju Jaya', Setting::get('store_name'));
        $this->assertEquals(3, (int) Setting::get('expires_hours'));
        $this->assertEquals(120, (int) Setting::get('late_fee_per_day_percent'));
    }

    public function test_admin_can_manage_staff_accounts(): void
    {
        // 1. Create staff
        $createResponse = $this->actingAs($this->admin)->post('/admin/staff', [
            'name' => 'Staff Baru Operasional',
            'phone' => '085511223344',
            'email' => 'staffbaru@rentalhub.id',
            'password' => 'secret123',
            'role' => 'staff',
        ]);

        $createResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['phone' => '085511223344', 'role' => 'staff']);

        $newStaff = User::where('phone', '085511223344')->first();

        // 2. Update staff
        $updateResponse = $this->actingAs($this->admin)->put("/admin/staff/{$newStaff->id}", [
            'name' => 'Staff Diperbarui',
            'phone' => '085511223344',
            'email' => 'staffbaru@rentalhub.id',
            'role' => 'staff',
        ]);

        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $newStaff->id, 'name' => 'Staff Diperbarui']);

        // 3. Admin cannot delete self
        $deleteSelfResponse = $this->actingAs($this->admin)->delete("/admin/staff/{$this->admin->id}");
        $deleteSelfResponse->assertSessionHas('error');

        // 4. Admin deletes new staff
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/staff/{$newStaff->id}");
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $newStaff->id]);
    }
}
