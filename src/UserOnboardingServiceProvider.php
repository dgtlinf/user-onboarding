<?php

namespace Dgtlinf\UserOnboarding;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class UserOnboardingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('user-onboarding')
            ->hasConfigFile()
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('dgtlinf/user-onboarding');
            });
    }

    public function packageBooted(): void
    {
        $this->app['router']->aliasMiddleware(
            'onboarding.step',
            \Dgtlinf\UserOnboarding\Http\Middleware\EnsureUserOnboardingStepCompleted::class
        );
    }

    public function packageRegistered(): void
    {
        // Here we can register custom logic or bindings after the package is booted
        $this->registerBindings();
    }


    protected function registerBindings(): void
    {
        $this->app->singleton(UserOnboardingManager::class);
        $this->app->alias(UserOnboardingManager::class, 'user-onboarding');
    }
}
