<?php

use App\Models\BulkStock;
use App\Models\PackageStock;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

it('allows admin to view any products', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', Product::class))->toBeTrue();
});

it('denies staff from viewing any products', function () {
    $staff = User::factory()->staff()->create();

    expect($staff->can('viewAny', Product::class))->toBeFalse();
});

it('allows admin to view a product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    expect($admin->can('view', $product))->toBeTrue();
});

it('allows admin to create products', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', Product::class))->toBeTrue();
});

it('allows admin to update a product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    expect($admin->can('update', $product))->toBeTrue();
});

it('allows admin to delete a product with zero-quantity stock', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    BulkStock::factory()->empty()->for($product)->create();
    PackageStock::factory()->empty()->for($product)->create();

    expect($admin->can('delete', $product))->toBeTrue();
});

it('denies admin from deleting a product with active bulk stock', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    BulkStock::factory()->for($product)->create(['quantity_grams' => 500]);

    $response = app(\App\Policies\ProductPolicy::class)->delete($admin, $product);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->allowed()->toBeFalse()
        ->message()->toContain('active inventory');
});

it('denies admin from deleting a product with active package stock', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    PackageStock::factory()->for($product)->create(['quantity' => 5]);

    $response = app(\App\Policies\ProductPolicy::class)->delete($admin, $product);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->allowed()->toBeFalse()
        ->message()->toContain('active inventory');
});

it('allows admin to force delete a product with zero-quantity stock', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    BulkStock::factory()->empty()->for($product)->create();
    PackageStock::factory()->empty()->for($product)->create();

    expect($admin->can('forceDelete', $product))->toBeTrue();
});

it('denies admin from force deleting a product with active stock', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    BulkStock::factory()->for($product)->create(['quantity_grams' => 500]);

    $response = app(\App\Policies\ProductPolicy::class)->forceDelete($admin, $product);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->allowed()->toBeFalse()
        ->message()->toContain('permanently delete');
});

it('allows admin to delete any products', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('deleteAny', Product::class))->toBeTrue();
});

it('allows admin to restore a soft-deleted product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $product->delete();

    expect($admin->can('restore', $product))->toBeTrue();
});

it('denies staff from deleting a product', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    expect($staff->can('delete', $product))->toBeFalse();
});

it('denies non-admin access to all actions', function (string $ability, array $args) {
    $customer = User::factory()->create(['is_admin' => false, 'is_staff' => false]);

    expect($customer->can($ability, ...$args))->toBeFalse();
})->with([
    'viewAny' => ['viewAny', [Product::class]],
    'view' => fn () => ['view', [Product::factory()->create()]],
    'create' => ['create', [Product::class]],
    'update' => fn () => ['update', [Product::factory()->create()]],
    'delete' => fn () => ['delete', [Product::factory()->create()]],
    'forceDelete' => fn () => ['forceDelete', [Product::factory()->create()]],
    'restore' => fn () => ['restore', [Product::factory()->create()]],
]);
