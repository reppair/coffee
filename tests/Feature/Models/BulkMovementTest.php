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
        ->quantity_grams_change->toBe(5000)
        ->type->toBe(BulkMovementType::Purchase);

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
        ->id->toBe($location->id)
        ->name->toBe('Test Location');
});

it('belongs to bulk stock', function () {
    $stock = BulkStock::factory()->create();
    $movement = BulkMovement::factory()->for($stock, 'bulkStock')->create();

    expect($movement->bulkStock)->toBeInstanceOf(BulkStock::class)
        ->id->toBe($stock->id);
});

it('belongs to user', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $movement = BulkMovement::factory()->for($user, 'user')->create();

    expect($movement->user)->toBeInstanceOf(User::class)
        ->id->toBe($user->id)
        ->name->toBe('Test User');
});

it('belongs to customer', function () {
    $customer = User::factory()->create(['name' => 'Customer']);
    $movement = BulkMovement::factory()->create(['customer_id' => $customer->id]);

    expect($movement->customer)->toBeInstanceOf(User::class)
        ->id->toBe($customer->id)
        ->name->toBe('Customer');
});

it('belongs to related movement', function () {
    $relatedMovement = BulkMovement::factory()->create();
    $movement = BulkMovement::factory()->create(['related_movement_id' => $relatedMovement->id]);

    expect($movement->relatedMovement)->toBeInstanceOf(BulkMovement::class)
        ->id->toBe($relatedMovement->id);
});

it('has one package movement', function () {
    $movement = BulkMovement::factory()->create();
    $packageMovement = PackageMovement::factory()->create(['bulk_movement_id' => $movement->id]);

    expect($movement->packageMovement)->toBeInstanceOf(PackageMovement::class)
        ->id->toBe($packageMovement->id);
});

it('casts type to BulkMovementType enum', function () {
    $movement = BulkMovement::factory()->purchase()->create();

    expect($movement->type)->toBeInstanceOf(BulkMovementType::class)
        ->toBe(BulkMovementType::Purchase);
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

    expect($movement)
        ->type->toBe(BulkMovementType::Purchase)
        ->cost_per_kg->not->toBeNull()
        ->supplier->not->toBeNull();
});

it('can create sale movement', function () {
    $movement = BulkMovement::factory()->sale()->create();

    expect($movement)
        ->type->toBe(BulkMovementType::Sale)
        ->quantity_grams_change->toBeLessThan(0)
        ->customer_id->not->toBeNull();
});

it('can create packaging movement', function () {
    $movement = BulkMovement::factory()->packaging()->create();

    expect($movement)
        ->type->toBe(BulkMovementType::Packaging)
        ->quantity_grams_change->toBeLessThan(0);
});

it('can create initial movement', function () {
    $movement = BulkMovement::factory()->initial()->create();

    expect($movement)
        ->type->toBe(BulkMovementType::Initial)
        ->quantity_grams_before->toBe(0);
});

it('can create transfer out movement', function () {
    $movement = BulkMovement::factory()->transferOut()->create();

    expect($movement)
        ->type->toBe(BulkMovementType::TransferOut)
        ->quantity_grams_change->toBeLessThan(0);
});

it('can create transfer in movement', function () {
    $movement = BulkMovement::factory()->transferIn()->create();

    expect($movement)
        ->type->toBe(BulkMovementType::TransferIn)
        ->quantity_grams_change->toBeGreaterThan(0);
});

it('can create adjustment movement', function () {
    $movement = BulkMovement::factory()->adjustment()->create();

    expect($movement->type)->toBe(BulkMovementType::Adjustment);
});

it('can create damaged movement', function () {
    $movement = BulkMovement::factory()->damaged()->create();

    expect($movement)
        ->type->toBe(BulkMovementType::Damaged)
        ->quantity_grams_change->toBeLessThan(0);
});

it('logs activity when created', function () {
    $movement = BulkMovement::factory()->create();

    expect($movement->activities()->count())->toBeGreaterThan(0);
});
