<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\ApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Services\CartService;
use InnoShop\Common\Services\Checkout\BillingService;
use InnoShop\Common\Services\CheckoutService;
use InnoShop\Common\Services\StateMachineService;
use Throwable;

class CheckoutController extends BaseApiController
{
    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function billingMethods(): JsonResponse
    {
        $methods = BillingService::getInstance()->getMethods();

        return json_success(trans('front::common.get_success'), $methods);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function quickConfirm(Request $request): JsonResponse
    {
        try {
            CartService::getInstance()->addCart($request->all());

            $checkoutService = CheckoutService::getInstance();
            $checkoutData    = ['billing_method_code' => $request->get('shipping_method_code')];
            $checkoutService->updateValues($checkoutData);

            $order = $checkoutService->confirm();
            StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID);

            return json_success(trans('front::common.submitted_success'), $order);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
