<?php

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\TemporaryFileUpload\DefaultTemporaryFileUploader;

return [

    'disk_name' => env('MEDIA_DISK', 'public'),

    'disk_names' => [
        'public' => 'public',
        'media' => 'media',
    ],

    'max_file_size' => 1024 * 1024 * 10,

    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),

    'queue_name' => '',

    'queue_priority' => 0,

    'responsable' => true,

    'path_generator' => null,

    'maintain_history' => false,

    'urls_are_descriptions' => false,

    'custom_attribute_getters' => [],

    'enable_temporary_uploads_maintenance' => true,

    'media_model' => Media::class,

    'temporary_file_uploader' => DefaultTemporaryFileUploader::class,

];
