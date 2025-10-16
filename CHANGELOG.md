# Changelog


---

**Maintained by:** Digital Infinity DOO Novi Sad
**Website:** [digitalinfinity.rs](https://www.digitalinfinity.rs)

## v1.1.1 - 2025-10-16

### v1.1.1 – Context-Aware Onboarding Middleware

#### 🚀 New Features

- Added **context-aware onboarding middleware** that supports model binding and dynamic route placeholders.
- Redirects now automatically replace `{company}`, `{team}`, `{id}`, or any `{placeholder}` with the context model’s key.
- Fully supports multi-flow onboarding via `onboarding.step::flow_name`.

#### 🧠 Improvements

- Simplified and standardized context detection (works for any Eloquent-like model).
- Backward compatible with previous config syntax (`redirects` can still be simple strings).
- Cleaner and safer flow handling with JSON (403) or redirect fallback.

#### ✅ Example

```php
'redirects' => [
    'default' => '/onboarding',
    'company_setup' => '/onboarding/company/{company}',
    'vendor_flow'   => '/onboarding/vendor/{id}',
],

```
#### 🧩 Middleware Usage

```php
Route::middleware('onboarding.step::company_setup')->group(function () {
    Route::get('/company/{company}/dashboard', DashboardController::class);
});

```
This release introduces full context-awareness and flexible dynamic redirects, making onboarding flows compatible with any model-driven route structure.

## v1.1.0 - 2025-10-16

### v1.1.0 – Context-Aware Onboarding Flows

#### 🚀 New Features

- Added **context support** to onboarding flows.
  
  - You can now pass any contextual object (e.g. `Company`, `Team`, or array) when starting a flow:
    ```php
    $flow = UserOnboarding::start($user, 'company_setup', $company);
    
    
    ```
  - Each step’s `check` callback now receives both `$user` and `$context` arguments.
  
- All core classes updated to support context:
  
  - `Step::isCompleted($user, $context)`
  - `UserOnboardingFlow::__construct($user, $steps, $context)`
  - `UserOnboardingManager::start($user, $flowName, $context)`
  

#### 🧠 Improvements

- Full docblocks added for all major classes.
- Improved test coverage with new **Feature test** for context-based onboarding.
- Maintains full backward compatibility — existing flows without context work as before.

#### 🧩 Example

```php
Step::make('billing')
    ->check(fn($user, $company) => $company->hasValidBillingEntity());


```
## v1.0.1 - 2025-10-16

### v1.0.1

#### What's New

- Added support for **per-flow onboarding redirects** via new `redirects` config array.
- Updated README to reflect multi-flow redirect configuration.
- Improved overall config clarity and documentation.

#### Notes

This update ensures each onboarding flow can define its own redirect path, allowing more flexible user experience handling across different onboarding contexts.

## v1.0.0 - 2025-10-13

### 🎉 User Onboarding for Laravel — v1.0.0

Initial stable release of `dgtlinf/user-onboarding`, a lightweight, stateless onboarding flow manager for Laravel applications.

#### 🚀 Highlights

- Stateless design — no database or migrations required.
- Flow configuration via `config/user-onboarding.php`.
- Dynamic, code-based flows for roles, features, or custom logic.
- Smart middleware with automatic redirect or API responses.
- Inertia.js and Blade friendly — simple to integrate with UI.
- Event system (`OnboardingStarted`, `StepCompleted`, `OnboardingCompleted`).
- Auto middleware registration compatible up to Laravel 12.
- MIT licensed and production ready.

## [Unreleased]

- Initial setup and scaffolding.
