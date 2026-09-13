<?php

return [

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => [
        'connection' => env('SCOUT_QUEUE_CONNECTION'),
        'queue' => env('SCOUT_QUEUE', 'scout'),
    ],

    'fudge_factor' => 10,

    'limit' => 500,

    'soft_delete' => false,

    'with' => [],

    'conjunctive_soft_deletes' => false,

    'types' => [
        'name' => [
            'searchable_attributes' => ['name', 'email'],
            'filterable_attributes' => ['name', 'email'],
            'sortable_attributes' => ['name', 'email'],
            'ranking_rules' => ['words', 'typo', 'sort', 'exactness'],
        ],
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            // 'users' => [
            //     'searchableAttributes' => [
            //         'name', 'email',
            //     ],
            // ],
        ],
    ],

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    'collection' => [
        'name' => function ($model) {
            return $model->searchableAs();
        },
        'queue' => function ($model) {
            return $model->shouldBeSearchable() && Scout::shouldQueue();
        },
    ],

];
