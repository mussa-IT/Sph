<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user can access the feature
        if (!$user->canAccessFeature($feature)) {
            $this->handleFeatureGate($request, $feature, $user);
        }

        return $next($request);
    }

    private function handleFeatureGate(Request $request, string $feature, User $user): void
    {
        $currentPlan = $user->getPlan();
        $upgradeUrl = route('pricing') . '?feature=' . $feature;

        // For AJAX requests, return JSON response
        if ($request->expectsJson() || $request->ajax()) {
            abort(403, json_encode([
                'message' => 'This feature requires a paid subscription.',
                'feature' => $feature,
                'current_plan' => $currentPlan?->name ?? 'Free',
                'upgrade_url' => $upgradeUrl,
                'requires_upgrade' => true,
            ]));
        }

        // For regular requests, show upgrade prompt
        session()->flash('feature_gate', [
            'feature' => $feature,
            'current_plan' => $currentPlan?->name ?? 'Free',
            'upgrade_url' => $upgradeUrl,
        ]);

        abort(403, 'This feature requires a paid subscription.');
    }
}
