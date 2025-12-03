<?php

declare(strict_types=1);

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

test('create new user with valid data', function () {
    $action = new CreateNewUser();

    $user = $action->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect(Hash::check('password', $user->password))->toBeTrue();

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('create new user requires name', function () {
    $action = new CreateNewUser();

    expect(fn () => $action->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]))->toThrow(ValidationException::class);
});

test('create new user requires email', function () {
    $action = new CreateNewUser();

    expect(fn () => $action->create([
        'name' => 'Test User',
        'password' => 'password',
    ]))->toThrow(ValidationException::class);
});

test('create new user requires valid email', function () {
    $action = new CreateNewUser();

    expect(fn () => $action->create([
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password',
    ]))->toThrow(ValidationException::class);
});

test('create new user requires password', function () {
    $action = new CreateNewUser();

    expect(fn () => $action->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]))->toThrow(ValidationException::class);
});

test('create new user requires unique email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $action = new CreateNewUser();

    expect(fn () => $action->create([
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password',
    ]))->toThrow(ValidationException::class);
});

test('create new user validates name length', function () {
    $action = new CreateNewUser();

    expect(fn () => $action->create([
        'name' => str_repeat('a', 256),
        'email' => 'test@example.com',
        'password' => 'password',
    ]))->toThrow(ValidationException::class);
});

test('create new user validates email length', function () {
    $action = new CreateNewUser();

    $longEmail = str_repeat('a', 250).'@example.com';

    expect(fn () => $action->create([
        'name' => 'Test User',
        'email' => $longEmail,
        'password' => 'password',
    ]))->toThrow(ValidationException::class);
});

test('password is hashed before saving', function () {
    $action = new CreateNewUser();

    $user = $action->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->password)->not->toBe('password');
    expect(Hash::check('password', $user->password))->toBeTrue();
});
