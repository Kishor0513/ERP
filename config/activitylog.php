<?php

use App\Models\ActivityLog;

return [

    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    'delete_records_older_than_days' => 365,

    'default_log_name' => 'default',

    'subject_returns_soft_deleted_models' => true,

    'table_name' => 'activity_log',

    'subject_model' => ActivityLog::class,

    'default_auth_driver' => null,

    'subject_returns_soft_deleted_models' => true,

];
