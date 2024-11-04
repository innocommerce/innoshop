<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise\Hooks;

class Product extends Base
{
    /**
     * @return void
     */
    public function init(): void
    {
        $this->addPanelMenu();
    }

    /**
     * @return void
     */
    private function addPanelMenu(): void
    {
        listen_hook_filter('component.sidebar.product.routes', function ($menus) {
            $menus[] = [
                'title' => '导入导出',
                'route' => 'import.index',
            ];

            return $menus;
        });
    }
}
