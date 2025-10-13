<?php

namespace Dgtlinf\UserOnboarding\Events;

use Dgtlinf\UserOnboarding\UserOnboardingFlow;
use Illuminate\Contracts\Auth\Authenticatable;

class OnboardingCompleted
{
    public Authenticatable $user;
    public UserOnboardingFlow $flow;

    public function __construct(Authenticatable $user, UserOnboardingFlow $flow)
    {
        $this->user = $user;
        $this->flow = $flow;
    }
}
