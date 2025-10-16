<?php

namespace Dgtlinf\UserOnboarding\Tests;

use Dgtlinf\UserOnboarding\UserOnboardingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
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
