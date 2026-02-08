<?php

use App\Models\BulkMovement;
use App\Models\BulkStock;
use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\PackageStock;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('can create a location', function () {
    $location = Location::create([
        'name' => 'Test Location',
        'address' => '123 Test St',
        'phone' => '+1234567890',
        'is_active' => true,
    ]);

    expect($location)->toBeInstanceOf(Location::class)
        ->and($location->name)->toBe('Test Location')
        ->and($location->is_active)->toBeTrue();

    assertDatabaseHas('locations', [
        'name' => 'Test Location',
        'phone' => '+1234567890',
    ]);
});

it('can update a location', function () {
    $location = Location::factory()->create(['name' => 'Original Name']);

    $location->update(['name' => 'Updated Name']);

    expect($location->fresh()->name)->toBe('Updated Name');
});

it('can delete a location without related records', function () {
    $location = Location::factory()->create();

    $location->delete();

    expect(Location::find($location->id))->toBeNull();
});

it('cannot delete a location with bulk stocks', function () {
    $location = Location::factory()->create();
    BulkStock::factory()->for($location, 'location')->create();

    expect(fn () => $location->delete())
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('cannot delete a location with package stocks', function () {
    $location = Location::factory()->create();
    PackageStock::factory()->for($location, 'location')->create();

    expect(fn () => $location->delete())
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('can retrieve a location', function () {
    $location = Location::factory()->create(['name' => 'Find Me']);

    $found = Location::where('name', 'Find Me')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($location->id);
});

it('belongs to many users', function () {
    $location = Location::factory()->create();
    $users = User::factory()->count(3)->create();
    $location->users()->attach($users);

    expect($location->users)->toHaveCount(3)
        ->and($location->users->first())->toBeInstanceOf(User::class);
});

it('has bulk stocks relationship', function () {
    $location = Location::factory()->create();
    $bulkStocks = BulkStock::factory()->count(2)->for($location, 'location')->create();

    expect($location->bulkStocks)->toHaveCount(2)
        ->and($location->bulkStocks->first())->toBeInstanceOf(BulkStock::class);
});

it('has package stocks relationship', function () {
    $location = Location::factory()->create();
    $packageStocks = PackageStock::factory()->count(3)->for($location, 'location')->create();

    expect($location->packageStocks)->toHaveCount(3)
        ->and($location->packageStocks->first())->toBeInstanceOf(PackageStock::class);
});

it('has bulk movements relationship', function () {
    $location = Location::factory()->create();
    $movements = BulkMovement::factory()->count(2)->for($location, 'location')->create();

    expect($location->bulkMovements)->toHaveCount(2)
        ->and($location->bulkMovements->first())->toBeInstanceOf(BulkMovement::class);
});

it('has package movements relationship', function () {
    $location = Location::factory()->create();
    $movements = PackageMovement::factory()->count(2)->for($location, 'location')->create();

    expect($location->packageMovements)->toHaveCount(2)
        ->and($location->packageMovements->first())->toBeInstanceOf(PackageMovement::class);
});

it('casts is_active to boolean', function () {
    $location = Location::factory()->create(['is_active' => true]);

    expect($location->is_active)->toBeTrue()
        ->and($location->is_active)->toBeBool();
});

it('can create inactive location', function () {
    $location = Location::factory()->inactive()->create();

    expect($location->is_active)->toBeFalse();
});

it('logs activity when created', function () {
    $location = Location::create([
        'name' => 'Activity Test',
        'address' => 'Test Address',
    ]);

    expect($location->activities()->count())->toBeGreaterThan(0);
});
