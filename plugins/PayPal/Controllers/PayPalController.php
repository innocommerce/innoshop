<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PayPal\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Repositories\Order\PaymentRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use Plugin\PayPal\Services\PayPalService;
use Srmklive\PayPal\Services\PayPal;
use Throwable;

class PayPalController
{
    private PayPal $paypalClient;

    /**
     * Init PayPal
     *
     * @throws Throwable
     */
    private function initPayPal($order): void
    {
        $paypalService      = new PayPalService($order);
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
        if (empty($order)) {
            return response()->json(['error' => ['message' => 'Order not found']], 404);
        }

        $this->initPayPal($order);

        $paypalOrder = $this->paypalClient->createOrder([
            'intent'         => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => strtoupper(system_setting('currency')),
                        'value'         => round($order->total, 2),
                    ],
                    'description' => $order->number,
                ],
            ],
        ]);

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
        if (empty($order)) {
            return response()->json(['error' => ['message' => 'Order not found']], 404);
        }

        $this->initPayPal($order);
        $paypalOrderId = $requestData['paypalOrderId'];
        $result        = $this->paypalClient->capturePaymentOrder($paypalOrderId);

        $isCompleted = ($result['status'] ?? '') === 'COMPLETED';
        $captureUnit = $result['purchase_units'][0]['payments']['captures'][0] ?? [];

        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
            'charge_id'    => $captureUnit['id'] ?? $paypalOrderId,
            'amount'       => (float) ($captureUnit['amount']['value'] ?? $order->total),
            'handling_fee' => (float) ($captureUnit['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0),
            'paid'         => $isCompleted,
            'reference'    => ['request' => $requestData, 'response' => $result],
        ]);

        try {
            DB::beginTransaction();
            if ($result['status'] === 'COMPLETED') {
                StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayPal capture failed: '.$e->getMessage(), ['order' => $orderNumber, 'exception' => $e]);

            return response()->json(['error' => ['message' => 'Payment processing failed']], 500);
        }

        return response()->json($result);
    }
}
