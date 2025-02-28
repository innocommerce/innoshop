<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PayFilter;

use Plugin\PayFilter\Services\PaymentFilterService;
use Throwable;

class Boot
{
    /**
     * @return void
     * @throws Throwable
     */
    public function init(): void
    {
        listen_hook_filter('service.checkout.billing.methods', function ($data) {
            foreach ($data as $index => $item) {
                if (! PaymentFilterService::getInstance($item['code'])->checkValid()) {
                    unset($data[$index]);
                }
            }

            return array_values($data);
        });
    }
}
