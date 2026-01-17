<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Single Origin', 'slug' => 'single-origin', 'description' => 'Coffee from a single source or region'],
            ['name' => 'Blends', 'slug' => 'blends', 'description' => 'Expertly crafted coffee blends'],
            ['name' => 'Black Tea', 'slug' => 'black-tea', 'description' => 'Traditional black tea varieties'],
            ['name' => 'Herbal Tea', 'slug' => 'herbal-tea', 'description' => 'Caffeine-free herbal infusions'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
