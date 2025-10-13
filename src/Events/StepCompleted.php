<?php

namespace Dgtlinf\UserOnboarding\Events;

use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\UserOnboardingFlow;
use Illuminate\Contracts\Auth\Authenticatable;

class StepCompleted
{
    public Authenticatable $user;
    public Step $step;
    public UserOnboardingFlow $flow;

    public function __construct(Authenticatable $user, Step $step, UserOnboardingFlow $flow)
    {
        $this->user = $user;
        $this->step = $step;
        $this->flow = $flow;
    }
}
