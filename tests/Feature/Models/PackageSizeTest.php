<?php

use App\Models\PackageSize;
use App\Models\PackageStock;

use function Pest\Laravel\assertDatabaseHas;

it('can create a package size', function () {
    $size = PackageSize::create([
        'name' => '250g',
        'weight_grams' => 250,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    expect($size)->toBeInstanceOf(PackageSize::class)
        ->name->toBe('250g')
        ->weight_grams->toBe(250)
        ->is_active->toBeTrue();

    assertDatabaseHas('package_sizes', [
        'name' => '250g',
        'weight_grams' => 250,
    ]);
});

it('can update a package size', function () {
    $size = PackageSize::factory()->create(['name' => '100g']);

    $size->update(['name' => '150g', 'weight_grams' => 150]);

    $fresh = $size->fresh();

    expect($fresh)
        ->name->toBe('150g')
        ->weight_grams->toBe(150);
});

it('can delete a package size', function () {
    $size = PackageSize::factory()->create();

    $size->delete();

    expect(PackageSize::find($size->id))->toBeNull();
});

it('can retrieve a package size', function () {
    $size = PackageSize::factory()->create(['name' => '500g']);

    $found = PackageSize::where('name', '500g')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($size->id);
});

it('has package stocks relationship', function () {
    $size = PackageSize::factory()->create();
    PackageStock::factory()->count(3)->for($size, 'packageSize')->create();

    expect($size->packageStocks)->toHaveCount(3)
        ->each->toBeInstanceOf(PackageStock::class);
});

it('casts is_active to boolean', function () {
    $size = PackageSize::factory()->create(['is_active' => true]);

    expect($size->is_active)->toBeTrue();
});

it('can create inactive package size', function () {
    $size = PackageSize::factory()->inactive()->create();

    expect($size->is_active)->toBeFalse();
});

it('logs activity when created', function () {
    $size = PackageSize::create([
        'name' => '300g',
        'weight_grams' => 300,
        'sort_order' => 1,
    ]);

    expect($size->activities()->count())->toBeGreaterThan(0);
});
