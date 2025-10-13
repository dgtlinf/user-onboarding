<?php

namespace Dgtlinf\UserOnboarding\Facades;

use Illuminate\Support\Facades\Facade;

class UserOnboarding extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'user-onboarding';
    }
}
