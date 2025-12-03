<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }
});

test('recovery codes component loads codes for user with 2FA enabled', function () {
    $user = User::factory()->withTwoFactorEnabled()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor.recovery-codes');

    expect($component->recoveryCodes)->toBeArray();
    expect($component->recoveryCodes)->not->toBeEmpty();
});

test('recovery codes component is empty for user without 2FA', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor.recovery-codes');

    expect($component->recoveryCodes)->toBeArray();
    expect($component->recoveryCodes)->toBeEmpty();
});

test('user can regenerate recovery codes', function () {
    $user = User::factory()->withTwoFactorEnabled()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor.recovery-codes');
    $originalCodes = $component->recoveryCodes;

    $component->call('regenerateRecoveryCodes');

    $user->refresh();

    $newCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

    expect($newCodes)->toBeArray();
    expect($newCodes)->not->toBe($originalCodes);
    expect(count($newCodes))->toBe(8);
});

test('recovery codes are properly decrypted', function () {
    $codes = ['code1', 'code2', 'code3', 'code4', 'code5', 'code6', 'code7', 'code8'];

    $user = User::factory()->create([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor.recovery-codes');

    expect($component->recoveryCodes)->toBe($codes);
});

test('recovery codes handles decryption errors gracefully', function () {
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => 'invalid-encrypted-data',
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor.recovery-codes');

    expect($component->recoveryCodes)->toBeEmpty();
    $component->assertHasErrors(['recoveryCodes']);
});

test('recovery codes component uses locked attribute', function () {
    $user = User::factory()->withTwoFactorEnabled()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor.recovery-codes');

    $reflection = new \ReflectionClass($component->instance());
    $property = $reflection->getProperty('recoveryCodes');
    $attributes = $property->getAttributes(\Livewire\Attributes\Locked::class);

    expect($attributes)->toHaveCount(1);
});
