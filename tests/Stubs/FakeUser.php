<?php

namespace Dgtlinf\UserOnboarding\Tests\Stubs;

use Illuminate\Contracts\Auth\Authenticatable;

class FakeUser implements Authenticatable
{
    public function getAuthIdentifierName() { return 'id'; }
    public function getAuthIdentifier() { return 1; }
    public function getAuthPassword() { return 'secret'; }
    public function getAuthPasswordName(): ?string { return 'password'; }
    public function getRememberToken() {}
    public function setRememberToken($value) {}
    public function getRememberTokenName() { return ''; }
}

