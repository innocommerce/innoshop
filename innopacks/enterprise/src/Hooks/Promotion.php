<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise\Hooks;

class Promotion extends Base
{
    /**
     * @return void
     */
    public function init(): void
    {
        $this->addPanelPromotionMenu();
    }

    /**
     * @return void
     */
    private function addPanelPromotionMenu(): void
    {
        listen_hook_filter('component.sidebar.menus', function ($data) {
            $data[3] = [
                'title'    => '促销',
                'icon'     => 'bi-gift',
                'children' => [
                    [
                        'route' => 'volume_prices.index',
                        'title' => '批发价',
                    ],
                ],
            ];

            return $data;
        });
    }
}
