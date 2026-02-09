<?php

use App\Models\Location;
use App\Models\User;

it('creates an admin user assigned to all locations', function () {
    $locations = Location::factory()->count(3)->create();

    $this->artisan('app:create-admin')
        ->expectsQuestion('What is the user\'s name?', 'John Doe')
        ->expectsQuestion('What is their email address?', 'john@example.com')
        ->expectsOutputToContain('Admin user created successfully.')
        ->expectsOutputToContain('Assigned to 3 location(s).')
        ->expectsOutputToContain('Password:')
        ->assertSuccessful();

    $user = User::where('email', 'john@example.com')->first();

    expect($user)
        ->not->toBeNull()
        ->name->toBe('John Doe')
        ->is_admin->toBeTrue()
        ->is_active->toBeTrue()
        ->password->not->toBeEmpty();

    expect($user->locations)->toHaveCount(3);
});

it('seeds locations when none exist', function () {
    expect(Location::count())->toBe(0);

    $this->artisan('app:create-admin')
        ->expectsQuestion('What is the user\'s name?', 'John Doe')
        ->expectsQuestion('What is their email address?', 'john@example.com')
        ->assertSuccessful();

    expect(Location::count())->toBeGreaterThan(0);

    $user = User::where('email', 'john@example.com')->first();

    expect($user->locations)->toHaveCount(Location::count());
});

it('validates the email is unique', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->artisan('app:create-admin')
        ->expectsQuestion('What is the user\'s name?', 'John Doe')
        ->expectsQuestion('What is their email address?', 'taken@example.com')
        ->assertFailed();
});

it('validates the email format', function () {
    $this->artisan('app:create-admin')
        ->expectsQuestion('What is the user\'s name?', 'John Doe')
        ->expectsQuestion('What is their email address?', 'not-an-email')
        ->assertFailed();
});
