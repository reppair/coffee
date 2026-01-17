<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BulkStock>
 */
class BulkStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'product_id' => Product::factory(),
            'quantity_grams' => fake()->numberBetween(10000, 50000),
            'low_stock_threshold_grams' => 5000,
            'default_sale_price_per_kg' => fake()->randomFloat(2, 20, 100),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_grams' => fake()->numberBetween(0, 5000),
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity_grams' => 0,
        ]);
    }
}
