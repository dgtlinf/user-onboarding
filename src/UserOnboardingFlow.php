<?php

namespace Dgtlinf\UserOnboarding;

use Dgtlinf\UserOnboarding\Events\OnboardingCompleted;
use Dgtlinf\UserOnboarding\Events\StepCompleted;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class UserOnboardingFlow
{
    protected Authenticatable $user;
    /** @var Collection<int, Step> */
    protected Collection $steps;
    /** @var array<string,bool> $manualCompletions */
    protected array $manualCompletions = [];

    public function __construct(Authenticatable $user, array $steps = [])
    {
        $this->user = $user;
        $this->steps = collect($steps);
    }

    public function addStep(Step $step): static
    {
        $this->steps->push($step);
        return $this;
    }

    public function steps(): Collection
    {
        return $this->steps;
    }

    public function completedSteps(): Collection
    {
        return $this->steps->filter(fn (Step $step) => $this->isStepCompleted($step->slug));
    }

    public function incompleteSteps(): Collection
    {
        return $this->steps->reject(fn (Step $step) => $this->isStepCompleted($step->slug));
    }

    public function current(): ?Step
    {
        return $this->incompleteSteps()->first();
    }

    public function isCompleted(): bool
    {
        return $this->incompleteSteps()->isEmpty();
    }

    public function progress(): float
    {
        $total = $this->steps->count();
        return $total > 0
            ? round(($this->completedSteps()->count() / $total) * 100, 2)
            : 0.0;
    }

    public function findStep(string $slug): Step
    {
        $step = $this->steps->firstWhere('slug', $slug);
        if (! $step) {
            throw new InvalidArgumentException("Step [{$slug}] not found in flow.");
        }
        return $step;
    }

    public function isStepCompleted(string $slug): bool
    {
        $step = $this->findStep($slug);

        if ($this->manualCompletions[$slug] ?? false) {
            return true;
        }

        return (bool) $step->isCompleted($this->user);
    }

    /**
     * Ručno označava step kao završen i šalje event.
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

    public function user(): Authenticatable
    {
        return $this->user;
    }
}
