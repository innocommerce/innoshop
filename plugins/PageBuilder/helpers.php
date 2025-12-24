<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
if (! function_exists('pb_get_bootstrap_columns')) {
    /**
     * Get Bootstrap column classes
     *
     * @param  int  $columns  Number of columns
     * @return string
     */
    function pb_get_bootstrap_columns(int $columns): string
    {
        switch ($columns) {
            case 3:
                return 'col-6 col-md-6 col-lg-4';
            case 4:
                return 'col-6 col-md-4 col-lg-3';
            case 6:
                return 'col-4 col-md-3 col-lg-2';
            default:
                return 'col-6 col-md-4 col-lg-3';
        }
    }
}

if (! function_exists('pb_get_width_class')) {
    /**
     * Get width class name
     *
     * @param  string|null  $width  Width value
     * @return string
     */
    function pb_get_width_class(?string $width = 'wide'): string
    {
        switch ($width) {
            case 'full':
                return 'swiper-fullscreen';
            case 'wide':
                return 'container-fluid';
            case 'narrow':
                return 'container';
            default:
                return 'container';
        }
    }
}
