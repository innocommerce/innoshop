<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InnoChat;

class Boot
{
    /**
     * @return void
     */
    public function init(): void
    {
        listen_hook_filter('panel.component.sidebar.setting.routes', function ($data) {
            $data[] = [
                'route' => 'openai.index',
                'title' => 'AI 助手',
            ];

            return $data;
        });
    }
}
