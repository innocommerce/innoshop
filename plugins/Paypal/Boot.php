<?php

/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Paypal;

use Exception;
use Plugin\Paypal\Services\PaypalService;
use Throwable;

class Boot
{
    /**
     * https://uniapp.dcloud.net.cn/tutorial/app-payment-paypal.html
     *
     * @throws Exception
     * @throws Throwable
     */
    public function init(): void
    {
        listen_hook_filter('service.payment.mobile_pay.data', function ($data) {
            $order = $data['order'];
            if ($order->payment_method_code != 'paypal') {
                return $data;
            }

            $data['params'] = (new PaypalService($order))->getMobilePaymentData();

            return $data;
        });
    }
}
