<?php

use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\Events\StepCompleted;
use Dgtlinf\UserOnboarding\Events\OnboardingCompleted;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;
use Illuminate\Support\Facades\Event;

it('dispatches events when steps are completed', function () {
    Event::fake([StepCompleted::class, OnboardingCompleted::class]);

    $user = new FakeUser();

    // Define flow
    config()->set('user-onboarding.flows.default', [
        Step::make('profile')->check(fn($u) => false),
        Step::make('verify_email')->check(fn($u) => false),
    ]);

    // Start onboarding
    $flow = UserOnboarding::start($user);


    $flow->completeStep('profile');

    Event::assertDispatched(StepCompleted::class, function ($event) use ($user) {
        return $event->user === $user
            && $event->step->slug === 'profile';
    });

    // Finish another step
    $flow->completeStep('verify_email');

    Event::assertDispatched(StepCompleted::class, fn($e) => $e->step->slug === 'verify_email');
    Event::assertDispatched(OnboardingCompleted::class);
});


it('handles multiple onboarding flows independently', function () {
    $user = new FakeUser();

    config()->set('user-onboarding.flows', [
        'default' => [
            Step::make('profile')->check(fn($u) => false),
        ],
        'company_setup' => [
            Step::make('billing')->check(fn($u) => false),
        ],
    ]);

    $userFlow = UserOnboarding::start($user, 'default');
    $companyFlow = UserOnboarding::start($user, 'company_setup');

    expect($userFlow->current()->slug)->toBe('profile');
    expect($companyFlow->current()->slug)->toBe('billing');
});
