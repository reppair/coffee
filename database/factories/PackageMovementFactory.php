<?php

namespace Database\Factories;

use App\Enums\PackageMovementType;
use App\Models\Location;
use App\Models\PackageStock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageMovement>
 */
class PackageMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityChange = fake()->numberBetween(1, 20);
        $quantityBefore = fake()->numberBetween(10, 100);

        return [
            'location_id' => Location::factory(),
            'package_stock_id' => PackageStock::factory(),
            'user_id' => User::factory(),
            'customer_id' => null,
            'type' => fake()->randomElement(PackageMovementType::cases()),
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityBefore + $quantityChange,
            'sale_price' => fake()->optional()->randomFloat(2, 5, 50),
            'related_movement_id' => null,
            'bulk_movement_id' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PackageMovementType::Sale,
            'quantity_change' => -fake()->numberBetween(1, 10),
            'sale_price' => fake()->randomFloat(2, 5, 50),
            'customer_id' => User::factory(),
        ]);
    }

    public function packaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PackageMovementType::Packaged,
            'quantity_change' => fake()->numberBetween(5, 20),
        ]);
    }

    public function initial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PackageMovementType::Initial,
            'quantity_before' => 0,
            'quantity_change' => fake()->numberBetween(10, 50),
            'quantity_after' => $attributes['quantity_change'],
        ]);
    }
}
