<?php

namespace Dgtlinf\UserOnboarding;

use Dgtlinf\UserOnboarding\Events\OnboardingStarted;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

class UserOnboardingManager
{
    public function for(Authenticatable $user): UserOnboardingFlow
    {
        return new UserOnboardingFlow($user);
    }

    public function start(Authenticatable $user, string $flowName = 'default'): UserOnboardingFlow
    {
        $flow = $this->makeFlowFromConfig($user, $flowName);
        event(new OnboardingStarted($user, $flow));
        return $flow;
    }

    protected function makeFlowFromConfig(Authenticatable $user, string $flowName): UserOnboardingFlow
    {
        $flows = config('user-onboarding.flows', []);

        if (! isset($flows[$flowName]) || ! is_array($flows[$flowName])) {
            throw new InvalidArgumentException("Onboarding flow [{$flowName}] is not defined in configuration.");
        }

        $steps = $flows[$flowName];
        return new UserOnboardingFlow($user, $steps);
    }
}
