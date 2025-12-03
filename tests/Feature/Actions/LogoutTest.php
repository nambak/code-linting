<?php

declare(strict_types=1);

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

test('logout invalidates session and logs out user', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Session::put('test_key', 'test_value');
    $initialToken = Session::token();

    expect(Auth::check())->toBeTrue();
    expect(Session::get('test_key'))->toBe('test_value');

    $logout = new Logout();
    $response = $logout();

    expect(Auth::check())->toBeFalse();
    expect(Session::token())->not->toBe($initialToken);
    expect($response->getTargetUrl())->toBe(url('/'));
});

test('logout redirects to home page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $logout = new Logout();
    $response = $logout();

    expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
    expect($response->getTargetUrl())->toBe(url('/'));
});
