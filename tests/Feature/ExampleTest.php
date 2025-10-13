<?php

use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\UserOnboardingFlow;

it('can boot the package service provider', function () {
    $this->assertTrue(app()->bound('user-onboarding'));
});


it('can create a flow for a user', function () {
    $flow = UserOnboarding::for(new FakeUser);
    expect($flow)->toBeInstanceOf(UserOnboardingFlow::class);
});

