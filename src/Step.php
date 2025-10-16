<?php

namespace Dgtlinf\UserOnboarding;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Represents a single onboarding step within a flow.
 *
 * Each step defines:
 * - A unique slug (identifier).
 * - A closure (`check`) that determines if the step is completed.
 * - Optional metadata for UI or descriptive purposes.
 *
 * The `check` closure receives two parameters:
 *  - The authenticated user instance.
 *  - An optional contextual object (e.g., company, team, or custom data array).
 *
 * Example:
 * ```php
 * Step::make('profile')
 *     ->check(fn($user) => filled($user->name))
 *     ->meta(['label' => 'Complete your profile']);
 * ```
 */
class Step
{
    /** @var string Unique identifier (slug) for this step. */
    public string $slug;

    /** @var Closure Callback used to determine if the step is completed. */
    protected Closure $check;

    /** @var array Arbitrary metadata attached to this step (e.g. label, icon). */
    protected array $meta = [];

    /**
     * Create a new step instance.
     *
     * @param  string  $slug  The unique identifier for the step.
     */
    public function __construct(string $slug)
    {
        $this->slug = $slug;

        // Default check closure always returns false until defined.
        $this->check = fn (Authenticatable $user, mixed $context = null) => false;
    }

    /**
     * Static factory method for creating a step.
     *
     * @param  string  $slug
     * @return static
     */
    public static function make(string $slug): static
    {
        return new static($slug);
    }

    /**
     * Define the check closure that determines whether this step is completed.
     *
     * The callback receives the authenticated user and an optional context object.
     *
     * @param  callable(Authenticatable, mixed): bool  $callback
     * @return static
     */
    public function check(callable $callback): static
    {
        $this->check = $callback;
        return $this;
    }

    /**
     * Attach arbitrary metadata to the step.
     *
     * @param  array<string, mixed>  $meta
     * @return static
     */
    public function meta(array $meta): static
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Determine whether this step is completed for the given user and context.
     *
     * @param  Authenticatable  $user
     * @param  mixed|null  $context  Optional contextual data (e.g. company, team, or any object/array).
     * @return bool
     */
    public function isCompleted(Authenticatable $user, mixed $context = null): bool
    {
        return (bool) call_user_func($this->check, $user, $context);
    }

    /**
     * Retrieve all metadata associated with this step.
     *
     * @return array<string, mixed>
     */
    public function metaData(): array
    {
        return $this->meta;
    }
}
