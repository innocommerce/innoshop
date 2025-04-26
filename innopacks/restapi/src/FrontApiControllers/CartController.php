<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\Request;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Services\CartService;
use Throwable;

class CartController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $cartList = CartService::getInstance(token_customer_id())->handleResponse();

        return read_json_success($cartList);
    }

    /**
     * Add product sku to cart.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        try {
            $cartData = CartService::getInstance(token_customer_id())->addCart($request->all());

            return create_json_success($cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  CartItem  $cart
     * @return mixed
     */
    public function update(Request $request, CartItem $cart): mixed
    {
        try {
            $cartData = CartService::getInstance(token_customer_id())->updateCart($cart, $request->all());

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function select(Request $request): mixed
    {
        try {
            $cartIds  = $request->get('cart_ids');
            $cartData = CartService::getInstance(token_customer_id())->select($cartIds);

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function unselect(Request $request): mixed
    {
        try {
            $cartIds  = $request->get('cart_ids');
            $cartData = CartService::getInstance(token_customer_id())->unselect($cartIds);

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function selectAll(): mixed
    {
        try {
            $cartData = CartService::getInstance(token_customer_id())->selectAll();

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function unselectAll(): mixed
    {
        try {
            $cartData = CartService::getInstance(token_customer_id())->unselectAll();

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CartItem  $cart
     * @return mixed
     */
    public function destroy(CartItem $cart): mixed
    {
        try {
            if ($cart->customer_id != token_customer_id()) {
                throw new \Exception('Cart cannot belongs to the customer');
            }
            $cartData = CartService::getInstance(token_customer_id())->delete($cart);

            return json_success(front_trans('common.deleted_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
