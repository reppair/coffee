<?php

use App\Models\Location;
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
        Schema::create('bulk_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->integer('quantity_grams')->default(0);
            $table->integer('low_stock_threshold_grams')->default(5000);
            $table->decimal('default_sale_price_per_kg', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_stocks');
    }
};
