<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingItemFactory extends Factory
{
    protected $model = BookingItem::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'item_id' => Item::factory(),
            'qty' => 1,
            'unit_price' => 100000,
            'unit_deposit' => 50000,
            'subtotal' => 100000,
        ];
    }
}
