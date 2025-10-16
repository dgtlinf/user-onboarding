<?php

namespace Dgtlinf\UserOnboarding;

use Dgtlinf\UserOnboarding\Events\OnboardingCompleted;
use Dgtlinf\UserOnboarding\Events\StepCompleted;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Represents a single user's onboarding flow.
 *
 * A flow consists of multiple {@see Step} instances that define
 * what needs to be completed before onboarding is considered done.
 *
 * The flow can be created manually (via {@see UserOnboardingManager::for()})
 * or from configuration (via {@see UserOnboardingManager::start()}).
 *
 * Each flow is *stateless*: completion is evaluated dynamically
 * using closures defined in each step or manual completions tracked at runtime.
 */
class UserOnboardingFlow
{
    /** @var Authenticatable The user associated with this onboarding flow. */
    protected Authenticatable $user;

    /** @var Collection<int, Step> The collection of steps defined for this flow. */
    protected Collection $steps;

    /** @var array<string,bool> Tracks manually completed steps by slug. */
    protected array $manualCompletions = [];

    /** @var mixed Optional context (e.g. company, team, or array of data) shared across steps. */
    protected mixed $context = null;

    /**
     * Create a new onboarding flow instance.
     *
     * @param  Authenticatable  $user  The user undergoing onboarding.
     * @param  array<int, Step>  $steps  The ordered list of steps in the flow.
     * @param  mixed|null  $context  Optional context object (e.g. company or project).
     */
    public function __construct(Authenticatable $user, array $steps = [], mixed $context = null)
    {
        $this->user = $user;
        $this->steps = collect($steps);
        $this->context = $context;
    }

    /**
     * Retrieve the context object associated with this flow.
     *
     * @return mixed|null
     */
    public function context(): mixed
    {
        return $this->context;
    }

    /**
     * Add a new step to the onboarding flow dynamically.
     *
     * @param  Step  $step
     * @return static
     */
    public function addStep(Step $step): static
    {
        $this->steps->push($step);
        return $this;
    }

    /**
     * Get all steps within this onboarding flow.
     *
     * @return Collection<int, Step>
     */
    public function steps(): Collection
    {
        return $this->steps;
    }

    /**
     * Get all completed steps.
     *
     * @return Collection<int, Step>
     */
    public function completedSteps(): Collection
    {
        return $this->steps->filter(fn (Step $step) => $this->isStepCompleted($step->slug));
    }

    /**
     * Get all incomplete (remaining) steps.
     *
     * @return Collection<int, Step>
     */
    public function incompleteSteps(): Collection
    {
        return $this->steps->reject(fn (Step $step) => $this->isStepCompleted($step->slug));
    }

    /**
     * Retrieve the current (next) step to complete.
     *
     * @return Step|null
     */
    public function current(): ?Step
    {
        return $this->incompleteSteps()->first();
    }

    /**
     * Determine if all steps are completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->incompleteSteps()->isEmpty();
    }

    /**
     * Calculate completion progress as a percentage.
     *
     * @return float Value between 0.00 and 100.00
     */
    public function progress(): float
    {
        $total = $this->steps->count();

        return $total > 0
            ? round(($this->completedSteps()->count() / $total) * 100, 2)
            : 0.0;
    }

    /**
     * Find a specific step by slug.
     *
     * @param  string  $slug
     * @return Step
     *
     * @throws InvalidArgumentException If the step does not exist.
     */
    public function findStep(string $slug): Step
    {
        $step = $this->steps->firstWhere('slug', $slug);

        if (! $step) {
            throw new InvalidArgumentException("Step [{$slug}] not found in flow.");
        }

        return $step;
    }

    /**
     * Determine whether a specific step is completed.
     *
     * This method checks both the dynamic closure result and any
     * manually marked completions.
     *
     * @param  string  $slug
     * @return bool
     */
    public function isStepCompleted(string $slug): bool
    {
        $step = $this->findStep($slug);

        if ($this->manualCompletions[$slug] ?? false) {
            return true;
        }

        return (bool) $step->isCompleted($this->user, $this->context);
    }

    /**
     * Manually mark a step as completed and dispatch related events.
     *
     * - Fires {@see StepCompleted} immediately.
     * - Fires {@see OnboardingCompleted} if all steps are done.
     *
     * @param  string  $slug
     * @return void
     */
    public function completeStep(string $slug): void
    {
        $step = $this->findStep($slug);

        if ($this->isStepCompleted($slug)) {
            return;
        }

        $this->manualCompletions[$slug] = true;

        event(new StepCompleted($this->user, $step, $this));

        if ($this->isCompleted()) {
            event(new OnboardingCompleted($this->user, $this));
        }
    }

    /**
     * Retrieve the user associated with this onboarding flow.
     *
     * @return Authenticatable
     */
    public function user(): Authenticatable
    {
        return $this->user;
    }
}
