<?php

use App\Models\Location;
use App\Models\PackageSize;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('package_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PackageSize::class)->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->decimal('price', 10, 2);
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();

            $table->unique(['location_id', 'product_id', 'package_size_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_stocks');
    }
};
