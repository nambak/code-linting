<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;

test('user can delete their account with valid password', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertRedirect('/');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('user cannot delete account with invalid password', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});

test('password is required for account deletion', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', '')
        ->call('deleteUser')
        ->assertHasErrors(['password']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});

test('user is logged out after account deletion', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $this->assertGuest();
});
