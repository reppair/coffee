<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $singleOrigin = Category::where('slug', 'single-origin')->first();
        $blends = Category::where('slug', 'blends')->first();
        $blackTea = Category::where('slug', 'black-tea')->first();
        $herbalTea = Category::where('slug', 'herbal-tea')->first();

        $products = [
            ['name' => 'Ethiopian Yirgacheffe', 'slug' => 'ethiopian-yirgacheffe', 'type' => ProductType::Coffee, 'category_id' => $singleOrigin->id],
            ['name' => 'Colombian Supremo', 'slug' => 'colombian-supremo', 'type' => ProductType::Coffee, 'category_id' => $singleOrigin->id],
            ['name' => 'House Blend', 'slug' => 'house-blend', 'type' => ProductType::Coffee, 'category_id' => $blends->id],
            ['name' => 'Espresso Roast', 'slug' => 'espresso-roast', 'type' => ProductType::Coffee, 'category_id' => $blends->id],
            ['name' => 'Earl Grey', 'slug' => 'earl-grey', 'type' => ProductType::Tea, 'category_id' => $blackTea->id],
            ['name' => 'Chamomile Dream', 'slug' => 'chamomile-dream', 'type' => ProductType::Tea, 'category_id' => $herbalTea->id],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
