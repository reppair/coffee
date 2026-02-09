<?php

use App\Models\Location;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->locations = Location::factory()->count(3)->create();
    $this->panel = Filament::getPanel('/admin');
});

it('allows admin to access the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/'.$this->locations->first()->id)
        ->assertOk();
});

it('allows staff to access the admin panel', function () {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->locations->first());

    $this->actingAs($staff)
        ->get('/admin/'.$this->locations->first()->id)
        ->assertOk();
});

it('denies customer access to the admin panel', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/admin/'.$this->locations->first()->id)
        ->assertForbidden();
});

it('grants panel access to admin', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->canAccessPanel($this->panel))->toBeTrue();
});

it('grants panel access to staff', function () {
    $staff = User::factory()->staff()->create();

    expect($staff->canAccessPanel($this->panel))->toBeTrue();
});

it('denies panel access to customer', function () {
    $customer = User::factory()->create();

    expect($customer->canAccessPanel($this->panel))->toBeFalse();
});

it('returns all locations as tenants for admin', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->getTenants($this->panel))
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Location::class);
});

it('returns only assigned locations as tenants for staff', function () {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->locations->first());

    expect($staff->getTenants($this->panel))
        ->toHaveCount(1)
        ->first()->id->toBe($this->locations->first()->id);
});

it('returns multiple assigned locations as tenants for staff', function () {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach([$this->locations[0]->id, $this->locations[1]->id]);

    expect($staff->getTenants($this->panel))->toHaveCount(2);
});

it('returns empty collection as tenants for staff with no locations', function () {
    $staff = User::factory()->staff()->create();

    expect($staff->getTenants($this->panel))->toBeEmpty();
});

it('allows admin to access any location tenant', function () {
    $admin = User::factory()->admin()->create();

    foreach ($this->locations as $location) {
        expect($admin->canAccessTenant($location))->toBeTrue();
    }
});

it('allows staff to access assigned location tenant', function () {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->locations->first());

    expect($staff->canAccessTenant($this->locations->first()))->toBeTrue();
});

it('denies staff access to unassigned location tenant', function () {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->locations->first());

    expect($staff->canAccessTenant($this->locations->get(1)))->toBeFalse()
        ->and($staff->canAccessTenant($this->locations->get(2)))->toBeFalse();
});

it('denies staff HTTP access to unassigned location', function () {
    $staff = User::factory()->staff()->create();
    $staff->locations()->attach($this->locations->first());

    $this->actingAs($staff)
        ->get('/admin/'.$this->locations->get(1)->id)
        ->assertNotFound();
});
