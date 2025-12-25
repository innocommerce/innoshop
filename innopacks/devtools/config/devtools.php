<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Template Path
    |--------------------------------------------------------------------------
    |
    | Path to the stub templates directory.
    |
    */
    'template_path' => __DIR__.'/../src/Templates',

    /*
    |--------------------------------------------------------------------------
    | Plugin Types
    |--------------------------------------------------------------------------
    |
    | Available plugin types for validation.
    |
    */
    'plugin_types' => [
        'feature',
        'marketing',
        'billing',
        'shipping',
        'fee',
        'social',
        'language',
        'translator',
        'intelli',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Files for Packaging
    |--------------------------------------------------------------------------
    |
    | Files and directories to exclude when creating packages.
    |
    */
    'exclude_patterns' => [
        '.git',
        '.gitignore',
        '.DS_Store',
        'node_modules',
        'vendor',
        '.idea',
        '.vscode',
        '*.log',
        '.env',
        '.env.*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gitea Configuration
    |--------------------------------------------------------------------------
    |
    | Gitea server URL and API token for repository operations.
    | Token can be set using: php artisan dev:set-gitea-token your_token
    | Token is stored in .env file (GITEA_TOKEN) or storage/app/.gitea_token
    |
    */
    'gitea_url' => env('GITEA_URL', 'https://innoshop.work'),
    'gitea_token' => env('GITEA_TOKEN'),
];

