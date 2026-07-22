<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'edition' => 'community',
    'version' => '0.8.7',
    'build'   => '20260722',
    'api_url' => env('INNOSHOP_API_URL', 'https://www.innoshop.cn'),

    // Override GeoLite2-City.mmdb path via GEOLITE2_PATH; defaults to storage/app/geolite2/geolite2-city.mmdb.
    'geo_lite_path' => env('GEOLITE2_PATH', storage_path('app/geolite2/geolite2-city.mmdb')),
];
