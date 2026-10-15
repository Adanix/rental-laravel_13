<?php

namespace Database\Factories;

use App\Models\Blackout;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlackoutFactory extends Factory
{
    protected $model = Blackout::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end = (clone $start)->modify('+3 days');

        return [
            'item_id' => Item::factory(),
            'start_at' => $start,
            'end_at' => $end,
            'reason' => 'Perawatan rutin berkala',
        ];
    }
}
