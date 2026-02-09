<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

it('allows admin to view any categories', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', Category::class))->toBeTrue();
});

it('denies staff from viewing any categories', function () {
    $staff = User::factory()->staff()->create();

    expect($staff->can('viewAny', Category::class))->toBeFalse();
});

it('allows admin to view a category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    expect($admin->can('view', $category))->toBeTrue();
});

it('allows admin to create categories', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', Category::class))->toBeTrue();
});

it('allows admin to update a category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    expect($admin->can('update', $category))->toBeTrue();
});

it('allows admin to delete a category without products', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    expect($admin->can('delete', $category))->toBeTrue();
});

it('denies admin from deleting a category with products', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    Product::factory()->for($category)->create();

    $response = app(\App\Policies\CategoryPolicy::class)->delete($admin, $category);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->allowed()->toBeFalse()
        ->message()->toBe('This category has associated products. Remove or reassign them before deleting.');
});

it('denies staff from deleting a category', function () {
    $staff = User::factory()->staff()->create();
    $category = Category::factory()->create();

    expect($staff->can('delete', $category))->toBeFalse();
});

it('denies non-admin access to all actions', function (string $ability, array $args) {
    $customer = User::factory()->create(['is_admin' => false, 'is_staff' => false]);

    expect($customer->can($ability, ...$args))->toBeFalse();
})->with([
    'viewAny' => ['viewAny', [Category::class]],
    'view' => fn () => ['view', [Category::factory()->create()]],
    'create' => ['create', [Category::class]],
    'update' => fn () => ['update', [Category::factory()->create()]],
    'delete' => fn () => ['delete', [Category::factory()->create()]],
]);
