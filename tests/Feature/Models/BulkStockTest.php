<?php

use App\Models\BulkMovement;
use App\Models\BulkStock;
use App\Models\Location;
use App\Models\Product;

use function Pest\Laravel\assertDatabaseHas;

it('can create bulk stock', function () {
    $location = Location::factory()->create();
    $product = Product::factory()->create();

    $stock = BulkStock::create([
        'location_id' => $location->id,
        'product_id' => $product->id,
        'quantity_grams' => 10000,
        'low_stock_threshold_grams' => 5000,
        'default_sale_price_per_kg' => 50.00,
    ]);

    expect($stock)->toBeInstanceOf(BulkStock::class)
        ->and($stock->quantity_grams)->toBe(10000)
        ->and($stock->default_sale_price_per_kg)->toBe('50.00');

    assertDatabaseHas('bulk_stocks', [
        'location_id' => $location->id,
        'product_id' => $product->id,
        'quantity_grams' => 10000,
    ]);
});

it('can update bulk stock', function () {
    $stock = BulkStock::factory()->create(['quantity_grams' => 10000]);

    $stock->update(['quantity_grams' => 15000]);

    expect($stock->fresh()->quantity_grams)->toBe(15000);
});

it('can delete bulk stock', function () {
    $stock = BulkStock::factory()->create();

    $stock->delete();

    expect(BulkStock::find($stock->id))->toBeNull();
});

it('can retrieve bulk stock', function () {
    $stock = BulkStock::factory()->create(['quantity_grams' => 12345]);

    $found = BulkStock::where('quantity_grams', 12345)->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($stock->id);
});

it('belongs to location', function () {
    $location = Location::factory()->create(['name' => 'Test Location']);
    $stock = BulkStock::factory()->for($location, 'location')->create();

    expect($stock->location)->toBeInstanceOf(Location::class)
        ->and($stock->location->id)->toBe($location->id)
        ->and($stock->location->name)->toBe('Test Location');
});

it('belongs to product', function () {
    $product = Product::factory()->create(['name' => 'Test Product']);
    $stock = BulkStock::factory()->for($product, 'product')->create();

    expect($stock->product)->toBeInstanceOf(Product::class)
        ->and($stock->product->id)->toBe($product->id)
        ->and($stock->product->name)->toBe('Test Product');
});

it('has bulk movements relationship', function () {
    $stock = BulkStock::factory()->create();
    $movements = BulkMovement::factory()->count(3)->for($stock, 'bulkStock')->create();

    expect($stock->bulkMovements)->toHaveCount(3)
        ->and($stock->bulkMovements->first())->toBeInstanceOf(BulkMovement::class);
});

it('isLowStock returns true when stock is below threshold', function () {
    $stock = BulkStock::factory()->create([
        'quantity_grams' => 3000,
        'low_stock_threshold_grams' => 5000,
    ]);

    expect($stock->isLowStock())->toBeTrue();
});

it('isLowStock returns false when stock is above threshold', function () {
    $stock = BulkStock::factory()->create([
        'quantity_grams' => 10000,
        'low_stock_threshold_grams' => 5000,
    ]);

    expect($stock->isLowStock())->toBeFalse();
});

it('isLowStock returns true when stock equals threshold', function () {
    $stock = BulkStock::factory()->create([
        'quantity_grams' => 5000,
        'low_stock_threshold_grams' => 5000,
    ]);

    expect($stock->isLowStock())->toBeTrue();
});

it('can create low stock', function () {
    $stock = BulkStock::factory()->lowStock()->create();

    expect($stock->quantity_grams)->toBeLessThanOrEqual(5000);
});

it('can create empty stock', function () {
    $stock = BulkStock::factory()->empty()->create();

    expect($stock->quantity_grams)->toBe(0);
});

it('casts default_sale_price_per_kg to decimal', function () {
    $stock = BulkStock::factory()->create(['default_sale_price_per_kg' => 75.50]);

    expect($stock->default_sale_price_per_kg)->toBe('75.50');
});

it('logs activity when created', function () {
    $stock = BulkStock::factory()->create();

    expect($stock->activities()->count())->toBeGreaterThan(0);
});

it('sets location_id on activity log', function () {
    $location = Location::factory()->create();
    $stock = BulkStock::factory()->for($location, 'location')->create();

    $activity = $stock->activities()->first();

    expect($activity->location_id)->toBe($location->id);
});
