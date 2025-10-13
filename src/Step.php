<?php

namespace Dgtlinf\UserOnboarding;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

class Step
{
    public string $slug;
    protected Closure $check;
    protected array $meta = [];

    public static function make(string $slug): static
    {
        return new static($slug);
    }

    public function __construct(string $slug)
    {
        $this->slug = $slug;
        $this->check = fn (Authenticatable $user) => false;
    }

    public function check(callable $callback): static
    {
        $this->check = $callback;
        return $this;
    }

    public function meta(array $meta): static
    {
        $this->meta = $meta;
        return $this;
    }

    public function isCompleted(Authenticatable $user): bool
    {
        return (bool) call_user_func($this->check, $user);
    }

    public function metaData(): array
    {
        return $this->meta;
    }
}
