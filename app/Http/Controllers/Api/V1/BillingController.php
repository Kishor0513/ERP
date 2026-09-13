<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Support\CurrentOrganization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends BaseController
{
    public function status(): JsonResponse
    {
        $org = CurrentOrganization::resolve();

        abort_unless($org, 422, 'No organization selected.');

        return $this->sendResponse([
            'organization_id' => $org->id,
            'on_trial' => $org->onTrial(),
            'trial_ends_at' => $org->trial_ends_at,
            'subscribed' => method_exists($org, 'subscribed') ? $org->subscribed('default') : false,
            'subscription' => method_exists($org, 'subscription') ? $org->subscription('default') : null,
            'plans' => config('saas.plans'),
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $org = CurrentOrganization::resolve();

        abort_unless($org, 422, 'No organization selected.');

        $validated = $request->validate([
            'plan' => 'required|in:starter,growth,enterprise',
            'interval' => 'required|in:monthly,yearly',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        $priceKey = $validated['interval'] === 'yearly' ? 'stripe_price_yearly' : 'stripe_price_monthly';
        $priceId = config("saas.plans.{$validated['plan']}.{$priceKey}");

        abort_unless($priceId, 422, 'Plan price not configured.');

        $checkout = $org->newSubscription('default', $priceId)
            ->trialUntil($org->trial_ends_at ?? now()->addDays((int) config('saas.trial_days', 14)))
            ->checkout([
                'success_url' => $validated['success_url'],
                'cancel_url' => $validated['cancel_url'],
            ]);

        return $this->sendResponse(['url' => $checkout->url], 'Checkout created');
    }

    public function portal(Request $request): JsonResponse
    {
        $org = CurrentOrganization::resolve();

        abort_unless($org, 422, 'No organization selected.');

        $validated = $request->validate(['return_url' => 'required|url']);

        return $this->sendResponse([
            'url' => $org->billingPortalUrl($validated['return_url']),
        ]);
    }
}
