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
     */
    public function handle(Request $request, Closure $next, ?string $requiredStep = null): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        // Always build the flow from config so we have real steps
        $flow = UserOnboarding::start($user, 'default');

        // If a specific step is required
        if ($requiredStep && ! $flow->isStepCompleted($requiredStep)) {
            return $this->deny($request);
        }

        // If no specific step required, ensure the full onboarding is done
        if (! $requiredStep && ($flow->steps()->isEmpty() || ! $flow->isCompleted())) {
            return $this->deny($request);
        }

        return $next($request);
    }

    /**
     * Return the correct denial response (redirect or JSON 403).
     */
    protected function deny(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Onboarding not completed.'], 403);
        }

        $redirectTo = config('user-onboarding.redirect_to', '/onboarding');
        return redirect($redirectTo);
    }
}
