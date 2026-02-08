<?php

use App\Models\BulkMovement;
use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\PackageStock;
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
        Schema::create('package_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(PackageStock::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(User::class, 'customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->integer('quantity_change');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->foreignIdFor(PackageMovement::class, 'related_movement_id')->nullable()->constrained('package_movements')->nullOnDelete();
            $table->foreignIdFor(BulkMovement::class)->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_movements');
    }
};
