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
     * 获取Bootstrap列类名
     *
     * @param  int  $columns  列数
     * @return string
     */
    function pb_get_bootstrap_columns(int $columns): string
    {
        switch ($columns) {
            case 3:
                return 'col-6 col-md-6 col-lg-4'; // 手机2列，平板2列，桌面3列
            case 4:
                return 'col-6 col-md-4 col-lg-3'; // 手机2列，平板3列，桌面4列
            case 6:
                return 'col-4 col-md-3 col-lg-2'; // 手机3列，平板4列，桌面6列
            default:
                return 'col-6 col-md-4 col-lg-3'; // 默认4列布局
        }
    }
}

if (! function_exists('pb_get_width_class')) {
    /**
     * 获取宽度类名
     *
     * @param  string|null  $width  宽度值
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
