<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'description' => fake()->optional()->paragraph(),
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(ProductType::cases()),
            'sku' => fake()->optional()->regexify('[A-Z]{3}[0-9]{4}'),
            'is_active' => true,
        ];
    }

    public function coffee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Coffee,
        ]);
    }

    public function tea(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Tea,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
