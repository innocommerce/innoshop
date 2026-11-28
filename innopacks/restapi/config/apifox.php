<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'token'            => env('APIFOX_TOKEN'),
    'front_project_id' => env('APIFOX_FRONT_PROJECT_ID'),
    'panel_project_id' => env('APIFOX_PANEL_PROJECT_ID'),
    'keep_unmatched'   => env('APIFOX_KEEP_UNMATCHED', false),
    'api_base_url'     => env('APIFOX_API_BASE_URL', 'https://api.apifox.com'),
];
