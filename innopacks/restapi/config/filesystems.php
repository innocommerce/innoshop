<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'file_manager' => [
        'driver' => env('FILE_MANAGER_DRIVER', 'local'),  // local, s3, oss
    ],

    'disks' => [
        'local' => [
            'driver'     => 'local',
            'root'       => public_path('catalog'),
            'url'        => env('APP_URL').'/catalog',
            'visibility' => 'public',
        ],
        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_URL'),
            'use_path_style_endpoint' => false,
            'bucket_endpoint'         => true,
            'scheme'                  => 'https',
            // OSS
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ],
    ],
];
