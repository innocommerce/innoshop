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
            $creditCardData = $request->all();

            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            $paymentData = ['amount' => $order->total, 'paid' => false, 'reference' => $creditCardData];
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
     * Create PaymentIntent for Elements checkout (PaymentIntents API)
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function createPaymentIntentAction(Request $request): JsonResponse
    {
        try {
            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            if (! $order) {
                return json_fail(trans('Stripe::common.order_not_found'));
            }

            $stripeService = new StripeService($order);
            $paymentIntent = $stripeService->createElementsPaymentIntent();

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'amount'    => $order->total,
                'paid'      => false,
                'reference' => ['payment_intent_id' => $paymentIntent->id],
            ]);

            return read_json_success([
                'client_secret' => $paymentIntent->client_secret,
            ]);

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
            $requestData = $request->all();
            Log::info('Request data: '.json_encode($requestData));

            $type        = $requestData['type'];
            $orderNumber = $request['data']['object']['metadata']['order_number'] ?? '';
            $order       = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

            Log::info('Request type: '.$type);
            Log::info('Request number: '.$orderNumber);

            if (in_array($type, ['charge.succeeded', 'payment_intent.succeeded']) && $order) {
                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success(trans('Stripe::common.capture_success'));
            }

            return json_success(trans('Stripe::common.capture_fail'));

        } catch (\Exception $e) {
            Log::info('Stripe error: '.$e->getMessage());

            return json_success($e->getMessage());
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
            $filters = [
                'number'      => $request->get('order_number'),
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

    /**
     * 创建 Stripe Embedded Checkout Session
     * 支持 Apple Pay / Google Pay / Link 快捷支付
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function createEmbeddedSession(Request $request): JsonResponse
    {
        try {
            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            if (! $order) {
                return json_fail(trans('Stripe::common.order_not_found'));
            }

            $stripeService = new StripeService($order);
            $session       = $stripeService->createEmbeddedSession([
                'return_url' => front_route('stripe_embedded_return', ['order_number' => $order->number]),
                'metadata'   => [
                    'order_number' => $order->number,
                    'customer_id'  => $order->customer_id,
                ],
            ]);

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'amount'    => $order->total,
                'paid'      => false,
                'reference' => ['session_id' => $session->id],
            ]);

            return read_json_success([
                'client_secret' => $session->client_secret,
            ]);

        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Embedded Checkout 支付完成返回页
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function embeddedReturn(Request $request): JsonResponse
    {
        try {
            $orderNumber = $request->get('order_number');
            $order       = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

            if (! $order) {
                return json_fail(trans('Stripe::common.order_not_found'));
            }

            if ($order->status == StateMachineService::PAID) {
                return json_success(trans('Stripe::common.capture_success'));
            }

            $stripeService = new StripeService($order);
            $payment       = PaymentRepo::getInstance()->getByOrderId($order->id);
            $sessionId     = $payment->reference['session_id'] ?? '';

            if (empty($sessionId)) {
                return json_fail(trans('Stripe::common.capture_fail'));
            }

            $session = $stripeService->retrieveSession($sessionId);

            if ($session->payment_status === 'paid') {
                PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                    'amount'    => $order->total,
                    'paid'      => true,
                    'reference' => ['session_id' => $session->id, 'payment_intent' => $session->payment_intent],
                ]);

                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success(trans('Stripe::common.capture_success'));
            }

            return json_success(trans('Stripe::common.capture_fail'));

        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
