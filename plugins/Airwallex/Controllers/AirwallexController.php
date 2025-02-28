<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Airwallex\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Repositories\Order\PaymentRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use InnoShop\Front\Controllers\BaseController;
use Plugin\Airwallex\Services\AirwallexService;
use Throwable;

class AirwallexController extends BaseController
{
    /**
     * Payment
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function payment(Request $request): JsonResponse
    {
        try {
            $number         = request('order_number');
            $customer       = current_customer();
            $order          = OrderRepo::getInstance()->getOrderByNumber($number, $customer);
            $creditCardData = $request->all();

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $creditCardData);
            $result = (new AirwallexService($order))->payment();
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, ['response' => $result]);

            if (! empty($result)) {
                return json_success($result);
            }

            return json_success(trans('Airwallex::common.payment_fail'));

        } catch (\Exception $e) {
            Log::error($e);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Webhook from Airwallex
     *
     * https://www.airwallex.com/app/developer/webhooks
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('====== Start Airwallex Callback ======');

        try {
            $requestData = $request->getContent();
            Log::info('Request data: '.json_encode($request->all()));

            $timestamp = $request->header('x-timestamp');
            $signature = $request->header('x-signature');

            $secret = plugin_setting('airwallex.callback_secret');

            if (hash_hmac('sha256', $timestamp.$requestData, $secret) != $signature) {
                return json_fail('signature_fail');
            }

            $status      = $request['data']['object']['status']            ?? '';
            $orderNumber = $request['data']['object']['merchant_order_id'] ?? '';
            $order       = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

            Log::info('Request number: '.$orderNumber);

            if ($status == 'SUCCEEDED' && $order) {
                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success('capture_success');
            }

            return json_fail('capture_fail');
        } catch (\Exception $e) {
            Log::info('Airwallex error: '.$e->getMessage());

            return json_fail($e->getMessage());
        }
    }
}
