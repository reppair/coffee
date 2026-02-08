<?php

use App\Models\BulkMovement;
use App\Models\BulkStock;
use App\Models\Location;
use App\Models\User;
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
        Schema::create('bulk_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(BulkStock::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(User::class, 'customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->integer('quantity_grams_change');
            $table->integer('quantity_grams_before');
            $table->integer('quantity_grams_after');
            $table->decimal('cost_per_kg', 10, 2)->nullable();
            $table->decimal('sale_price_per_kg', 10, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->foreignIdFor(BulkMovement::class, 'related_movement_id')->nullable()->constrained('bulk_movements')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_movements');
    }
};
