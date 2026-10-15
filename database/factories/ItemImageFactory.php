<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemImageFactory extends Factory
{
    protected $model = ItemImage::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'path' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&auto=format&fit=crop&q=80',
            'sort_order' => 0,
            'is_primary' => true,
        ];
    }
}
