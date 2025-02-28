<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ImageBot;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->addImageBotMenu();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function addImageBotMenu(): void
    {
        listen_hook_filter('panel.component.sidebar.product.routes', function ($data) {
            $data[] = [
                'route' => 'image_bot.index',
                'title' => 'AI 智绘',
            ];

            return $data;
        });
    }
}
