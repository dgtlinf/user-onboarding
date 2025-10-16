<?php

namespace Dgtlinf\UserOnboarding\Http\Middleware;

use Closure;
use Dgtlinf\UserOnboarding\Facades\UserOnboarding;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOnboardingStepCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $flow = 'default'): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Detect context
        $context = $this->resolveContext($request);

        // Create onboarding flow
        $flowInstance = UserOnboarding::start($user, ltrim($flow, ':'), $context);

        if ($flowInstance->isCompleted()) {
            return $next($request);
        }

        // Determine redirect for this flow
        $redirects = config('user-onboarding.redirects', []);
        $redirectPattern = $redirects[ltrim($flow, ':')] ?? $redirects['default'] ?? '/onboarding';

        // Inject context key if available
        if ($context && method_exists($context, 'getKey')) {
            $redirect = preg_replace('/\{[a-zA-Z_]+\}/', $context->getKey(), $redirectPattern);
        } else {
            $redirect = $redirectPattern;
        }

        // JSON or redirect response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Onboarding incomplete.'], 403);
        }

        return redirect($redirect);
    }

    /**
     * Resolve context model (from route bindings).
     */
    protected function resolveContext(Request $request): mixed
    {
        $route = $request->route();

        if (! $route) {
            return null;
        }

        foreach ($route->parameters() as $param) {
            if (is_object($param) && method_exists($param, 'getKey')) {
                return $param;
            }
        }

        return null;
    }
}
