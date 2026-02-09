<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings/profile')
        ->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh())
        ->name->toEqual('Test User')
        ->email->toEqual('test@example.com')
        ->email_verified_at->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull()
        ->and(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
