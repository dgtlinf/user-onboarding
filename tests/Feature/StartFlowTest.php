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


it('supports context-based onboarding flows', function () {
    Event::fake([OnboardingStarted::class]);

    $user = new FakeUser();
    $company = (object) ['id' => 42, 'name' => 'Digital Infinity'];

    // Define flow in config
    config()->set('user-onboarding.flows.company_setup', [
        Step::make('billing')
            ->check(fn($u, $context) => isset($context->id) && $context->name === 'Digital Infinity'),
        Step::make('documents')
            ->check(fn($u, $context) => $context->id === 42),
    ]);

    // Start onboarding flow with context
    $flow = UserOnboarding::start($user, 'company_setup', $company);

    expect($flow->context())->toBe($company);
    expect($flow->isStepCompleted('billing'))->toBeTrue();
    expect($flow->isStepCompleted('documents'))->toBeTrue();
    expect($flow->isCompleted())->toBeTrue();

    Event::assertDispatched(OnboardingStarted::class, fn($e) =>
        $e->user === $user && $e->flow->context() === $company
    );
});
