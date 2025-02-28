<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Airwallex\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Country;
use InnoShop\Front\Services\PaymentService;

class AirwallexService extends PaymentService
{
    private ClientService $airwallex;

    /**
     * @throws Exception
     */
    public function __construct($order)
    {
        parent::__construct($order);
        $clientId = plugin_setting('airwallex.client_id');
        $apiKey   = plugin_setting('airwallex.api_key');
        if (empty($apiKey)) {
            throw new Exception('Invalid airwallex api key');
        }
        $testMode        = plugin_setting('airwallex.test_mode');
        $this->airwallex = new ClientService([
            'clientId'   => $clientId,
            'apiKey'     => $apiKey,
            'production' => $testMode == '0',
        ]);
    }

    /**
     * @throws Exception
     */
    public function payment(): array
    {
        $currency = strtoupper($this->order->currency_code);

        $total = floor($this->order->total) * $this->order->currency_value;
        $total = round($total, 2);

        $paymentData = [
            'amount'            => $total,
            'currency'          => $currency,
            'merchant_order_id' => $this->order->number,
            'request_id'        => $this->generateRandomId(),
            'order'             => [
                'shipping' => $this->getShippingAddress(),
            ],
        ];

        Log::info('Create Airwallex Payment Data:');
        Log::info(json_encode($paymentData));

        return $this->airwallex->paymentIntent->create($paymentData);
    }

    /**
     * @return string
     */
    public function generateRandomId(): string
    {
        $randomPart = mt_rand(1000, 9999);
        $timestamp  = time();

        return $timestamp.$randomPart;
    }

    /**
     * @return array
     */
    private function getShippingAddress(): array
    {
        $shippingCountry = Country::query()->find($this->order->shipping_country_id);

        return [
            'first_name' => $this->order->shipping_customer_name,
            'phone'      => $this->order->shipping_telephone,
            'address'    => [
                'city'         => $this->order->shipping_city,
                'country_code' => $shippingCountry->code ?? '',
                'street'       => $this->order->shipping_address_1,
                'postal_code'  => $this->order->shipping_zipcode,
                'state'        => $this->order->shipping_zone,
            ],
        ];
    }

    /**
     * 获取支付地址
     *
     * @return array
     */
    private function getBillingDetails(): array
    {
        $order          = $this->order;
        $paymentCountry = Country::query()->find($order->payment_country_id);

        return [
            'name'    => $order->customer_name,
            'email'   => $order->email,
            'phone'   => $order->telephone ?: $order->payment_telephone,
            'address' => [
                'city'       => $order->payment_city,
                'country'    => $paymentCountry->code ?? '',
                'line1'      => $order->payment_address_1,
                'line2'      => $order->payment_address_2,
                'postalCode' => $order->payment_zipcode,
                'state'      => $order->payment_zone,
            ],
        ];
    }
}
