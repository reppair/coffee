<?php

namespace Database\Factories;

use App\Enums\BulkMovementType;
use App\Models\BulkStock;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BulkMovement>
 */
class BulkMovementFactory extends Factory
{
    public function definition(): array
    {
        $quantityChange = fake()->numberBetween(1000, 10000);
        $quantityBefore = fake()->numberBetween(5000, 50000);

        return [
            'location_id' => Location::factory(),
            'bulk_stock_id' => BulkStock::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(BulkMovementType::cases()),
            'quantity_grams_change' => $quantityChange,
            'quantity_grams_before' => $quantityBefore,
            'quantity_grams_after' => $quantityBefore + $quantityChange,
            'cost_per_kg' => fake()->optional()->randomFloat(2, 10, 50),
            'sale_price_per_kg' => fake()->optional()->randomFloat(2, 20, 100),
            'supplier' => fake()->optional()->company(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::Purchase,
            'quantity_grams_change' => fake()->numberBetween(5000, 20000),
            'cost_per_kg' => fake()->randomFloat(2, 10, 50),
            'supplier' => fake()->company(),
        ]);
    }

    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::Sale,
            'quantity_grams_change' => -fake()->numberBetween(1000, 5000),
            'sale_price_per_kg' => fake()->randomFloat(2, 20, 100),
            'customer_id' => User::factory(),
        ]);
    }

    public function packaging(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::Packaging,
            'quantity_grams_change' => -fake()->numberBetween(500, 2000),
        ]);
    }

    public function initial(): static
    {
        return $this->state(function (array $attributes) {
            $quantityChange = fake()->numberBetween(10000, 50000);

            return [
                'type' => BulkMovementType::Initial,
                'quantity_grams_before' => 0,
                'quantity_grams_change' => $quantityChange,
                'quantity_grams_after' => $quantityChange,
            ];
        });
    }

    public function transferOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::TransferOut,
            'quantity_grams_change' => -fake()->numberBetween(1000, 5000),
        ]);
    }

    public function transferIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::TransferIn,
            'quantity_grams_change' => fake()->numberBetween(1000, 5000),
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::Adjustment,
            'quantity_grams_change' => fake()->numberBetween(-2000, 2000),
        ]);
    }

    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BulkMovementType::Damaged,
            'quantity_grams_change' => -fake()->numberBetween(100, 2000),
        ]);
    }
}
