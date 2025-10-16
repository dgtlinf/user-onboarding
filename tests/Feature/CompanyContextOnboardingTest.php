<?php

use Dgtlinf\UserOnboarding\Step;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeUser;
use Dgtlinf\UserOnboarding\Tests\Stubs\FakeCompany;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    FakeCompany::$instances = [];

    Route::bind('company', fn($value) => FakeCompany::$instances[$value] ?? new FakeCompany($value));

    config()->set('user-onboarding.flows.company_setup', [
        Step::make('has_name')->check(fn($user, $company) => $company->hasName()),
        Step::make('setup_complete')->check(fn($user, $company) => $company->isSetupComplete()),
    ]);

    config()->set('user-onboarding.redirects.company_setup', '/onboarding/{company}');

    Route::middleware(['web', 'onboarding.step::company_setup'])
        ->get('/company/{company}/dashboard', fn() => 'OK');
});

it('redirects to onboarding when company setup is incomplete', function () {
    $user = new FakeUser();
    $company = new FakeCompany('cmp_123', null);

    FakeCompany::$instances[$company->id] = $company;

    $this->actingAs($user)
        ->get("/company/{$company->id}/dashboard")
        ->assertRedirect("/onboarding/{$company->id}");
});

it('allows access when company setup is complete', function () {
    $user = new FakeUser();
    $company = new FakeCompany('cmp_999', 'Acme Ltd');

    FakeCompany::$instances[$company->id] = $company;

    $this->actingAs($user)
        ->get("/company/{$company->id}/dashboard")
        ->assertOk();
});
