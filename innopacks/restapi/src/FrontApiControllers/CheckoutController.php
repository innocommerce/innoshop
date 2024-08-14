<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Exceptions\Unauthorized;
use InnoShop\Common\Services\CartService;
use InnoShop\Common\Services\Checkout\BillingService;
use InnoShop\Common\Services\CheckoutService;
use InnoShop\Common\Services\StateMachineService;
use InnoShop\Front\Requests\CheckoutConfirmRequest;
use Throwable;

class CheckoutController extends BaseController
{
    /**
     * Get checkout data and render page.
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function index(): JsonResponse
    {
        try {
            $checkout = CheckoutService::getInstance(token_customer_id());
            $result   = $checkout->getCheckoutResult();

            return read_json_success($result);
        } catch (Unauthorized $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Update checkout, include shipping address, shipping method, billing address, billing method
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $data     = $request->all();
            $checkout = CheckoutService::getInstance(token_customer_id());
            $checkout->updateValues($data);
            $result = $checkout->getCheckoutResult();

            return update_json_success($result);
        } catch (Unauthorized $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Confirm checkout and place order
     *
     * @param  CheckoutConfirmRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function confirm(CheckoutConfirmRequest $request): JsonResponse
    {
        try {
            $data     = $request->all();
            $checkout = CheckoutService::getInstance(token_customer_id());
            if ($data) {
                $checkout->updateValues($data);
            }

            $order = $checkout->confirm();
            StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID, '', true);

            return submit_json_success($order);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function billingMethods(): JsonResponse
    {
        try {
            $methods = BillingService::getInstance()->getMethods();

            return read_json_success($methods);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function quickConfirm(Request $request): JsonResponse
    {
        try {
            CartService::getInstance(token_customer_id())->addCart($request->all());

            $checkoutService = CheckoutService::getInstance(token_customer_id());
            $checkoutData    = ['billing_method_code' => $request->get('shipping_method_code')];
            $checkoutService->updateValues($checkoutData);

            $order = $checkoutService->confirm();
            StateMachineService::getInstance($order)->changeStatus(StateMachineService::UNPAID, '', true);

            return submit_json_success($order);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
