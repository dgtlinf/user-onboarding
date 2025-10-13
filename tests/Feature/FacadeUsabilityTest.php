<?php

use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;

it('can build and use a flow fluently via the facade', function () {
    $user = new FakeUser();

    // Create flow via the facade directly (without config)
    $flow = UserOnboarding::for($user)
        ->addStep(Step::make('profile')->check(fn($u) => true))
        ->addStep(Step::make('verify_email')->check(fn($u) => false));

    // Verify step handling works
    expect($flow->steps()->count())->toBe(2);
    expect($flow->completedSteps()->count())->toBe(1);
    expect($flow->isStepCompleted('profile'))->toBeTrue();
    expect($flow->isStepCompleted('verify_email'))->toBeFalse();

    // Manually complete the second step and confirm all completed
    $flow->completeStep('verify_email');
    expect($flow->isCompleted())->toBeTrue();
    expect($flow->progress())->toBe(100.00);
});
