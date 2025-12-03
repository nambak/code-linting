<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

test('two factor settings page can be rendered', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('two-factor.show'))
        ->assertOk()
        ->assertSee('Two Factor Authentication')
        ->assertSee('Disabled');
});

test('two factor settings page requires password confirmation when enabled', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('two-factor.show'));

    $response->assertRedirect(route('password.confirm'));
});

test('two factor settings page returns forbidden response when two factor is disabled', function () {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('two-factor.show'));

    $response->assertForbidden();
});

test('two factor authentication disabled when confirmation abandoned between requests', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor');

    $component->assertSet('twoFactorEnabled', false);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('user can enable two factor authentication', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor')
        ->assertSet('twoFactorEnabled', false)
        ->call('enable')
        ->assertSet('showModal', true);

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();
});

test('user can disable two factor authentication', function () {
    $user = User::factory()->withTwoFactorEnabled()->create();

    $this->actingAs($user);

    Livewire::test('settings.two-factor')
        ->assertSet('twoFactorEnabled', true)
        ->call('disable')
        ->assertSet('twoFactorEnabled', false);

    $user->refresh();
    expect($user->two_factor_secret)->toBeNull();
});

test('modal shows verification step when confirmation required', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test('settings.two-factor')
        ->call('enable')
        ->call('showVerificationIfNecessary')
        ->assertSet('showVerificationStep', true);
});

test('modal closes without verification when confirmation not required', function () {
    Features::twoFactorAuthentication(['confirm' => false]);

    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test('settings.two-factor')
        ->call('enable')
        ->call('showVerificationIfNecessary')
        ->assertSet('showModal', false);
});

test('user can reset verification state', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test('settings.two-factor')
        ->set('code', '123456')
        ->set('showVerificationStep', true)
        ->call('resetVerification')
        ->assertSet('code', '')
        ->assertSet('showVerificationStep', false);
});

test('user can close modal', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test('settings.two-factor')
        ->call('enable')
        ->assertSet('showModal', true)
        ->call('closeModal')
        ->assertSet('showModal', false)
        ->assertSet('code', '')
        ->assertSet('showVerificationStep', false);
});

test('qr code and setup key are loaded when enabling 2fa', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    expect($component->qrCodeSvg)->not->toBeEmpty();
    expect($component->manualSetupKey)->not->toBeEmpty();
});

test('setup data errors are handled gracefully', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'invalid-data',
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor');

    expect($component->qrCodeSvg)->toBe('');
    expect($component->manualSetupKey)->toBe('');
});

test('modal config returns correct state when 2fa not confirmed', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    $config = $component->instance()->getModalConfigProperty();

    expect($config['title'])->toContain('Enable Two-Factor');
});

test('modal config returns correct state for verification step', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor')
        ->call('enable')
        ->set('showVerificationStep', true);

    $config = $component->instance()->getModalConfigProperty();

    expect($config['title'])->toContain('Verify Authentication Code');
});

test('code validation requires 6 characters', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test('settings.two-factor')
        ->call('enable')
        ->call('showVerificationIfNecessary')
        ->set('code', '12345')
        ->call('confirmTwoFactor')
        ->assertHasErrors(['code']);
});
