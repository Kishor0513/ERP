<?php

namespace App\Http\Middleware;

use App\Support\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        $organization = CurrentOrganization::resolve();

        if (! $organization) {
            return response()->json(['success' => false, 'message' => 'No organization selected.'], 422);
        }

        if (! $organization->is_active) {
            return response()->json(['success' => false, 'message' => 'Organization is suspended.'], 403);
        }

        $trialDays = (int) config('saas.trial_days', 14);
        $requireSubscription = config('saas.require_subscription', false);

        if (! $requireSubscription) {
            return $next($request);
        }

        if ($organization->hasActiveSubscription()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Active subscription required.',
            'data' => [
                'trial_days' => $trialDays,
                'billing_url' => '/api/v1/billing',
            ],
        ], 402);
    }
}
