<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [

    'models' => [
        'permission' => Permission::class,
        'role' => Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'model_morph_key' => 'model_id',
        'role_morph_key' => 'role_id',
    ],

    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'teams' => false,

    'cache' => [
        'enabled' => env('PERMISSION_CACHE_ENABLED', true),
        'store' => env('PERMISSION_CACHE_STORE', 'default'),
        'key' => env('PERMISSION_CACHE_KEY', 'spatie.permission.cache'),
        'prefix' => env('PERMISSION_CACHE_PREFIX', 'spatie.permission.cache'),
        'ttl' => env('PERMISSION_CACHE_TTL', 60 * 60),
    ],
];
