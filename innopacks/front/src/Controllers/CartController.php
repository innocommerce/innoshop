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
use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Services\CartService;
use InnoShop\Front\Requests\CartRequest;
use Throwable;

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
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Add product sku to cart.
     *
     * @param  CartRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(CartRequest $request): mixed
    {
        try {
            $cartData = CartService::getInstance()->addCart($request->all());

            return json_success(front_trans('common.saved_success'), $cartData);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CartRequest  $request
     * @param  CartItem  $cart
     * @return mixed
     */
    public function update(CartRequest $request, CartItem $cart): mixed
    {
        try {
            $cartData = CartService::getInstance()->updateCart($cart, $request->all());

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (Exception $e) {
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
            $cartData = CartService::getInstance()->select($cartIds);

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (Exception $e) {
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
            $cartData = CartService::getInstance()->unselect($cartIds);

            return json_success(front_trans('common.updated_success'), $cartData);
        } catch (Exception $e) {
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
            $cartData = CartService::getInstance()->delete($cart);

            return json_success(front_trans('common.deleted_success'), $cartData);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
