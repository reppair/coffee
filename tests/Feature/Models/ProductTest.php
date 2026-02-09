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

it('soft deletes a product', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect(Product::find($product->id))->toBeNull()
        ->and(Product::withTrashed()->find($product->id))->not->toBeNull()
        ->trashed()->toBeTrue();
});

it('restores a soft-deleted product', function () {
    $product = Product::factory()->create();
    $product->delete();

    $product->restore();

    expect(Product::find($product->id))->not->toBeNull()
        ->trashed()->toBeFalse();
});

it('force deletes a product', function () {
    $product = Product::factory()->create();

    $product->forceDelete();

    expect(Product::withTrashed()->find($product->id))->toBeNull();
});

it('force delete preserves stock records with null product_id', function () {
    $product = Product::factory()->create();
    $bulkStock = BulkStock::factory()->for($product)->create();
    $packageStock = PackageStock::factory()->for($product)->create();

    $product->forceDelete();

    expect($bulkStock->fresh()->product_id)->toBeNull()
        ->and($packageStock->fresh()->product_id)->toBeNull();
});

it('excludes soft-deleted products from default queries', function () {
    $active = Product::factory()->create();
    $deleted = Product::factory()->create();
    $deleted->delete();

    expect(Product::all())->toHaveCount(1)
        ->first()->id->toBe($active->id)
        ->and(Product::withTrashed()->count())->toBe(2);
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

it('auto-generates slug from name on create', function () {
    $product = Product::factory()->create([
        'name' => 'Ethiopian Yirgacheffe',
        'slug' => null,
    ]);

    expect($product->slug)->toBe('ethiopian-yirgacheffe');
});

it('regenerates slug on name update', function () {
    $product = Product::factory()->create(['name' => 'Original Name']);

    expect($product->slug)->toBe('original-name');

    $product->update(['name' => 'Updated Name']);

    expect($product->fresh()->slug)->toBe('updated-name');
});

it('auto-assigns category_sort_order when created', function () {
    $category = Category::factory()->create();

    $product1 = Product::factory()->for($category)->create();
    $product2 = Product::factory()->for($category)->create();
    $product3 = Product::factory()->for($category)->create();

    expect($product1->fresh()->category_sort_order)->toBe(1)
        ->and($product2->fresh()->category_sort_order)->toBe(2)
        ->and($product3->fresh()->category_sort_order)->toBe(3);
});

it('scopes category_sort_order per category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $product1a = Product::factory()->for($category1)->create();
    $product1b = Product::factory()->for($category1)->create();
    $product2a = Product::factory()->for($category2)->create();
    $product2b = Product::factory()->for($category2)->create();

    expect($product1a->fresh()->category_sort_order)->toBe(1)
        ->and($product1b->fresh()->category_sort_order)->toBe(2)
        ->and($product2a->fresh()->category_sort_order)->toBe(1)
        ->and($product2b->fresh()->category_sort_order)->toBe(2);
});

it('reorders within same category', function () {
    $category = Category::factory()->create();

    $product1 = Product::factory()->for($category)->create();
    $product2 = Product::factory()->for($category)->create();
    $product3 = Product::factory()->for($category)->create();

    expect($product1->fresh()->category_sort_order)->toBe(1)
        ->and($product2->fresh()->category_sort_order)->toBe(2)
        ->and($product3->fresh()->category_sort_order)->toBe(3);

    $product1->moveOrderDown();

    expect($product1->fresh()->category_sort_order)->toBe(2)
        ->and($product2->fresh()->category_sort_order)->toBe(1)
        ->and($product3->fresh()->category_sort_order)->toBe(3);
});

it('handles products without category', function () {
    $product = Product::factory()->create(['category_id' => null]);

    expect($product->category_id)->toBeNull()
        ->and($product->category_sort_order)->toBeGreaterThanOrEqual(0);
});

it('reassigns sort order when moving product to different category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $product1a = Product::factory()->for($category1)->create();
    $product1b = Product::factory()->for($category1)->create();

    $product2a = Product::factory()->for($category2)->create();

    expect($product1a->fresh()->category_sort_order)->toBe(1)
        ->and($product1b->fresh()->category_sort_order)->toBe(2)
        ->and($product2a->fresh()->category_sort_order)->toBe(1);

    $product1b->update(['category_id' => $category2->id]);
    $product1b->setHighestOrderNumber();

    expect($product1b->fresh()->category_sort_order)->toBe(2)
        ->and($product1b->fresh()->category_id)->toBe($category2->id);
});
