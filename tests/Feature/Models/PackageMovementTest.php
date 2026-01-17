<?php

use App\Enums\PackageMovementType;
use App\Models\BulkMovement;
use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\PackageStock;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('can create package movement', function () {
    $location = Location::factory()->create();
    $stock = PackageStock::factory()->create();
    $user = User::factory()->create();

    $movement = PackageMovement::create([
        'location_id' => $location->id,
        'package_stock_id' => $stock->id,
        'user_id' => $user->id,
        'type' => PackageMovementType::Sale,
        'quantity_change' => -5,
        'quantity_before' => 50,
        'quantity_after' => 45,
        'sale_price' => 12.99,
    ]);

    expect($movement)->toBeInstanceOf(PackageMovement::class)
        ->and($movement->quantity_change)->toBe(-5)
        ->and($movement->type)->toBe(PackageMovementType::Sale);

    assertDatabaseHas('package_movements', [
        'location_id' => $location->id,
        'quantity_change' => -5,
    ]);
});

it('can update package movement', function () {
    $movement = PackageMovement::factory()->create(['quantity_change' => 10]);

    $movement->update(['quantity_change' => 15]);

    expect($movement->fresh()->quantity_change)->toBe(15);
});

it('can delete package movement', function () {
    $movement = PackageMovement::factory()->create();

    $movement->delete();

    expect(PackageMovement::find($movement->id))->toBeNull();
});

it('can retrieve package movement', function () {
    $movement = PackageMovement::factory()->create(['quantity_change' => 123]);

    $found = PackageMovement::where('quantity_change', 123)->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($movement->id);
});

it('belongs to location', function () {
    $location = Location::factory()->create(['name' => 'Test Location']);
    $movement = PackageMovement::factory()->for($location, 'location')->create();

    expect($movement->location)->toBeInstanceOf(Location::class)
        ->and($movement->location->id)->toBe($location->id)
        ->and($movement->location->name)->toBe('Test Location');
});

it('belongs to package stock', function () {
    $stock = PackageStock::factory()->create();
    $movement = PackageMovement::factory()->for($stock, 'packageStock')->create();

    expect($movement->packageStock)->toBeInstanceOf(PackageStock::class)
        ->and($movement->packageStock->id)->toBe($stock->id);
});

it('belongs to user', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $movement = PackageMovement::factory()->for($user, 'user')->create();

    expect($movement->user)->toBeInstanceOf(User::class)
        ->and($movement->user->id)->toBe($user->id)
        ->and($movement->user->name)->toBe('Test User');
});

it('belongs to customer', function () {
    $customer = User::factory()->create(['name' => 'Customer']);
    $movement = PackageMovement::factory()->create(['customer_id' => $customer->id]);

    expect($movement->customer)->toBeInstanceOf(User::class)
        ->and($movement->customer->id)->toBe($customer->id)
        ->and($movement->customer->name)->toBe('Customer');
});

it('belongs to related movement', function () {
    $relatedMovement = PackageMovement::factory()->create();
    $movement = PackageMovement::factory()->create(['related_movement_id' => $relatedMovement->id]);

    expect($movement->relatedMovement)->toBeInstanceOf(PackageMovement::class)
        ->and($movement->relatedMovement->id)->toBe($relatedMovement->id);
});

it('belongs to bulk movement', function () {
    $bulkMovement = BulkMovement::factory()->create();
    $movement = PackageMovement::factory()->create(['bulk_movement_id' => $bulkMovement->id]);

    expect($movement->bulkMovement)->toBeInstanceOf(BulkMovement::class)
        ->and($movement->bulkMovement->id)->toBe($bulkMovement->id);
});

it('casts type to PackageMovementType enum', function () {
    $movement = PackageMovement::factory()->sale()->create();

    expect($movement->type)->toBeInstanceOf(PackageMovementType::class)
        ->and($movement->type)->toBe(PackageMovementType::Sale);
});

it('casts sale_price to decimal', function () {
    $movement = PackageMovement::factory()->create(['sale_price' => 19.99]);

    expect($movement->sale_price)->toBe('19.99');
});

it('can create sale movement', function () {
    $movement = PackageMovement::factory()->sale()->create();

    expect($movement->type)->toBe(PackageMovementType::Sale)
        ->and($movement->quantity_change)->toBeLessThan(0)
        ->and($movement->sale_price)->not->toBeNull()
        ->and($movement->customer_id)->not->toBeNull();
});

it('can create packaged movement', function () {
    $movement = PackageMovement::factory()->packaged()->create();

    expect($movement->type)->toBe(PackageMovementType::Packaged)
        ->and($movement->quantity_change)->toBeGreaterThan(0);
});

it('can create initial movement', function () {
    $movement = PackageMovement::factory()->initial()->create();

    expect($movement->type)->toBe(PackageMovementType::Initial)
        ->and($movement->quantity_before)->toBe(0);
});

it('logs activity when created', function () {
    $movement = PackageMovement::factory()->create();

    expect($movement->activities()->count())->toBeGreaterThan(0);
});

it('sets location_id on activity log', function () {
    $location = Location::factory()->create();
    $movement = PackageMovement::factory()->for($location, 'location')->create();

    $activity = $movement->activities()->first();

    expect($activity->location_id)->toBe($location->id);
});
