<?php

namespace Dgtlinf\UserOnboarding;

use Dgtlinf\UserOnboarding\Events\OnboardingStarted;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

/**
 * Entry point and factory for creating onboarding flows.
 *
 * The `UserOnboardingManager` is responsible for:
 * - Instantiating {@see UserOnboardingFlow} objects.
 * - Loading flow definitions from configuration.
 * - Dispatching lifecycle events (e.g. {@see OnboardingStarted}).
 *
 * Typically accessed via the `UserOnboarding` facade:
 * ```php
 * $flow = UserOnboarding::start($user, 'default');
 * ```
 */
class UserOnboardingManager
{
    /**
     * Create a new, empty onboarding flow for the given user.
     *
     * This is useful for building onboarding flows dynamically
     * without relying on configuration.
     *
     * Example:
     * ```php
     * $flow = UserOnboarding::for($user)
     *     ->addStep(Step::make('profile')->check(fn($u) => filled($u->name)));
     * ```
     *
     * @param  Authenticatable  $user
     * @return UserOnboardingFlow
     */
    public function for(Authenticatable $user): UserOnboardingFlow
    {
        return new UserOnboardingFlow($user);
    }

    /**
     * Start a named onboarding flow for the given user.
     *
     * Loads step definitions from `config/user-onboarding.php`
     * under the provided `$flowName`. Automatically dispatches
     * the {@see OnboardingStarted} event.
     *
     * Optionally, a context object (e.g. company, team, or array)
     * can be passed to provide additional data to each step.
     *
     * Example:
     * ```php
     * $flow = UserOnboarding::start($user, 'company_setup', $company);
     * ```
     *
     * @param  Authenticatable  $user  The user beginning onboarding.
     * @param  string  $flowName  The flow key defined in configuration.
     * @param  mixed|null  $context  Optional contextual data (company, project, etc.).
     * @return UserOnboardingFlow
     *
     * @throws InvalidArgumentException  If the requested flow is not defined.
     */
    public function start(Authenticatable $user, string $flowName = 'default', mixed $context = null): UserOnboardingFlow
    {
        $flow = $this->makeFlowFromConfig($user, $flowName, $context);

        event(new OnboardingStarted($user, $flow));

        return $flow;
    }

    /**
     * Internal helper to create a flow instance using configuration data.
     *
     * @param  Authenticatable  $user
     * @param  string  $flowName
     * @param  mixed|null  $context
     * @return UserOnboardingFlow
     *
     * @throws InvalidArgumentException  If the flow name is not found in config.
     */
    protected function makeFlowFromConfig(Authenticatable $user, string $flowName, mixed $context = null): UserOnboardingFlow
    {
        $flows = config('user-onboarding.flows', []);

        if (! isset($flows[$flowName]) || ! is_array($flows[$flowName])) {
            throw new InvalidArgumentException("Onboarding flow [{$flowName}] is not defined in configuration.");
        }

        $steps = $flows[$flowName];

        return new UserOnboardingFlow($user, $steps, $context);
    }
}
