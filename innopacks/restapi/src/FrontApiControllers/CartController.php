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
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Front - Cart')]
#[Authenticated]
class CartController extends BaseController
{
    /**
     * @return mixed
     */
    #[Endpoint('Get cart items')]
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
    #[Endpoint('Add item to cart')]
    #[BodyParam('sku_id', type: 'integer', required: true)]
    #[BodyParam('quantity', type: 'integer', required: true, example: 1)]
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
    #[Endpoint('Update cart item')]
    #[UrlParam('cart', type: 'integer', description: 'Cart item ID')]
    public function update(Request $request, CartItem $cart): mixed
    {
        try {
            if ($cart->customer_id != token_customer_id()) {
                throw new \Exception('Cart item does not belong to the customer');
            }
            $cartData = CartService::getInstance(token_customer_id())->updateCart($cart, $request->all());

            return json_success(common_trans('base.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update a cart item.
     * PATCH /api/front/carts/{cart}
     *
     * @param  Request  $request
     * @param  CartItem  $cart
     * @return mixed
     */
    #[Endpoint('Partial update cart item')]
    #[UrlParam('cart', type: 'integer', description: 'Cart item ID')]
    public function patch(Request $request, CartItem $cart): mixed
    {
        try {
            if ($cart->customer_id != token_customer_id()) {
                throw new \Exception('Cart item does not belong to the customer');
            }
            $cartData = CartService::getInstance(token_customer_id())->updateCart($cart, $request->all());

            return update_json_success($cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Select cart items')]
    #[BodyParam('cart_ids', type: 'string', required: true, description: 'Comma-separated cart item IDs')]
    public function select(Request $request): mixed
    {
        try {
            $cartIds  = $request->get('cart_ids');
            $cartData = CartService::getInstance(token_customer_id())->select($cartIds);

            return json_success(common_trans('base.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Unselect cart items')]
    #[BodyParam('cart_ids', type: 'string', required: true)]
    public function unselect(Request $request): mixed
    {
        try {
            $cartIds  = $request->get('cart_ids');
            $cartData = CartService::getInstance(token_customer_id())->unselect($cartIds);

            return json_success(common_trans('base.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    #[Endpoint('Select all cart items')]
    public function selectAll(): mixed
    {
        try {
            $cartData = CartService::getInstance(token_customer_id())->selectAll();

            return json_success(common_trans('base.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    #[Endpoint('Unselect all cart items')]
    public function unselectAll(): mixed
    {
        try {
            $cartData = CartService::getInstance(token_customer_id())->unselectAll();

            return json_success(common_trans('base.updated_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CartItem  $cart
     * @return mixed
     */
    #[Endpoint('Remove cart item')]
    #[UrlParam('cart', type: 'integer', description: 'Cart item ID')]
    public function destroy(CartItem $cart): mixed
    {
        try {
            if ($cart->customer_id != token_customer_id()) {
                throw new \Exception('Cart cannot belongs to the customer');
            }
            $cartData = CartService::getInstance(token_customer_id())->delete($cart);

            return json_success(common_trans('base.deleted_success'), $cartData);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
