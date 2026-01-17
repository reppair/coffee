<?php

namespace Database\Seeders;

use App\Models\PackageSize;
use Illuminate\Database\Seeder;

class PackageSizeSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = [
            ['name' => '200g', 'weight_grams' => 200, 'sort_order' => 1],
            ['name' => '500g', 'weight_grams' => 500, 'sort_order' => 2],
            ['name' => '1kg', 'weight_grams' => 1000, 'sort_order' => 3],
        ];

        foreach ($sizes as $size) {
            PackageSize::create($size);
        }
    }
}
