<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Stripe\Services;

use Exception;
use InnoShop\Common\Libraries\Currency;
use InnoShop\Common\Models\Country;
use InnoShop\Front\Services\PaymentService;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeService extends PaymentService
{
    // 零位十进制货币 https://stripe.com/docs/currencies#special-cases
    public const ZERO_DECIMAL = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Invalid payment methods:
     * card, acss_debit, affirm, afterpay_clearpay, alipay, au_becs_debit, bacs_debit, bancontact, blik,
     * boleto, cashapp, customer_balance, eps, fpx, giropay, grabpay, ideal, klarna, konbini, link, multibanco,
     * oxxo, p24, pay_by_bank, paynow, paypal, pix, promptpay, sepa_debit, sofort, swish, us_bank_account,
     * wechat_pay, revolut_pay, mobilepay, zip, amazon_pay, alma, twint, kr_card, naver_pay, kakao_pay, payco,
     * nz_bank_account, samsung_pay, billie, or satispay
     */
    public const PAYMENT_METHODS = [
        'card', 'klarna',
    ];

    private StripeClient $stripeClient;

    /**
     * @throws Exception
     */
    public function __construct($order)
    {
        parent::__construct($order);
        $apiKey = plugin_setting('stripe.secret_key');
        if (empty($apiKey)) {
            throw new Exception('Invalid stripe secret key');
        }
        $this->stripeClient = new StripeClient($apiKey);
    }

    /**
     * @throws ApiErrorException
     * @throws Exception
     */
    public function capture($creditCardData): Charge
    {
        $tokenId = $creditCardData['token'] ?? '';
        if (empty($tokenId)) {
            throw new Exception('Invalid token');
        }

        $currency = $this->order->currency_code;
        $total    = Currency::getInstance()->convertByRate($this->order->total, $this->order->currency_value);

        if (! in_array($currency, self::ZERO_DECIMAL)) {
            $total = round($total, 2) * 100;
        } else {
            $total = floor($total);
        }

        $stripeCustomer = $this->createCustomer($tokenId);

        $stripeChargeParameters = [
            'amount'   => $total,
            'currency' => $currency,
            'metadata' => [
                'order_number' => $this->order->number,
            ],
            'customer' => $stripeCustomer->id,
            'shipping' => $this->getShippingAddress(),
        ];

        return $this->stripeClient->charges->create($stripeChargeParameters);
    }

    /**
     * 创建 stripe customer
     * @param  string  $source
     * @return Customer
     * @throws ApiErrorException
     */
    private function createCustomer(string $source = ''): Customer
    {
        $paymentCountry = Country::query()->find($this->order->payment_country_id);
        $customerData   = [
            'email'       => $this->order->email,
            'description' => setting('base.meta_title'),
            'name'        => $this->order->customer_name,
            'phone'       => $this->order->shipping_telephone,
            'address'     => [
                'city'        => $this->order->payment_city,
                'country'     => $paymentCountry->code ?? '',
                'line1'       => $this->order->payment_address_1,
                'line2'       => $this->order->payment_address_2,
                'postal_code' => $this->order->payment_zipcode,
                'state'       => $this->order->payment_zone,
            ],
            'shipping' => $this->getShippingAddress(),
            'metadata' => [
                'order_number' => $this->order->number,
            ],
        ];

        if ($source) {
            $customerData['source'] = $source;
        }

        return $this->stripeClient->customers->create($customerData);
    }

    /**
     * @return array
     */
    private function getShippingAddress(): array
    {
        $shippingCountry = Country::query()->find($this->order->shipping_country_id);

        return [
            'name'    => $this->order->shipping_customer_name,
            'phone'   => $this->order->shipping_telephone,
            'address' => [
                'city'        => $this->order->shipping_city,
                'country'     => $shippingCountry->code ?? '',
                'line1'       => $this->order->shipping_address_1,
                'line2'       => $this->order->shipping_address_2,
                'postal_code' => $this->order->shipping_zipcode,
                'state'       => $this->order->shipping_zone,
            ],
        ];
    }

    /**
     * 获取支付参数给 uniapp 使用
     * @return array
     * @throws ApiErrorException
     */
    public function getMobilePaymentData(): array
    {
        $stripeCustomer = $this->createCustomer();
        $paymentIntent  = $this->createPaymentIntent($stripeCustomer);

        return [
            'isAllowDelay'   => true,
            'merchantName'   => system_setting('base.meta_title'),
            'paymentIntent'  => $paymentIntent->client_secret,
            'publishKey'     => plugin_setting('stripe.publishable_key'),
            'billingDetails' => $this->getBillingDetails(),
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

    /**
     * Create payment intent
     * @param  $stripeCustomer
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent($stripeCustomer): PaymentIntent
    {
        $currency = $this->order->currency_code;
        if (! in_array($currency, self::ZERO_DECIMAL)) {
            $total = round($this->order->total, 2) * 100;
        } else {
            $total = floor($this->order->total);
        }

        return $this->stripeClient->paymentIntents->create([
            'amount'                    => $total,
            'currency'                  => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'customer' => $stripeCustomer->id,
            'metadata' => [
                'order_number' => $this->order->number,
            ],
            'shipping' => $this->getShippingAddress(),
        ]);
    }

    /**
     * 创建 Stripe Checkout Session
     *
     * @param  array  $options  配置选项
     * @return \Stripe\Checkout\Session
     * @throws ApiErrorException
     */
    public function createCheckoutSession(array $options = []): \Stripe\Checkout\Session
    {
        $currency = $this->order->currency_code;
        if (! in_array($currency, self::ZERO_DECIMAL)) {
            $total = round($this->order->total, 2) * 100;
        } else {
            $total = floor($this->order->total);
        }

        $defaultOptions = [
            'payment_method_types' => self::PAYMENT_METHODS,
            'mode'                 => 'payment',
            'line_items'           => [[
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name'        => 'Order #'.$this->order->number,
                        'description' => setting('base.meta_title') ?: 'Order Payment',
                    ],
                    'unit_amount' => $total,
                ],
                'quantity' => 1,
            ]],
            'customer_email'      => $this->order->email,
            'client_reference_id' => $this->order->number,
            'metadata'            => [
                'order_number' => $this->order->number,
                'customer_id'  => $this->order->customer_id,
            ],
            'shipping_address_collection' => [
                'allowed_countries' => [
                    'CN', 'US', 'CA', 'GB', 'DE', 'FR', 'IT', 'ES', 'JP', 'AU', 'HK', 'SG', 'MY', 'TH', 'VN', 'ID', 'PH', 'KR',
                ],
            ],
            'shipping_options' => [
                [
                    'shipping_rate_data' => [
                        'type'         => 'fixed_amount',
                        'fixed_amount' => [
                            'amount'   => 0,
                            'currency' => $currency,
                        ],
                        'display_name'      => 'Standard Shipping',
                        'delivery_estimate' => [
                            'minimum' => [
                                'unit'  => 'business_day',
                                'value' => 5,
                            ],
                            'maximum' => [
                                'unit'  => 'business_day',
                                'value' => 7,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $sessionOptions = array_merge($defaultOptions, $options);

        return $this->stripeClient->checkout->sessions->create($sessionOptions);
    }

    /**
     * 获取 Stripe Checkout Session
     *
     * @param  string  $sessionId
     * @return \Stripe\Checkout\Session
     * @throws ApiErrorException
     */
    public function retrieveCheckoutSession(string $sessionId): \Stripe\Checkout\Session
    {
        return $this->stripeClient->checkout->sessions->retrieve($sessionId);
    }
}
