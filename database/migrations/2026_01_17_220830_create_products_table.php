<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Category::class)->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('sku')->nullable()->unique();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('category_sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
