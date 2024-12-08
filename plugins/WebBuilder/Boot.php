<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder;

use InnoShop\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    /**
     * @return void
     */
    public function init(): void
    {
        $this->addPanelMenus();
    }

    /**
     * @return void
     */
    private function addPanelMenus(): void
    {
        listen_hook_filter('component.sidebar.design.routes', function ($data) {
            $data[] = [
                'route' => 'web_builder.index',
                'title' => trans('WebBuilder::route.title'),
                'blank' => true,
            ];

            return $data;
        });
    }
}
