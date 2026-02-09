<?php

use App\Models\BulkMovement;
use App\Models\Location;
use App\Models\PackageMovement;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('can create a user', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'is_admin' => false,
        'is_staff' => false,
        'is_active' => true,
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->name->toBe('Test User')
        ->email->toBe('test@example.com');

    assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('can update a user', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $user->update(['name' => 'Updated Name']);

    expect($user->fresh()->name)->toBe('Updated Name');
});

it('can delete a user', function () {
    $user = User::factory()->create();

    $user->delete();

    expect(User::find($user->id))->toBeNull();
});

it('can retrieve a user', function () {
    $user = User::factory()->create(['name' => 'Find Me']);

    $found = User::where('name', 'Find Me')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($user->id);
});

it('belongs to many locations', function () {
    $user = User::factory()->create();
    $locations = Location::factory()->count(3)->create();
    $user->locations()->attach($locations);

    expect($user->locations)->toHaveCount(3)
        ->each->toBeInstanceOf(Location::class);
});

it('has bulk movements relationship', function () {
    $user = User::factory()->create();
    BulkMovement::factory()->count(2)->for($user, 'user')->create();

    expect($user->bulkMovements)->toHaveCount(2)
        ->each->toBeInstanceOf(BulkMovement::class);
});

it('has package movements relationship', function () {
    $user = User::factory()->create();
    PackageMovement::factory()->count(2)->for($user, 'user')->create();

    expect($user->packageMovements)->toHaveCount(2)
        ->each->toBeInstanceOf(PackageMovement::class);
});

it('has customer bulk movements relationship', function () {
    $customer = User::factory()->create();
    BulkMovement::factory()->count(2)->create(['customer_id' => $customer->id]);

    expect($customer->customerBulkMovements)->toHaveCount(2)
        ->each->toBeInstanceOf(BulkMovement::class);
});

it('has customer package movements relationship', function () {
    $customer = User::factory()->create();
    PackageMovement::factory()->count(2)->create(['customer_id' => $customer->id]);

    expect($customer->customerPackageMovements)->toHaveCount(2)
        ->each->toBeInstanceOf(PackageMovement::class);
});

it('casts is_admin to boolean', function () {
    $user = User::factory()->admin()->create();

    expect($user->is_admin)->toBeTrue();
});

it('casts is_staff to boolean', function () {
    $user = User::factory()->staff()->create();

    expect($user->is_staff)->toBeTrue();
});

it('casts is_active to boolean', function () {
    $user = User::factory()->create(['is_active' => true]);

    expect($user->is_active)->toBeTrue();
});

it('can create admin user', function () {
    $user = User::factory()->admin()->create();

    expect($user)
        ->is_admin->toBeTrue()
        ->is_staff->toBeFalse();
});

it('can create staff user', function () {
    $user = User::factory()->staff()->create();

    expect($user)
        ->is_admin->toBeFalse()
        ->is_staff->toBeTrue();
});

it('can create customer user', function () {
    $user = User::factory()->create();

    expect($user)
        ->is_admin->toBeFalse()
        ->is_staff->toBeFalse();
});

it('can create inactive user', function () {
    $user = User::factory()->inactive()->create();

    expect($user->is_active)->toBeFalse();
});

it('identifies admin correctly', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $customer = User::factory()->create();

    expect($admin->isAdmin())->toBeTrue()
        ->and($staff->isAdmin())->toBeFalse()
        ->and($customer->isAdmin())->toBeFalse();
});

it('identifies staff correctly', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $customer = User::factory()->create();

    expect($staff->isStaff())->toBeTrue()
        ->and($admin->isStaff())->toBeFalse()
        ->and($customer->isStaff())->toBeFalse();
});

it('identifies customer correctly', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();
    $customer = User::factory()->create();

    expect($customer->isCustomer())->toBeTrue()
        ->and($admin->isCustomer())->toBeFalse()
        ->and($staff->isCustomer())->toBeFalse();
});

it('generates initials from full name', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    expect($user->initials())->toBe('JD');
});

it('generates initials from single name', function () {
    $user = User::factory()->create(['name' => 'John']);

    expect($user->initials())->toBe('J');
});

it('generates initials from three-part name using first two', function () {
    $user = User::factory()->create(['name' => 'John Michael Doe']);

    expect($user->initials())->toBe('JM');
});
