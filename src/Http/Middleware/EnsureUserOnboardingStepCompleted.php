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
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $requiredStep
     * @param  string|null  $flowName
     */
    public function handle(Request $request, Closure $next, ?string $requiredStep = null, ?string $flowName = 'default'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        $flowName ??= 'default';

        $flow = UserOnboarding::start($user, $flowName);

        // Check a specific step
        if ($requiredStep && ! $flow->isStepCompleted($requiredStep)) {
            return $this->deny($request, $flowName);
        }

        // Check full flow completion
        if (! $requiredStep && ($flow->steps()->isEmpty() || ! $flow->isCompleted())) {
            return $this->deny($request, $flowName);
        }

        return $next($request);
    }

    /**
     * Return correct denial response (JSON or redirect based on flow).
     */
    protected function deny(Request $request, string $flowName): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Onboarding not completed for flow: {$flowName}."
            ], 403);
        }

        $redirectTo = config("user-onboarding.redirects.{$flowName}")
            ?? config('user-onboarding.redirects.default', '/onboarding');

        return redirect($redirectTo);
    }
}
