<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise\Hooks;

class Currency extends Base
{
    public function init()
    {
        $this->addCurrencyButton();
    }

    private function addCurrencyButton()
    {
        listen_blade_insert('panel.layout.right.button.after', function ($data) {
            if (! request()->routeIs('panel.currencies.index')) {
                return '';
            }

            //            return '22';
            return view('enterprise::panel.currencies.update_rate_button');
            $url = 'https://v6.exchangerate-api.com/v6/7e83297ecc54d8098f478d7c/latest/CNY';
        });
    }
}
