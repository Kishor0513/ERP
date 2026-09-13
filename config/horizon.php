<?php

use Laravel\Horizon\Http\Middleware\Authenticate;
use Laravel\Horizon\Http\Middleware\Authorize;
use Laravel\Horizon\Http\Middleware\PreventRequestsFromMaintenance;

return [

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'queue_length',
                'maxProcesses' => 10,
                'maxTime' => 3600,
                'maxJobs' => 1000,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 90,
                'nice' => 0,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'autoScalingStrategy' => 'queue_length',
                'maxProcesses' => 3,
                'maxTime' => 3600,
                'maxJobs' => 1000,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 90,
                'nice' => 0,
            ],
        ],
    ],

    'default' => env('HORIZON_PREFIX', 'horizon:'),

    'prefix' => [
        'environment' => 'horizon:',
        'command' => 'horizon:',
        'supervisor' => 'horizon:supervisor:',
        'supervisors' => 'horizon:supervisors:',
        'scheduled' => 'horizon:scheduled:',
        'metrics' => 'horizon:metrics:',
        'balance' => 'horizon:balance:',
        'minimum_balance' => 'horizon:minimum_balance:',
        'trim' => 'horizon:trim:',
        'failed' => 'horizon:failed:',
    ],

    'middleware' => [
        'web',
        Authenticate::class,
        Authorize::class,
        PreventRequestsFromMaintenance::class,
    ],

    'auth' => [
        'guards' => ['web'],
    ],

    'paths' => [
        'paths' => [
            'app',
            'config',
            'database',
            'resources',
            'routes',
        ],
    ],

    'extra' => [
        'artisan' => [
            'blacklist' => [
                'horizon:assets',
                'horizon:check',
                'horizon:clear',
                'horizon:continuation',
                'horizon:css',
                'horizon:exit',
                'horizon:install',
                'horizon:list',
                'horizon:pulse',
                'horizon:purge',
                'horizon:status',
                'horizon:terminate',
            ],
        ],
    ],

];
