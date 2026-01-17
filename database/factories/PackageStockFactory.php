<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\PackageSize;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageStock>
 */
class PackageStockFactory extends Factory
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
            'package_size_id' => PackageSize::factory(),
            'quantity' => fake()->numberBetween(20, 100),
            'price' => fake()->randomFloat(2, 5, 50),
            'low_stock_threshold' => 10,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(0, 10),
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }
}
