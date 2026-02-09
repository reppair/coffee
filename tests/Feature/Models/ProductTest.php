<?php

use App\Enums\ProductType;
use App\Models\BulkStock;
use App\Models\Category;
use App\Models\PackageStock;
use App\Models\Product;

use function Pest\Laravel\assertDatabaseHas;

it('can create a product', function () {
    $category = Category::factory()->create();

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Test Product',
        'description' => 'A delicious test product',
        'slug' => 'test-product',
        'type' => ProductType::Coffee,
        'sku' => 'TEST123',
        'is_active' => true,
    ]);

    expect($product)->toBeInstanceOf(Product::class)
        ->name->toBe('Test Product')
        ->description->toBe('A delicious test product')
        ->type->toBe(ProductType::Coffee)
        ->is_active->toBeTrue();

    assertDatabaseHas('products', [
        'name' => 'Test Product',
        'description' => 'A delicious test product',
        'sku' => 'TEST123',
    ]);
});

it('can update a product', function () {
    $product = Product::factory()->create(['name' => 'Original Name']);

    $product->update(['name' => 'Updated Name']);

    expect($product->fresh()->name)->toBe('Updated Name');
});

it('can delete a product', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect(Product::find($product->id))->toBeNull();
});

it('can retrieve a product', function () {
    $product = Product::factory()->create(['name' => 'Find Me']);

    $found = Product::where('name', 'Find Me')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($product->id);
});

it('belongs to category', function () {
    $category = Category::factory()->create(['name' => 'Test Category']);
    $product = Product::factory()->for($category)->create();

    expect($product->category)->toBeInstanceOf(Category::class)
        ->id->toBe($category->id)
        ->name->toBe('Test Category');
});

it('has bulk stocks relationship', function () {
    $product = Product::factory()->create();
    BulkStock::factory()->count(2)->for($product, 'product')->create();

    expect($product->bulkStocks)->toHaveCount(2)
        ->each->toBeInstanceOf(BulkStock::class);
});

it('has package stocks relationship', function () {
    $product = Product::factory()->create();
    PackageStock::factory()->count(3)->for($product, 'product')->create();

    expect($product->packageStocks)->toHaveCount(3)
        ->each->toBeInstanceOf(PackageStock::class);
});

it('casts type to ProductType enum', function () {
    $product = Product::factory()->coffee()->create();

    expect($product->type)->toBeInstanceOf(ProductType::class)
        ->toBe(ProductType::Coffee);

    $product->update(['type' => ProductType::Tea]);

    expect($product->fresh()->type)->toBe(ProductType::Tea);
});

it('casts is_active to boolean', function () {
    $product = Product::factory()->create(['is_active' => true]);

    expect($product->is_active)->toBeTrue();
});

it('can create coffee product', function () {
    $product = Product::factory()->coffee()->create();

    expect($product->type)->toBe(ProductType::Coffee);
});

it('can create tea product', function () {
    $product = Product::factory()->tea()->create();

    expect($product->type)->toBe(ProductType::Tea);
});

it('can create inactive product', function () {
    $product = Product::factory()->inactive()->create();

    expect($product->is_active)->toBeFalse();
});

it('logs activity when created', function () {
    $product = Product::factory()->create();

    expect($product->activities()->count())->toBeGreaterThan(0);
});

it('can have nullable description', function () {
    $product = Product::factory()->create(['description' => null]);

    expect($product->description)->toBeNull();

    $product->update(['description' => 'Now with description']);

    expect($product->fresh()->description)->toBe('Now with description');
});
