<?php

namespace Dgtlinf\UserOnboarding\Tests\Stubs;

class FakeCompany
{
    public static array $instances = [];

    public string $id;
    public ?string $name = null;

    public function __construct(string $id, ?string $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getKey(): string
    {
        return $this->id;
    }

    public function hasName(): bool
    {
        return !empty($this->name);
    }

    public function isSetupComplete(): bool
    {
        return $this->hasName();
    }
}
