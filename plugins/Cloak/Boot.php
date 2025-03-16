<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://github.com/qxsclass
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Cloak;

class Boot
{
    public function __construct() {}

    public function init(): void
    {
        // Add panel sidebar menu item
        listen_hook_filter('component.sidebar.plugin.routes', function ($data) {
            $data[] = [
                'route' => 'cloaks.index',
                'title' => __('Cloak::common.cloak'),
            ];

            return $data;
        });

    }
}
