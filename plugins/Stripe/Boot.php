<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Stripe;

use Plugin\Stripe\Services\StripeService;
use Stripe\Exception\ApiErrorException;

class Boot
{
    /**
     * https://uniapp.dcloud.net.cn/tutorial/app-payment-stripe.html
     *
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function init(): void
    {
        listen_hook_filter('service.payment.mobile_pay.data', function ($data) {
            $order = $data['order'];
            if ($order->payment_method_code != 'stripe') {
                return $data;
            }

            $data['params'] = (new StripeService($order))->getMobilePaymentData();

            return $data;
        });
    }
}
