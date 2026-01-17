<?php

use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\PackageSize;
use App\Models\PackageStock;
use App\Models\Product;

use function Pest\Laravel\assertDatabaseHas;

it('can create package stock', function () {
    $location = Location::factory()->create();
    $product = Product::factory()->create();
    $size = PackageSize::factory()->create();

    $stock = PackageStock::create([
        'location_id' => $location->id,
        'product_id' => $product->id,
        'package_size_id' => $size->id,
        'quantity' => 50,
        'price' => 12.99,
        'low_stock_threshold' => 10,
    ]);

    expect($stock)->toBeInstanceOf(PackageStock::class)
        ->and($stock->quantity)->toBe(50)
        ->and($stock->price)->toBe('12.99');

    assertDatabaseHas('package_stocks', [
        'location_id' => $location->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);
});

it('can update package stock', function () {
    $stock = PackageStock::factory()->create(['quantity' => 50]);

    $stock->update(['quantity' => 75]);

    expect($stock->fresh()->quantity)->toBe(75);
});

it('can delete package stock', function () {
    $stock = PackageStock::factory()->create();

    $stock->delete();

    expect(PackageStock::find($stock->id))->toBeNull();
});

it('can retrieve package stock', function () {
    $stock = PackageStock::factory()->create(['quantity' => 123]);

    $found = PackageStock::where('quantity', 123)->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($stock->id);
});

it('belongs to location', function () {
    $location = Location::factory()->create(['name' => 'Test Location']);
    $stock = PackageStock::factory()->for($location, 'location')->create();

    expect($stock->location)->toBeInstanceOf(Location::class)
        ->and($stock->location->id)->toBe($location->id)
        ->and($stock->location->name)->toBe('Test Location');
});

it('belongs to product', function () {
    $product = Product::factory()->create(['name' => 'Test Product']);
    $stock = PackageStock::factory()->for($product, 'product')->create();

    expect($stock->product)->toBeInstanceOf(Product::class)
        ->and($stock->product->id)->toBe($product->id)
        ->and($stock->product->name)->toBe('Test Product');
});

it('belongs to package size', function () {
    $size = PackageSize::factory()->create(['name' => '500g']);
    $stock = PackageStock::factory()->for($size, 'packageSize')->create();

    expect($stock->packageSize)->toBeInstanceOf(PackageSize::class)
        ->and($stock->packageSize->id)->toBe($size->id)
        ->and($stock->packageSize->name)->toBe('500g');
});

it('has package movements relationship', function () {
    $stock = PackageStock::factory()->create();
    $movements = PackageMovement::factory()->count(3)->for($stock, 'packageStock')->create();

    expect($stock->packageMovements)->toHaveCount(3)
        ->and($stock->packageMovements->first())->toBeInstanceOf(PackageMovement::class);
});

it('isLowStock returns true when stock is below threshold', function () {
    $stock = PackageStock::factory()->create([
        'quantity' => 5,
        'low_stock_threshold' => 10,
    ]);

    expect($stock->isLowStock())->toBeTrue();
});

it('isLowStock returns false when stock is above threshold', function () {
    $stock = PackageStock::factory()->create([
        'quantity' => 50,
        'low_stock_threshold' => 10,
    ]);

    expect($stock->isLowStock())->toBeFalse();
});

it('isLowStock returns true when stock equals threshold', function () {
    $stock = PackageStock::factory()->create([
        'quantity' => 10,
        'low_stock_threshold' => 10,
    ]);

    expect($stock->isLowStock())->toBeTrue();
});

it('can create low stock', function () {
    $stock = PackageStock::factory()->lowStock()->create();

    expect($stock->quantity)->toBeLessThanOrEqual(10);
});

it('can create empty stock', function () {
    $stock = PackageStock::factory()->empty()->create();

    expect($stock->quantity)->toBe(0);
});

it('casts price to decimal', function () {
    $stock = PackageStock::factory()->create(['price' => 15.99]);

    expect($stock->price)->toBe('15.99');
});

it('logs activity when created', function () {
    $stock = PackageStock::factory()->create();

    expect($stock->activities()->count())->toBeGreaterThan(0);
});

it('sets location_id on activity log', function () {
    $location = Location::factory()->create();
    $stock = PackageStock::factory()->for($location, 'location')->create();

    $activity = $stock->activities()->first();

    expect($activity->location_id)->toBe($location->id);
});
