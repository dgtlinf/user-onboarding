<?php

use Dgtlinf\UserOnboarding\Step;

return [

    /*
    |--------------------------------------------------------------------------
    | User Onboarding Flows
    |--------------------------------------------------------------------------
    |
    | Here you can define one or more onboarding flows.
    | Each flow is a list of steps defined as instances of the Step class.
    | Every step should have a unique slug and a check callback that determines
    | if the step is completed for a given user.
    |
    | Example:
    |
    | 'flows' => [
    |     'default' => [
    |         Step::make('profile')
    |             ->check(fn($user) => filled($user->name))
    |             ->meta(['label' => 'Complete your profile']),
    |
    |         Step::make('verify_email')
    |             ->check(fn($user) => $user->hasVerifiedEmail())
    |             ->meta(['label' => 'Verify your email']),
    |
    |         Step::make('invite_team')
    |             ->check(fn($user) => $user->invitations()->count() > 0)
    |             ->meta(['label' => 'Invite your team']),
    |     ],
    |
    |     'team' => [
    |         Step::make('setup_workspace')
    |             ->check(fn($user) => $user->workspaceConfigured()),
    |
    |         Step::make('add_members')
    |             ->check(fn($user) => $user->members()->count() > 1),
    |     ],
    | ],
    |
    */

    'flows' => [],

    /*
    |--------------------------------------------------------------------------
    | Redirect Path
    |--------------------------------------------------------------------------
    |
    | When a user tries to access a protected route but has not completed
    | their onboarding, they will be redirected to this path.
    | For API or JSON requests, the middleware will respond with HTTP 403.
    |
    | Example:
    | 'redirect_to' => '/onboarding',
    |
    */

    'redirect_to' => '/onboarding',
];
