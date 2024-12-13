<?php

/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Paypal\Services;

use Exception;
use InnoShop\Front\Services\PaymentService;
use Srmklive\PayPal\Services\PayPal;
use Throwable;

class PaypalService extends PaymentService
{
    public PayPal $paypalClient;

    /**
     * @param  $order
     * @throws Exception
     * @throws Throwable
     */
    public function __construct($order)
    {
        parent::__construct($order);
        $this->initPaypal();
    }

    /**
     * @return void
     * @throws Throwable
     */
    private function initPaypal(): void
    {
        $paypalSetting = plugin_setting('paypal');
        $config        = [
            'mode'    => $paypalSetting['sandbox_mode'] ? 'sandbox' : 'live',
            'sandbox' => [
                'client_id'     => $paypalSetting['sandbox_client_id'],
                'client_secret' => $paypalSetting['sandbox_secret'],
            ],
            'live' => [
                'client_id'     => $paypalSetting['live_client_id'],
                'client_secret' => $paypalSetting['live_secret'],
            ],
            'payment_action' => 'Sale',
            'currency'       => strtoupper(system_setting('currency')),
            'notify_url'     => '',
            'locale'         => 'en_US',
            'validate_ssl'   => false,
        ];
        config(['paypal' => null]);
        $this->paypalClient = new PayPal($config);
        $paypalAccessToken  = $this->paypalClient->getAccessToken();
        $this->paypalClient->setAccessToken($paypalAccessToken);
    }

    /**
     * Get mobile payment data for API.
     *
     * @return array
     * @throws Throwable
     */
    public function getMobilePaymentData(): array
    {
        $paypalSetting = plugin_setting('paypal');
        $mode          = $paypalSetting['sandbox_mode'] ? 'sandbox' : 'live';

        if ($mode == 'sandbox') {
            $clientId = $paypalSetting['sandbox_client_id'];
        } else {
            $clientId = $paypalSetting['live_client_id'];
        }

        $paypalOrder = $this->createOrder();

        return [
            'clientId'    => $clientId,
            'currency'    => $this->order->currency_code,
            'environment' => $mode,
            'orderId'     => $paypalOrder['id'],
            'userAction'  => 'paynow',  // paynow|continue
        ];
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function createOrder(): mixed
    {
        $this->initPaypal();
        $order = $this->order;
        $total = round($order->total, 2);

        return $this->paypalClient->createOrder([
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $order->currency_code,
                        'value'         => $total,
                    ],
                    'description' => 'test',
                ],
            ],
        ]);
    }
}
