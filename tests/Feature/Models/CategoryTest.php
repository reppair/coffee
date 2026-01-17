<?php

use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\assertDatabaseHas;

it('can create a category', function () {
    $category = Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'description' => 'Test description',
        'is_active' => true,
    ]);

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Test Category')
        ->and($category->slug)->toBe('test-category')
        ->and($category->is_active)->toBeTrue();

    assertDatabaseHas('categories', [
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);
});

it('can update a category', function () {
    $category = Category::factory()->create(['name' => 'Original Name']);

    $category->update(['name' => 'Updated Name']);

    expect($category->fresh()->name)->toBe('Updated Name');
});

it('can delete a category', function () {
    $category = Category::factory()->create();

    $category->delete();

    expect(Category::find($category->id))->toBeNull();
});

it('can retrieve a category', function () {
    $category = Category::factory()->create(['name' => 'Find Me']);

    $found = Category::where('name', 'Find Me')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($category->id);
});

it('has products relationship', function () {
    $category = Category::factory()->create();
    $products = Product::factory()->count(3)->for($category)->create();

    expect($category->products)->toHaveCount(3)
        ->and($category->products->first())->toBeInstanceOf(Product::class)
        ->and($category->products->pluck('id')->toArray())->toEqual($products->pluck('id')->toArray());
});

it('casts is_active to boolean', function () {
    $category = Category::factory()->create(['is_active' => true]);

    expect($category->is_active)->toBeTrue()
        ->and($category->is_active)->toBeBool();

    $category->update(['is_active' => false]);

    expect($category->fresh()->is_active)->toBeFalse()
        ->and($category->fresh()->is_active)->toBeBool();
});

it('can create inactive category', function () {
    $category = Category::factory()->inactive()->create();

    expect($category->is_active)->toBeFalse();
});

it('logs activity when created', function () {
    $category = Category::create([
        'name' => 'Activity Test',
        'slug' => 'activity-test',
    ]);

    expect($category->activities()->count())->toBeGreaterThan(0);
});
