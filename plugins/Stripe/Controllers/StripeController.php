<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Stripe\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Repositories\Order\PaymentRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use Plugin\Stripe\Services\StripeService;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * 订单支付扣款
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function capture(Request $request): JsonResponse
    {
        try {
            $creditCardData = $request->validate([
                'order_number' => 'required|string',
                'token'        => 'required|string',
            ]);

            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();
            if (! $order) {
                return json_fail(trans('Stripe::common.order_not_found'));
            }

            $paymentData = ['amount' => $order->total, 'paid' => false];
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

            $result = (new StripeService($order))->capture($creditCardData);
            $isPaid = $result['paid'] && $result['captured'];

            $paymentData = ['charge_id' => $result->id, 'amount' => $order->total, 'paid' => $isPaid, 'reference' => $result];
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

            if ($isPaid) {
                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success(trans('Stripe::common.capture_success'));
            }

            return json_success(trans('Stripe::common.capture_fail'));

        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Webhook from stripe
     * https://dashboard.stripe.com/webhooks
     * @param  Request  $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('====== Start Stripe Callback ======');

        try {
            $webhookSecret = plugin_setting('stripe.webhook_secret');
            if (! is_string($webhookSecret) || $webhookSecret === '') {
                Log::warning('Stripe webhook rejected: webhook secret is not configured.');

                return json_fail('Webhook signature verification is not configured', null, 503);
            }

            try {
                $event = Webhook::constructEvent(
                    $request->getContent(),
                    (string) $request->header('Stripe-Signature'),
                    $webhookSecret
                );
            } catch (\Throwable $e) {
                Log::warning('Stripe webhook signature verification failed.');

                return json_fail('Invalid signature', null, 400);
            }

            $type        = $event->type;
            $charge      = $event->data->object;
            $orderNumber = $charge->metadata->order_number ?? '';
            $order       = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

            Log::info('Stripe webhook received', ['id' => $event->id, 'type' => $type]);

            if ($type == 'charge.succeeded' && $order) {
                $expectedAmount = StripeService::getChargeAmount($order);
                $validPayment   = ($charge->paid ?? false)
                    && ($charge->captured ?? false)
                    && ($charge->status ?? '') === 'succeeded'
                    && (int) ($charge->amount ?? 0) === $expectedAmount
                    && strtoupper((string) ($charge->currency ?? '')) === strtoupper($order->currency_code);

                if (! $validPayment) {
                    Log::warning('Stripe webhook payment verification failed', ['order_number' => $orderNumber]);

                    return json_fail('Payment verification failed');
                }

                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success(trans('Stripe::common.capture_success'));
            }

            return json_success(trans('Stripe::common.capture_fail'));

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed: '.$e->getMessage());

            return json_fail($e->getMessage());
        }
    }

    /**
     * 创建 Stripe Checkout Session
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'order_number' => 'required|string',
            ]);

            $filters = [
                'number'      => $data['order_number'],
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            if (! $order) {
                return json_fail(trans('Stripe::common.order_not_found'));
            }

            $stripeService = new StripeService($order);
            $session       = $stripeService->createCheckoutSession([
                'success_url' => front_route('payment.success', ['order_number' => $order->number]),
                'cancel_url'  => front_route('payment.cancel', ['order_number' => $order->number]),
                'metadata'    => [
                    'order_number' => $order->number,
                    'customer_id'  => $order->customer_id,
                ],
            ]);

            return read_json_success([
                'session_id'   => $session->id,
                'checkout_url' => $session->url,
            ]);

        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function checkoutSuccess(Request $request): JsonResponse
    {
        return json_success(trans('Stripe::common.checkout_success'));
    }

    public function checkoutCancel(Request $request): JsonResponse
    {
        return json_success(trans('Stripe::common.checkout_cancel'));
    }
}
