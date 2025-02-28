<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SubNews;

class Boot
{
    /**
     * @return void
     */
    public function init(): void
    {
        $this->addPanelMenus();
        $this->addSubscribeForm();
    }

    /**
     * @return void
     */
    private function addPanelMenus(): void
    {
        listen_hook_filter('panel.component.sidebar.customer.routes', function ($data) {
            $data[] = [
                'route' => 'sub_mails.index',
                'title' => trans('SubNews::common.sub_mails'),
            ];

            return $data;
        });
    }

    /**
     * @return void
     */
    private function addSubscribeForm(): void
    {
        listen_blade_insert('home.content.bottom', function ($data) {
            return view('SubNews::front.subscribe_form');
        });
    }
}
