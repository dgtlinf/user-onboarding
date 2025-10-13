<?php

use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\UserOnboardingFlow;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;

it('detects completed and incomplete steps correctly', function () {
    $user = new FakeUser();

    $steps = [
        Step::make('one')->check(fn($u) => true),
        Step::make('two')->check(fn($u) => false),
    ];

    $flow = new UserOnboardingFlow($user, $steps);

    expect($flow->completedSteps()->pluck('slug'))->toContain('one');
    expect($flow->incompleteSteps()->pluck('slug'))->toContain('two');
    expect($flow->isStepCompleted('one'))->toBeTrue();
    expect($flow->isStepCompleted('two'))->toBeFalse();
});
