<?php

use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\Events\OnboardingStarted;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;
use Illuminate\Support\Facades\Event;

it('dispatches OnboardingStarted when flow starts', function () {
    Event::fake([OnboardingStarted::class]);

    $user = new FakeUser();

    config()->set('user-onboarding.flows.default', [
        Step::make('intro')->check(fn($u) => false),
    ]);

    $flow = UserOnboarding::start($user);

    Event::assertDispatched(OnboardingStarted::class, function ($event) use ($user, $flow) {
        return $event->user === $user
            && $event->flow === $flow;
    });

    expect($flow->steps()->count())->toBe(1);
    expect($flow->isCompleted())->toBeFalse();
});
