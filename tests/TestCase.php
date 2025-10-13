<?php

namespace Dgtlinf\UserOnboarding\Tests;

use Dgtlinf\UserOnboarding\UserOnboardingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            UserOnboardingServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

    }
}
