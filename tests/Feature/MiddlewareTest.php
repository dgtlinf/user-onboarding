<?php

use Dgtlinf\UserOnboarding\Http\Middleware\EnsureUserOnboardingStepCompleted;
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(EnsureUserOnboardingStepCompleted::class)
        ->get('/protected', fn() => 'ok');
});

it('denies access if user onboarding is not completed', function () {
    config()->set('user-onboarding.flows.default', [
        \Dgtlinf\UserOnboarding\Step::make('profile')->check(fn() => false),
    ]);

    $this->actingAs(new \Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser())
        ->get('/protected')
        ->assertRedirect('/onboarding');
});


it('allows access if onboarding is completed', function () {
    $user = new FakeUser();

    config()->set('user-onboarding.flows.default', [
        Step::make('profile')->check(fn($u) => true),
    ]);

    // Overwrite middleware binding to skip redirect
    Route::middleware(EnsureUserOnboardingStepCompleted::class)
        ->get('/protected-complete', fn() => 'ok');

    $this->actingAs($user)
        ->get('/protected-complete')
        ->assertOk();
});
