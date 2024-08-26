<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Services\CartService;

class CartController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $cartList = CartService::getInstance()->handleResponse();

        return inno_view('cart.index', $cartList);
    }

    /**
     * Get mini cart result.
     * @return mixed
     */
    public function mini(): mixed
    {
        try {
            $currentCart = CartService::getInstance()->handleResponse();

            return json_success(front_trans('common.read_success'), $currentCart);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Add product sku to cart.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $cartData = CartService::getInstance()->addCart($request->all());

            return json_success(front_trans('common.saved_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  CartItem  $cart
     * @return JsonResponse
     */
    public function update(Request $request, CartItem $cart): JsonResponse
    {
        try {
            $cartData = CartService::getInstance()->updateCart($cart, $request->all());

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function select(Request $request): JsonResponse
    {
        try {
            $cartIds  = $request->get('cart_ids');
            $cartData = CartService::getInstance()->select($cartIds);

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function unselect(Request $request): JsonResponse
    {
        try {
            $cartIds  = $request->get('cart_ids');
            $cartData = CartService::getInstance()->unselect($cartIds);

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CartItem  $cart
     * @return JsonResponse
     */
    public function destroy(CartItem $cart): JsonResponse
    {
        try {
            $cart->delete();
            $cartData = CartService::getInstance()->getCartList();

            return json_success(front_trans('common.deleted_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
