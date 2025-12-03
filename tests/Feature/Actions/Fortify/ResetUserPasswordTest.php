<?php

declare(strict_types=1);

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

test('user password can be reset', function () {
    $user = User::factory()->create(['password' => 'old-password']);

    $action = new ResetUserPassword();

    $action->reset($user, [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue();
    expect(Hash::check('old-password', $user->password))->toBeFalse();
});

test('password reset requires password field', function () {
    $user = User::factory()->create();

    $action = new ResetUserPassword();

    expect(fn () => $action->reset($user, []))->toThrow(ValidationException::class);
});

test('password reset validates password rules', function () {
    $user = User::factory()->create();

    $action = new ResetUserPassword();

    expect(fn () => $action->reset($user, [
        'password' => 'short',
    ]))->toThrow(ValidationException::class);
});

test('new password is hashed before saving', function () {
    $user = User::factory()->create();

    $action = new ResetUserPassword();

    $action->reset($user, [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $user->refresh();

    expect($user->password)->not->toBe('new-password');
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

test('password reset persists to database', function () {
    $user = User::factory()->create(['password' => 'old-password']);

    $action = new ResetUserPassword();

    $action->reset($user, [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $freshUser = User::find($user->id);

    expect(Hash::check('new-password', $freshUser->password))->toBeTrue();
});
