<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageSize>
 */
class PackageSizeFactory extends Factory
{
    public function definition(): array
    {
        $weightGrams = fake()->randomElement([200, 500, 1000]);

        return [
            'name' => $weightGrams.'g',
            'weight_grams' => $weightGrams,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
