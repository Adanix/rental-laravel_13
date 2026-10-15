<?php

namespace Tests\Unit;

use App\Models\Item;
use App\Models\Setting;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService();

        Setting::set('weekly_discount_min_days', 7);
        Setting::set('weekly_discount_percent', 15);
    }

    public function test_duration_days_calculation(): void
    {
        $start = Carbon::parse('2026-11-01 10:00:00');

        // 2 hours -> 1 day
        $this->assertEquals(1, $this->service->calculateDays($start, Carbon::parse('2026-11-01 12:00:00')));

        // 24 hours -> 1 day
        $this->assertEquals(1, $this->service->calculateDays($start, Carbon::parse('2026-11-02 10:00:00')));

        // 25 hours -> 2 days
        $this->assertEquals(2, $this->service->calculateDays($start, Carbon::parse('2026-11-02 11:00:00')));

        // 48 hours -> 2 days
        $this->assertEquals(2, $this->service->calculateDays($start, Carbon::parse('2026-11-03 10:00:00')));
    }

    public function test_pricing_calculation_standard(): void
    {
        $item = Item::factory()->create([
            'price_per_day' => 100000,
            'deposit' => 50000,
        ]);

        $start = Carbon::parse('2026-11-01 10:00:00');
        $end = Carbon::parse('2026-11-03 10:00:00'); // 2 days

        $result = $this->service->calculate([
            ['item' => $item, 'qty' => 2],
        ], $start, $end);

        $this->assertEquals(2, $result['duration_days']);
        $this->assertEquals(400000, $result['subtotal_rental']); // 2 days * 100k * 2 qty
        $this->assertEquals(100000, $result['deposit_total']);   // 50k * 2 qty
        $this->assertEquals(0, $result['discount_amount']);
        $this->assertEquals(500000, $result['grand_total']);
    }

    public function test_long_rental_weekly_discount_applied(): void
    {
        $item = Item::factory()->create([
            'price_per_day' => 100000,
            'deposit' => 50000,
        ]);

        $start = Carbon::parse('2026-11-01 10:00:00');
        $end = Carbon::parse('2026-11-08 10:00:00'); // 7 days!

        $result = $this->service->calculate([
            ['item' => $item, 'qty' => 1],
        ], $start, $end);

        $this->assertEquals(7, $result['duration_days']);
        $this->assertEquals(700000, $result['subtotal_rental_raw']); // 7 * 100k
        // 15% of 700k = 105,000
        $this->assertEquals(105000, $result['discount_amount']);
        $this->assertEquals(595000, $result['subtotal_rental']); // 700k - 105k
        $this->assertEquals(50000, $result['deposit_total']);
        $this->assertEquals(645000, $result['grand_total']);
    }
}
