<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class F0FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_helpers_format_rupiah_and_date(): void
    {
        $this->assertEquals('Rp 150.000', format_rupiah(150000));
        $this->assertEquals('Rp 0', format_rupiah(0));
        $this->assertNotEmpty(format_date_id(now()));
    }

    public function test_settings_get_and_set_with_cache(): void
    {
        Setting::set('test_key', 'test_value');
        $this->assertEquals('test_value', Setting::get('test_key'));

        Setting::set('test_json', ['a' => 1, 'b' => 'dua']);
        $this->assertEquals(['a' => 1, 'b' => 'dua'], Setting::get('test_json'));
    }

    public function test_models_and_enums_work_properly(): void
    {
        $category = Category::factory()->create(['name' => 'Audio']);
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'name' => 'Mic Shure SM58',
            'status' => ItemStatus::ACTIVE,
            'price_per_day' => 100000,
            'deposit' => 50000,
            'stock' => 2,
        ]);

        $this->assertEquals(ItemStatus::ACTIVE, $item->status);
        $this->assertEquals('Audio', $item->category->name);
        $this->assertNotEmpty($item->primary_image_url);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Anda');
    }

    public function test_user_can_login_with_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '081299998888',
            'password' => bcrypt('password123'),
            'role' => UserRole::CUSTOMER,
        ]);

        $response = $this->post('/login', [
            'identifier' => '081299998888',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('my-bookings.index'));
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@example.com',
            'phone' => '081277776666',
            'password' => bcrypt('secret123'),
            'role' => UserRole::STAFF,
        ]);

        $response = $this->post('/login', [
            'identifier' => 'staff@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Doni Siregar',
            'phone' => '081311112222',
            'email' => 'doni@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('my-bookings.index'));
        $this->assertDatabaseHas('users', [
            'phone' => '081311112222',
            'name' => 'Doni Siregar',
            'role' => 'customer',
        ]);
        $this->assertAuthenticated();
    }

    public function test_role_middleware_blocks_unauthorized_access(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get('/admin/dashboard');
        $response->assertStatus(403);

        $admin = User::factory()->admin()->create();
        $adminResponse = $this->actingAs($admin)->get('/admin/dashboard');
        $adminResponse->assertStatus(200);
    }
}
