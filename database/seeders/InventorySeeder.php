<?php

namespace Database\Seeders;

use App\Enums\BulkMovementType;
use App\Enums\PackageMovementType;
use App\Models\BulkMovement;
use App\Models\BulkStock;
use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\PackageSize;
use App\Models\PackageStock;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $cushCoffee = Location::where('name', 'Cush Coffee')->first();
        $cushCentral = Location::where('name', 'Cush Central')->first();

        $dimo = User::where('email', 'dimo@example.com')->first();
        $geri = User::where('email', 'geri@example.com')->first();

        $products = Product::all();
        $packageSizes = PackageSize::all();

        foreach ($products as $product) {
            $bulkStock = BulkStock::create([
                'location_id' => $cushCoffee->id,
                'product_id' => $product->id,
                'quantity_grams' => 0,
                'low_stock_threshold_grams' => 5000,
                'default_sale_price_per_kg' => rand(30, 80),
            ]);

            BulkMovement::create([
                'location_id' => $cushCoffee->id,
                'bulk_stock_id' => $bulkStock->id,
                'user_id' => $dimo->id,
                'type' => BulkMovementType::Initial,
                'quantity_grams_change' => 20000,
                'quantity_grams_before' => 0,
                'quantity_grams_after' => 20000,
                'cost_per_kg' => rand(15, 40),
                'supplier' => 'Initial Supplier',
            ]);

            $bulkStock->update(['quantity_grams' => 20000]);

            BulkMovement::create([
                'location_id' => $cushCoffee->id,
                'bulk_stock_id' => $bulkStock->id,
                'user_id' => $geri->id,
                'type' => BulkMovementType::Packaging,
                'quantity_grams_change' => -3000,
                'quantity_grams_before' => 20000,
                'quantity_grams_after' => 17000,
            ]);

            $bulkStock->update(['quantity_grams' => 17000]);

            foreach ($packageSizes as $index => $size) {
                $packageStock = PackageStock::create([
                    'location_id' => $cushCoffee->id,
                    'product_id' => $product->id,
                    'package_size_id' => $size->id,
                    'quantity' => 0,
                    'price' => round(($size->weight_grams / 1000) * rand(40, 90), 2),
                    'low_stock_threshold' => 10,
                ]);

                $packagedQuantity = rand(20, 50);

                PackageMovement::create([
                    'location_id' => $cushCoffee->id,
                    'package_stock_id' => $packageStock->id,
                    'user_id' => $geri->id,
                    'type' => PackageMovementType::Packaged,
                    'quantity_change' => $packagedQuantity,
                    'quantity_before' => 0,
                    'quantity_after' => $packagedQuantity,
                ]);

                $packageStock->update(['quantity' => $packagedQuantity]);

                if ($index === 0) {
                    PackageMovement::create([
                        'location_id' => $cushCoffee->id,
                        'package_stock_id' => $packageStock->id,
                        'user_id' => $geri->id,
                        'type' => PackageMovementType::Sale,
                        'quantity_change' => -5,
                        'quantity_before' => $packagedQuantity,
                        'quantity_after' => $packagedQuantity - 5,
                        'sale_price' => $packageStock->price,
                    ]);

                    $packageStock->update(['quantity' => $packagedQuantity - 5]);
                }
            }
        }

        $firstProduct = $products->first();
        $centralBulkStock = BulkStock::create([
            'location_id' => $cushCentral->id,
            'product_id' => $firstProduct->id,
            'quantity_grams' => 0,
            'low_stock_threshold_grams' => 5000,
            'default_sale_price_per_kg' => 60,
        ]);

        BulkMovement::create([
            'location_id' => $cushCentral->id,
            'bulk_stock_id' => $centralBulkStock->id,
            'user_id' => $dimo->id,
            'type' => BulkMovementType::TransferIn,
            'quantity_grams_change' => 5000,
            'quantity_grams_before' => 0,
            'quantity_grams_after' => 5000,
        ]);

        $centralBulkStock->update(['quantity_grams' => 5000]);
    }
}
