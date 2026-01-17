<?php

use App\Enums\BulkMovementType;
use App\Models\BulkMovement;
use App\Models\BulkStock;
use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('can create bulk movement', function () {
    $location = Location::factory()->create();
    $stock = BulkStock::factory()->create();
    $user = User::factory()->create();

    $movement = BulkMovement::create([
        'location_id' => $location->id,
        'bulk_stock_id' => $stock->id,
        'user_id' => $user->id,
        'type' => BulkMovementType::Purchase,
        'quantity_grams_change' => 5000,
        'quantity_grams_before' => 10000,
        'quantity_grams_after' => 15000,
        'cost_per_kg' => 25.50,
        'supplier' => 'Test Supplier',
    ]);

    expect($movement)->toBeInstanceOf(BulkMovement::class)
        ->and($movement->quantity_grams_change)->toBe(5000)
        ->and($movement->type)->toBe(BulkMovementType::Purchase);

    assertDatabaseHas('bulk_movements', [
        'location_id' => $location->id,
        'quantity_grams_change' => 5000,
    ]);
});

it('can update bulk movement', function () {
    $movement = BulkMovement::factory()->create(['quantity_grams_change' => 5000]);

    $movement->update(['quantity_grams_change' => 7500]);

    expect($movement->fresh()->quantity_grams_change)->toBe(7500);
});

it('can delete bulk movement', function () {
    $movement = BulkMovement::factory()->create();

    $movement->delete();

    expect(BulkMovement::find($movement->id))->toBeNull();
});

it('can retrieve bulk movement', function () {
    $movement = BulkMovement::factory()->create(['quantity_grams_change' => 12345]);

    $found = BulkMovement::where('quantity_grams_change', 12345)->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($movement->id);
});

it('belongs to location', function () {
    $location = Location::factory()->create(['name' => 'Test Location']);
    $movement = BulkMovement::factory()->for($location, 'location')->create();

    expect($movement->location)->toBeInstanceOf(Location::class)
        ->and($movement->location->id)->toBe($location->id)
        ->and($movement->location->name)->toBe('Test Location');
});

it('belongs to bulk stock', function () {
    $stock = BulkStock::factory()->create();
    $movement = BulkMovement::factory()->for($stock, 'bulkStock')->create();

    expect($movement->bulkStock)->toBeInstanceOf(BulkStock::class)
        ->and($movement->bulkStock->id)->toBe($stock->id);
});

it('belongs to user', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $movement = BulkMovement::factory()->for($user, 'user')->create();

    expect($movement->user)->toBeInstanceOf(User::class)
        ->and($movement->user->id)->toBe($user->id)
        ->and($movement->user->name)->toBe('Test User');
});

it('belongs to customer', function () {
    $customer = User::factory()->create(['name' => 'Customer']);
    $movement = BulkMovement::factory()->create(['customer_id' => $customer->id]);

    expect($movement->customer)->toBeInstanceOf(User::class)
        ->and($movement->customer->id)->toBe($customer->id)
        ->and($movement->customer->name)->toBe('Customer');
});

it('belongs to related movement', function () {
    $relatedMovement = BulkMovement::factory()->create();
    $movement = BulkMovement::factory()->create(['related_movement_id' => $relatedMovement->id]);

    expect($movement->relatedMovement)->toBeInstanceOf(BulkMovement::class)
        ->and($movement->relatedMovement->id)->toBe($relatedMovement->id);
});

it('belongs to package movement', function () {
    $packageMovement = PackageMovement::factory()->create();
    $movement = BulkMovement::factory()->create(['package_movement_id' => $packageMovement->id]);

    expect($movement->packageMovement)->toBeInstanceOf(PackageMovement::class)
        ->and($movement->packageMovement->id)->toBe($packageMovement->id);
});

it('casts type to BulkMovementType enum', function () {
    $movement = BulkMovement::factory()->purchase()->create();

    expect($movement->type)->toBeInstanceOf(BulkMovementType::class)
        ->and($movement->type)->toBe(BulkMovementType::Purchase);
});

it('casts cost_per_kg to decimal', function () {
    $movement = BulkMovement::factory()->create(['cost_per_kg' => 35.75]);

    expect($movement->cost_per_kg)->toBe('35.75');
});

it('casts sale_price_per_kg to decimal', function () {
    $movement = BulkMovement::factory()->create(['sale_price_per_kg' => 50.25]);

    expect($movement->sale_price_per_kg)->toBe('50.25');
});

it('can create purchase movement', function () {
    $movement = BulkMovement::factory()->purchase()->create();

    expect($movement->type)->toBe(BulkMovementType::Purchase)
        ->and($movement->cost_per_kg)->not->toBeNull()
        ->and($movement->supplier)->not->toBeNull();
});

it('can create sale movement', function () {
    $movement = BulkMovement::factory()->sale()->create();

    expect($movement->type)->toBe(BulkMovementType::Sale)
        ->and($movement->quantity_grams_change)->toBeLessThan(0)
        ->and($movement->customer_id)->not->toBeNull();
});

it('can create packaging movement', function () {
    $movement = BulkMovement::factory()->packaging()->create();

    expect($movement->type)->toBe(BulkMovementType::Packaging)
        ->and($movement->quantity_grams_change)->toBeLessThan(0);
});

it('can create initial movement', function () {
    $movement = BulkMovement::factory()->initial()->create();

    expect($movement->type)->toBe(BulkMovementType::Initial)
        ->and($movement->quantity_grams_before)->toBe(0);
});

it('logs activity when created', function () {
    $movement = BulkMovement::factory()->create();

    expect($movement->activities()->count())->toBeGreaterThan(0);
});

it('sets location_id on activity log', function () {
    $location = Location::factory()->create();
    $movement = BulkMovement::factory()->for($location, 'location')->create();

    $activity = $movement->activities()->first();

    expect($activity->location_id)->toBe($location->id);
});
