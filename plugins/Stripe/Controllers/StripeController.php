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

            if (!$order) {
                return json_fail('Order not found');
            }

            // Check if order is already paid
            if ($order->status === StateMachineService::PAID) {
                return json_success(trans('Stripe::common.already_paid'), ['already_paid' => true]);
            }

            $paymentData = ['amount' => $order->total, 'paid' => false, 'reference' => $creditCardData];
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

            $result = (new StripeService($order))->capture($creditCardData);

            $isPaid = $result->paid && $result->captured;

            $paymentData = ['charge_id' => $result->id, 'amount' => $order->total, 'paid' => $isPaid, 'reference' => $result->toArray()];
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

            if ($isPaid) {
                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success(trans('Stripe::common.capture_success'), [
                    'paid' => true,
                    'order_number' => $order->number,
                    'order_status' => $order->status,
                ]);
            }

            return json_fail(trans('Stripe::common.capture_fail'));

        } catch (\Exception $e) {
            Log::error('Stripe capture error: '.$e->getMessage());
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

            $type        = $requestData['type'] ?? '';
            $orderNumber = '';

            // Handle different event types
            if ($type == 'checkout.session.completed') {
                // For Stripe Checkout
                $orderNumber = $requestData['data']['object']['metadata']['order_number'] ??
                              $requestData['data']['object']['client_reference_id'] ?? '';
                $paymentStatus = $requestData['data']['object']['payment_status'] ?? '';

                Log::info('Checkout session - Payment status: '.$paymentStatus);
                Log::info('Order number: '.$orderNumber);

                if ($orderNumber && $paymentStatus === 'paid') {
                    $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

                    if ($order) {
                        // Update payment record
                        $sessionId = $requestData['data']['object']['id'] ?? '';
                        $paymentIntentId = $requestData['data']['object']['payment_intent'] ?? '';

                        $paymentData = [
                            'charge_id' => $paymentIntentId,
                            'amount'    => $order->total,
                            'paid'      => true,
                            'reference' => $requestData['data']['object'],
                        ];
                        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

                        // Update order status
                        StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                        Log::info('Order status updated to PAID for order: '.$orderNumber);
                        return json_success(trans('Stripe::common.capture_success'));
                    }
                }
            } elseif ($type == 'charge.succeeded') {
                // For Stripe Elements
                $orderNumber = $requestData['data']['object']['metadata']['order_number'] ?? '';

                Log::info('Charge succeeded - Order number: '.$orderNumber);

                if ($orderNumber) {
                    $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

                    if ($order) {
                        StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                        Log::info('Order status updated to PAID for order: '.$orderNumber);
                        return json_success(trans('Stripe::common.capture_success'));
                    }
                }
            } elseif ($type == 'payment_intent.succeeded') {
                // Additional event for payment confirmation
                $orderNumber = $requestData['data']['object']['metadata']['order_number'] ?? '';

                Log::info('Payment intent succeeded - Order number: '.$orderNumber);

                if ($orderNumber) {
                    $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

                    if ($order && $order->status === StateMachineService::UNPAID) {
                        StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                        Log::info('Order status updated to PAID for order: '.$orderNumber);
                        return json_success(trans('Stripe::common.capture_success'));
                    }
                }
            }

            Log::info('Event type not handled or order not found: '.$type);
            return json_success('Event received');

        } catch (\Exception $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

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
                'success_url' => front_route('stripe_checkout_success', ['order_number' => $order->number]).'&session_id={CHECKOUT_SESSION_ID}',
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

    public function checkoutSuccess(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $orderNumber = $request->get('order_number');

            if ($sessionId && $orderNumber) {
                $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

                if ($order) {
                    // Verify the session with Stripe
                    $stripeService = new StripeService($order);
                    $session = $stripeService->retrieveCheckoutSession($sessionId);

                    // If payment is successful and order is still unpaid, update it
                    if ($session->payment_status === 'paid' && $order->status === StateMachineService::UNPAID) {
                        $paymentData = [
                            'charge_id' => $session->payment_intent ?? $sessionId,
                            'amount'    => $order->total,
                            'paid'      => true,
                            'reference' => $session->toArray(),
                        ];
                        PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

                        StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                        Log::info('Order status updated to PAID on success return for order: '.$orderNumber);
                    }
                }
            }

            // Redirect to the general payment success page
            return redirect()->route('payment.success', ['order_number' => $orderNumber]);

        } catch (\Exception $e) {
            Log::error('Checkout success verification error: '.$e->getMessage());
            return redirect()->route('payment.success', ['order_number' => $request->get('order_number')]);
        }
    }

    public function checkoutCancel(Request $request): JsonResponse
    {
        return json_success(trans('Stripe::common.checkout_cancel'));
    }
}
