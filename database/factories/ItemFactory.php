<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraph(),
            'price_per_day' => fake()->randomElement([50000, 100000, 150000, 250000, 350000, 500000]),
            'deposit' => fake()->randomElement([50000, 100000, 200000, 300000]),
            'stock' => fake()->numberBetween(1, 5),
            'specs' => [
                'Brand' => fake()->word(),
                'Kondisi' => 'Sangat Baik',
                'Kelengkapan' => 'Unit + Tas + Aksesori Standar',
            ],
            'status' => ItemStatus::ACTIVE,
        ];
    }
}
