<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Services\CartService;
use Throwable;

class CartController extends BaseController
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $cartList = CartService::getInstance($customer->id)->handleResponse();

        return read_json_success($cartList);
    }

    /**
     * Add product sku to cart.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $customer = $request->user();
            $cartData = CartService::getInstance($customer->id)->addCart($request->all());

            return create_json_success($cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
