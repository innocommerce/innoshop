<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Paypal\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Libraries\Currency;
use InnoShop\Common\Repositories\Order\PaymentRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use Plugin\Paypal\Services\PaypalService;
use Srmklive\PayPal\Services\PayPal;
use Throwable;

class PaypalController
{
    private PayPal $paypalClient;

    /**
     * Init Paypal
     *
     * @throws Throwable
     */
    private function initPaypal($order): void
    {
        $paypalService      = new PaypalService($order);
        $this->paypalClient = $paypalService->paypalClient;
    }

    /**
     * Create PayPal order.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function create(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $orderNumber = $requestData['orderNumber'] ?? '';

        $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);
        $this->initPaypal($order);

        $currencyCode = plugin_setting('paypal', 'currency', 'usd');
        $orderTotal   = Currency::getInstance()->convert($order->total, $order->currency_code, 'USD');

        $orderData = [
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => strtoupper($currencyCode),
                        'value'         => round($orderTotal, 2),
                    ],
                    'description' => $order->number,
                ],
            ],
        ];

        $paypalOrder = $this->paypalClient->createOrder($orderData);

        return response()->json($paypalOrder);
    }

    /**
     * Callback after capture.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function capture(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $orderNumber = $requestData['orderNumber'] ?? '';

        $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

        $this->initPaypal($order);
        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, ['request' => $requestData]);
        $paypalOrderId = $requestData['paypalOrderId'];
        $result        = $this->paypalClient->capturePaymentOrder($paypalOrderId);
        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, ['response' => $result]);

        try {
            DB::beginTransaction();
            if ($result['status'] === 'COMPLETED') {
                StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
        }

        return response()->json($result);
    }
}
