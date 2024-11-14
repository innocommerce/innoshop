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
        $this->addPanelFileManagerMenu();
    }

    /**
     * @return void
     */
    private function addPanelFileManagerMenu(): void
    {
        listen_hook_filter('component.sidebar.product.routes', function ($data) {
            $data[] = [
                'route' => 'file_manager.index',
                'title' => trans('enterprise::file_manager.title'),
            ];

            return $data;
        });
    }
}
