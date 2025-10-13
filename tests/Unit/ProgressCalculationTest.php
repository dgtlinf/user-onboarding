<?php

use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\UserOnboardingFlow;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;

it('calculates progress and current step correctly', function () {
    $user = new FakeUser();

    $steps = [
        Step::make('profile')->check(fn($u) => true),
        Step::make('verify_email')->check(fn($u) => false),
        Step::make('connect_team')->check(fn($u) => false),
    ];

    $flow = new UserOnboardingFlow($user, $steps);

    expect($flow->isCompleted())->toBeFalse();
    expect($flow->progress())->toBe(33.33);
    expect($flow->current()->slug)->toBe('verify_email');
});
