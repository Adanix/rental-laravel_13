<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Enums\UserRole;
use App\Models\Blackout;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class F1CatalogAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_page_renders_successfully(): void
    {
        $category = Category::factory()->create(['name' => 'Kamera', 'slug' => 'kamera']);
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sony A7 IV',
            'status' => ItemStatus::ACTIVE,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Sony A7 IV');
        $response->assertSee('Kamera');
    }

    public function test_public_catalog_can_filter_by_category_and_search(): void
    {
        $cat1 = Category::factory()->create(['name' => 'Audio', 'slug' => 'audio']);
        $cat2 = Category::factory()->create(['name' => 'Camping', 'slug' => 'camping']);

        Item::factory()->create(['category_id' => $cat1->id, 'name' => 'Mic Hollyland', 'status' => ItemStatus::ACTIVE]);
        Item::factory()->create(['category_id' => $cat2->id, 'name' => 'Tenda Dome', 'status' => ItemStatus::ACTIVE]);

        // Search
        $resSearch = $this->get('/?q=Hollyland');
        $resSearch->assertStatus(200);
        $resSearch->assertSee('Mic Hollyland');
        $resSearch->assertDontSee('Tenda Dome');

        // Category filter
        $resCat = $this->get('/?category=camping');
        $resCat->assertStatus(200);
        $resCat->assertSee('Tenda Dome');
        $resCat->assertDontSee('Mic Hollyland');
    }

    public function test_public_catalog_filters_by_availability_date_range(): void
    {
        $item = Item::factory()->create(['stock' => 1, 'status' => ItemStatus::ACTIVE, 'name' => 'Drone DJI']);

        // Add blackout for Drone next week
        Blackout::factory()->create([
            'item_id' => $item->id,
            'start_at' => Carbon::parse('2026-11-10 08:00:00'),
            'end_at' => Carbon::parse('2026-11-12 18:00:00'),
        ]);

        // Search during blackout
        $response = $this->get('/?start_at=2026-11-10T10:00&end_at=2026-11-12T10:00');
        $response->assertStatus(200);
        $response->assertDontSee('Drone DJI');

        // Search after blackout
        $response2 = $this->get('/?start_at=2026-11-15T10:00&end_at=2026-11-17T10:00');
        $response2->assertStatus(200);
        $response2->assertSee('Drone DJI');
    }

    public function test_item_detail_page_renders_successfully(): void
    {
        $category = Category::factory()->create(['name' => 'Audio']);
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'name' => 'JBL Eon One',
            'slug' => 'jbl-eon-one',
            'description' => 'Speaker portable mantap',
            'specs' => ['Baterai' => '12 Jam', 'Output' => '112 dB'],
            'status' => ItemStatus::ACTIVE,
        ]);

        $response = $this->get('/items/jbl-eon-one');
        $response->assertStatus(200);
        $response->assertSee('JBL Eon One');
        $response->assertSee('Speaker portable mantap');
        $response->assertSee('12 Jam');
    }

    public function test_check_availability_endpoint_returns_json(): void
    {
        $item = Item::factory()->create(['stock' => 2, 'status' => ItemStatus::ACTIVE]);

        $response = $this->postJson("/items/{$item->id}/check-availability", [
            'start_at' => Carbon::now()->addDay()->toIso8601String(),
            'end_at' => Carbon::now()->addDays(2)->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'item_id' => $item->id,
            'stock_available' => 2,
            'is_available' => true,
        ]);
    }

    public function test_staff_can_view_and_manage_categories(): void
    {
        $staff = User::factory()->staff()->create();

        // Create Category
        $response = $this->actingAs($staff)->post('/admin/categories', [
            'name' => 'Lighting Studio',
            'is_active' => 1,
        ]);
        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Lighting Studio']);

        $category = Category::where('name', 'Lighting Studio')->first();

        // Update Category
        $updateRes = $this->actingAs($staff)->put("/admin/categories/{$category->id}", [
            'name' => 'Lighting & Studio Pro',
            'is_active' => 1,
        ]);
        $updateRes->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Lighting & Studio Pro']);

        // Delete Category
        $delRes = $this->actingAs($staff)->delete("/admin/categories/{$category->id}");
        $delRes->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_staff_can_create_and_update_items(): void
    {
        Storage::fake('public');
        $staff = User::factory()->staff()->create();
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->create('camera.jpg', 100, 'image/jpeg');

        // Store item
        $response = $this->actingAs($staff)->post('/admin/items', [
            'category_id' => $category->id,
            'name' => 'Panasonic Lumix S5 II',
            'description' => 'Kamera fullframe video handal',
            'price_per_day' => 250000,
            'deposit' => 200000,
            'stock' => 2,
            'status' => 'active',
            'specs_keys' => ['Sensor', 'Mount'],
            'specs_values' => ['24MP', 'L-mount'],
            'images' => [$image],
        ]);

        $response->assertRedirect('/admin/items');
        $this->assertDatabaseHas('items', [
            'name' => 'Panasonic Lumix S5 II',
            'price_per_day' => 250000,
        ]);

        $item = Item::where('name', 'Panasonic Lumix S5 II')->first();
        $this->assertCount(1, $item->images);
        $this->assertEquals(['Sensor' => '24MP', 'Mount' => 'L-mount'], $item->specs);

        // Update item
        $updateRes = $this->actingAs($staff)->put("/admin/items/{$item->id}", [
            'category_id' => $category->id,
            'name' => 'Panasonic Lumix S5 II Updated',
            'price_per_day' => 280000,
            'deposit' => 200000,
            'stock' => 3,
            'status' => 'active',
        ]);
        $updateRes->assertRedirect('/admin/items');
        $this->assertDatabaseHas('items', [
            'name' => 'Panasonic Lumix S5 II Updated',
            'price_per_day' => 280000,
            'stock' => 3,
        ]);
    }

    public function test_staff_can_manage_blackouts(): void
    {
        $staff = User::factory()->staff()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($staff)->post('/admin/blackouts', [
            'item_id' => $item->id,
            'start_at' => Carbon::now()->addDays(5)->toDateTimeString(),
            'end_at' => Carbon::now()->addDays(7)->toDateTimeString(),
            'reason' => 'Servis shutter count',
        ]);

        $response->assertRedirect('/admin/blackouts');
        $this->assertDatabaseHas('blackouts', [
            'item_id' => $item->id,
            'reason' => 'Servis shutter count',
        ]);

        $blackout = Blackout::where('item_id', $item->id)->first();
        $delRes = $this->actingAs($staff)->delete("/admin/blackouts/{$blackout->id}");
        $delRes->assertRedirect('/admin/blackouts');
        $this->assertDatabaseMissing('blackouts', ['id' => $blackout->id]);
    }
}
