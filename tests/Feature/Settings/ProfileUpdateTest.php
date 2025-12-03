<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('profile component mounts with user data', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Profile::class);

    expect($component->name)->toBe('John Doe');
    expect($component->email)->toBe('john@example.com');
});

test('profile update validates required name', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', '')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasErrors(['name']);
});

test('profile update validates required email', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', $user->name)
        ->set('email', '')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email']);
});

test('profile update validates email format', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', $user->name)
        ->set('email', 'invalid-email')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email']);
});

test('profile update validates unique email', function () {
    User::factory()->create(['email' => 'existing@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', $user->name)
        ->set('email', 'existing@example.com')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email']);
});

test('profile update validates name max length', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', str_repeat('a', 256))
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasErrors(['name']);
});

test('profile update validates email max length', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $longEmail = str_repeat('a', 250).'@example.com';

    Livewire::test(Profile::class)
        ->set('name', $user->name)
        ->set('email', $longEmail)
        ->call('updateProfileInformation')
        ->assertHasErrors(['email']);
});

test('user can resend verification notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->call('resendVerificationNotification');

    Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
});

test('verified users are redirected when trying to resend verification', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->call('resendVerificationNotification')
        ->assertRedirect(route('dashboard'));
});

test('profile update dispatches event', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertDispatched('profile-updated');
});
