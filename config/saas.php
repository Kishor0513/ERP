<?php

return [
    'trial_days' => (int) env('SAAS_TRIAL_DAYS', 14),
    'require_subscription' => (bool) env('SAAS_REQUIRE_SUBSCRIPTION', false),

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'stripe_price_monthly' => env('STRIPE_PRICE_STARTER_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_STARTER_YEARLY'),
            'seats' => 5,
            'features' => ['catalog', 'inventory', 'sales'],
        ],
        'growth' => [
            'name' => 'Growth',
            'stripe_price_monthly' => env('STRIPE_PRICE_GROWTH_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_GROWTH_YEARLY'),
            'seats' => 25,
            'features' => ['catalog', 'inventory', 'sales', 'production', 'procurement'],
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'stripe_price_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_ENTERPRISE_YEARLY'),
            'seats' => -1,
            'features' => ['*'],
        ],
    ],
];
