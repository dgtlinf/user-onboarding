<?php

use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;
use Dgtlinf\UserOnboarding\Events\OnboardingStarted;
use Illuminate\Support\Facades\Event;

it('can start and distinguish multiple flows', function () {
    Event::fake([OnboardingStarted::class]);

    $user = new FakeUser();

    // Define two different flows in config
    config()->set('user-onboarding.flows', [
        'default' => [
            Step::make('intro')->check(fn($u) => false),
            Step::make('verify_email')->check(fn($u) => false),
        ],
        'team' => [
            Step::make('invite_members')->check(fn($u) => true),
            Step::make('setup_workspace')->check(fn($u) => false),
        ],
    ]);

    // Start the default flow
    $defaultFlow = UserOnboarding::start($user, 'default');
    expect($defaultFlow->steps()->count())->toBe(2);

    // Start another named flow
    $teamFlow = UserOnboarding::start($user, 'team');
    expect($teamFlow->steps()->count())->toBe(2);

    // Ensure each flow instance is independent
    expect($defaultFlow->current()->slug)->toBe('intro');
    expect($teamFlow->current()->slug)->toBe('setup_workspace');

    // Check events were dispatched for both
    Event::assertDispatched(OnboardingStarted::class, 2);
});
