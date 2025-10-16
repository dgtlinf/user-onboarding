# User Onboarding for Laravel 10+

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dgtlinf/user-onboarding.svg?style=flat-square)](https://packagist.org/packages/dgtlinf/user-onboarding)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dgtlinf/user-onboarding/run-tests.yml?branch=main\&label=tests\&style=flat-square)](https://github.com/dgtlinf/user-onboarding/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/dgtlinf/user-onboarding.svg?style=flat-square)](https://packagist.org/packages/dgtlinf/user-onboarding)
[![License](https://img.shields.io/github/license/dgtlinf/user-onboarding.svg?style=flat-square)](LICENSE.md)
![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue?style=flat-square)
![Laravel Version](https://img.shields.io/badge/Laravel-%5E10-orange?style=flat-square)

A lightweight, stateless **user onboarding flow manager** for Laravel applications. Define onboarding steps entirely in code or in configuration, use middleware to restrict access, and listen to onboarding events — all without a database.

---

## 🚀 Installation

Install the package via Composer:

```bash
composer require dgtlinf/user-onboarding
```

Then publish the config file and installation assets using the included install command:

```bash
php artisan user-onboarding:install
```

This will publish the configuration file:

```
config/user-onboarding.php
```

---

## ⚙️ Defining Flows

You can define your onboarding steps in **two different ways** — depending on whether your flow is static (defined in `config`) or dynamic (defined programmatically).

### 1. Config-Based Flows (Recommended)

This is the most common approach. Define your flows in `config/user-onboarding.php`:

```php
use Dgtlinf\UserOnboarding\Step;

return [
    'flows' => [
        'default' => [
            Step::make('profile')->check(fn($user) => filled($user->name)),
            Step::make('verify_email')->check(fn($user) => $user->hasVerifiedEmail()),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding Redirects
    |--------------------------------------------------------------------------
    |
    | Define where users should be redirected when onboarding is incomplete
    | for a specific flow. The 'default' route is used if none match.
    |
    */
    'redirects' => [
        'default' => '/onboarding',
    ],
];
```

The middleware and facade will automatically use these definitions:

```php
$flow = UserOnboarding::start($user, 'default');
```

### 2. Dynamic Flows (Programmatic)

You can also define steps directly in code — perfect for custom setups, role-based flows, or feature flags.

#### In a Controller

```php
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;

public function onboarding()
{
    $user = auth()->user();

    $flow = UserOnboarding::for($user)
        ->addStep(Step::make('fill_profile')->check(fn($u) => filled($u->name)))
        ->addStep(Step::make('upload_avatar')->check(fn($u) => $u->avatar !== null));

    return inertia('Onboarding/Index', [
        'steps' => $flow->steps(),
        'currentStep' => $flow->current()?->slug,
        'progress' => $flow->progress(),
    ]);
}
```


#### In AppServiceProvider (Global Logic)

You can register a reusable macro for dynamic onboarding flows:

```php
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;

public function boot()
{
    UserOnboarding::macro('dynamicFlow', function ($user) {
        $flow = UserOnboarding::for($user)
            ->addStep(Step::make('profile')->check(fn($u) => filled($u->name)))
            ->addStep(Step::make('verify_email')->check(fn($u) => $u->hasVerifiedEmail()));

        if ($user->is_team_owner) {
            $flow->addStep(Step::make('invite_members')->check(fn($u) => $u->team->members->count() > 1));
        }

        return $flow;
    });
}
```

Now anywhere in your app, you can call:

```php
UserOnboarding::dynamicFlow($user)->progress();
```

---

## 🧬 Basic Usage

```php
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Dgtlinf\UserOnboarding\Step;

$user = auth()->user();

$flow = UserOnboarding::for($user)
    ->addStep(Step::make('profile')->check(fn($u) => $u->profileCompleted()))
    ->addStep(Step::make('verify_email')->check(fn($u) => $u->hasVerifiedEmail()));

if ($flow->isCompleted()) {
    // continue to dashboard
}

$flow->progress(); // e.g. 50.0
```

If you prefer configuration-based flows, use:

```php
$flow = UserOnboarding::start($user, 'default');
```

---

## 🛡️ Middleware Protection

You can easily protect routes to ensure users can only access them after completing onboarding.

### Protect Entire Routes

```php
Route::middleware('onboarding.step')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

If a user has not completed onboarding, they’ll be redirected to `/onboarding` (or your configured path).

### Require a Specific Step

```php
Route::middleware('onboarding.step:verify_email')->group(function () {
    Route::get('/projects', ProjectsController::class);
});
```

If the user hasn’t completed the `verify_email` step, the middleware denies access.

---

## 🤓 Creating a Custom Middleware

By default, this package includes the middleware
`Dgtlinf\\UserOnboarding\\Http\\Middleware\\EnsureUserOnboardingStepCompleted`.

You can register it manually or create your own to customize the behavior (for example, JSON vs. redirect responses).

### 1. Using the built-in middleware

Register it in your `app/Http/Kernel.php` if not already auto-discovered:

```php
protected $routeMiddleware = [
    'onboarding.step' => \\Dgtlinf\\UserOnboarding\\Http\\Middleware\\EnsureUserOnboardingStepCompleted::class,
];
```

Then use it in your routes:

```php
Route::middleware('onboarding.step')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

### 2. Creating your own middleware

If you need different behavior (e.g. API response instead of redirect),
you can create your own middleware and use the package’s API directly:

```bash
php artisan make:middleware EnsureOnboardingForApi
```

```php
namespace App\\Http\\Middleware;

use Closure;
use Dgtlinf\\UserOnboarding\\Facades\\UserOnboarding;

class EnsureOnboardingForApi
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        // Use a specific flow if needed
        $flow = UserOnboarding::start($user, 'default');

        if (! $flow->isCompleted()) {
            // For APIs, return a JSON response instead of redirect
            return response()->json([
                'message' => 'User onboarding not completed',
                'next_step' => $flow->current()?->slug,
                'progress' => $flow->progress(),
            ], 403);
        }

        return $next($request);
    }
}
```

Then register and use it:

```php
Route::middleware('onboarding.api')->get('/api/profile', [ProfileController::class, 'show']);
```

✅ **Tip:**
You can also use this pattern to create per-role or per-guard middleware variants:

```php
UserOnboarding::start($user, $user->isAdmin() ? 'admin' : 'default');
```

---

## 🧬 Example Workflow (Blade or Inertia)

When a user tries to access a protected route, the middleware redirects them to `/onboarding`. You can use the flow object to determine which step to render next.

### Controller Example

```php
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;

public function show()
{
    $user = auth()->user();

    $flow = UserOnboarding::start($user, 'default');

    return inertia('Onboarding/Index', [
        'currentStep' => $flow->current()?->slug,
        'steps' => $flow->steps()->map(fn($s) => [
            'slug' => $s->slug,
            'completed' => $flow->isStepCompleted($s->slug),
        ])->values(),
        'progress' => $flow->progress(),
    ]);
}
```

### Inertia/Vue Example

```vue
<template>
    <div class="max-w-lg mx-auto mt-10">
        <h2 class="text-2xl font-bold mb-4">Onboarding Progress</h2>
        <progress :value="progress" max="100" class="w-full mb-4"></progress>

        <div v-for="step in steps" :key="step.slug" class="mb-2">
      <span
          class="inline-block w-3 h-3 rounded-full mr-2"
          :class="step.completed ? 'bg-green-500' : 'bg-gray-400'"
      ></span>
            {{ step.slug }}
        </div>

        <div class="mt-6">
            <component :is="getStepComponent(currentStep)" v-if="currentStep" />
        </div>
    </div>
</template>

<script setup>
    defineProps({ currentStep: String, steps: Array, progress: Number })

    function getStepComponent(slug) {
        switch (slug) {
            case 'profile':
                return 'OnboardingProfileStep'
            case 'verify_email':
                return 'OnboardingVerifyEmailStep'
            default:
                return 'OnboardingDone'
        }
    }
</script>
```

### Blade Example

```blade
@php
    $flow = UserOnboarding::start(auth()->user());
    $current = $flow->current()?->slug;
@endphp

@if ($current === 'profile')
    @include('onboarding.steps.profile')
@elseif ($current === 'verify_email')
    @include('onboarding.steps.verify-email')
@else
    <p>All done! 🎉</p>
@endif
```

When a user completes a step (e.g., submits a form):

```php
UserOnboarding::for($user)->completeStep('profile');
```

The next time the onboarding view loads, the next step will automatically render.

---

## 🧱 Listening to Events

The package dispatches the following events automatically:

| Event                 | Description                                                 |
| --------------------- | ----------------------------------------------------------- |
| `OnboardingStarted`   | Fired when a flow begins via `UserOnboarding::start()`      |
| `StepCompleted`       | Fired when a step is completed manually or programmatically |
| `OnboardingCompleted` | Fired when all steps are completed                          |

### Example Listener

```php
use Dgtlinf\UserOnboarding\Events\StepCompleted;

Event::listen(StepCompleted::class, function ($event) {
    Log::info('Step completed', [
        'user' => $event->user->id,
        'step' => $event->step->slug,
    ]);
});
```

---

## 📡 Events Overview

* `OnboardingStarted` → emitted when a user begins onboarding
* `StepCompleted` → emitted when a user finishes a step
* `OnboardingCompleted` → emitted when a flow is fully done

Each event carries the `$user` and `$flow` (and `$step` when relevant).

---

## 🥉 Example Use Cases

* Block certain routes until user setup is finished
* Show onboarding progress bar in the UI
* Trigger reminders via queued listeners
* Log onboarding analytics and completions
* Connect with external CRM or email campaigns

---

## ⚙️ Publishing & Customization

You can re-publish configuration anytime:

```bash
php artisan vendor:publish --tag="user-onboarding-config"
```

---

## 🧮 Tech Notes

* **Stateless**: No database persistence — each step is evaluated live.
* **Extensible**: Add events, listeners, and custom middleware.
* **Minimal**: Only `spatie/laravel-package-tools` and `illuminate/support` are required.

---

## 🖦 License

MIT License © [Digital Infinity](https://digitalinfinity.rs)
