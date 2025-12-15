<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\CartItemRepo;
use InnoShop\Common\Resources\CartListItem;
use Throwable;

class CartService
{
    private int $customerID;

    private string $guestID;

    private StockService $stockService;

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     */
    public function __construct(int $customerID = 0, string $guestID = '')
    {
        if ($customerID) {
            $this->customerID = $customerID;
        } else {
            $this->customerID = current_customer_id();
        }

        if ($guestID) {
            $this->guestID = $guestID;
        } else {
            $this->guestID = current_guest_id();
        }

        $this->stockService = StockService::getInstance();
    }

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @return static
     */
    public static function getInstance(int $customerID = 0, string $guestID = ''): static
    {
        return new static($customerID, $guestID);
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function getCartBuilder(array $filters = []): Builder
    {
        $filters = $this->mergeAuthId($filters);

        return CartItemRepo::getInstance()->builder($filters);
    }

    /**
     * @param  array  $filters
     * @return Collection
     */
    public function getCartItems(array $filters = [], $withHook = true): Collection
    {
        $cartItems = $this->getCartBuilder($filters)->get();

        $cartItems = $cartItems->filter(function ($item) use ($withHook) {
            if (empty($item->product) || empty($item->productSku)) {
                $item->delete();

                return false;
            }

            $item->is_stock_enough = $this->stockService->checkStock($item->sku_code, $item->quantity, $item->id);

            if ($withHook) {
                fire_hook_action('service.cart.items.item', $item);
            }

            return true;
        });

        if ($withHook) {
            $cartItems = fire_hook_filter('service.cart.items', $cartItems);
        }

        return $cartItems;
    }

    /**
     * @param  array  $filters
     * @return array
     */
    public function getCartList(array $filters = []): array
    {
        $cartItems = $this->getCartItems($filters);

        return CartListItem::collection($cartItems)->jsonSerialize();
    }

    /**
     * @param  $data
     * @return array
     * @throws Throwable
     */
    public function addCart($data): array
    {
        if (! $this->stockService->checkStockBySkuId($data['sku_id'], $data['quantity'] ?? 1)) {
            throw new Exception(trans('front/common.stock_not_enough'));
        }

        // 验证产品选项
        if (! empty($data['options'])) {
            $productSku = \InnoShop\Common\Models\Product\Sku::find($data['sku_id']);
            if ($productSku && $productSku->product) {
                $optionService = \InnoShop\Common\Services\ProductOptionService::getInstance($productSku->product);
                $validation    = $optionService->validateOptions($data['options']);

                if (! $validation['valid']) {
                    throw new Exception(implode('; ', $validation['errors']));
                }
            }
        }

        $data     = $this->mergeAuthId($data);
        $cartItem = CartItemRepo::getInstance()->create($data);

        // 保存选项信息
        if (! empty($data['options']) && $cartItem) {
            $this->saveCartItemOptions($cartItem, $data['options']);
        }

        // Trigger hook after adding item to cart
        fire_hook_action('service.cart.add.after', [
            'cart_item' => $cartItem,
            'quantity'  => $data['quantity'] ?? 1,
        ]);

        return $this->handleResponse();
    }

    /**
     * @param  $cartItem
     * @param  $data
     * @return array
     * @throws Exception
     */
    public function updateCart($cartItem, $data): array
    {
        if (! $this->stockService->checkStockByCartItem($cartItem, $data['quantity'])) {
            throw new Exception(trans('front/common.stock_not_enough'));
        }

        $data = $this->mergeAuthId($data);
        CartItemRepo::getInstance()->update($cartItem, $data);

        // Trigger hook after updating cart item
        fire_hook_action('service.cart.update.after', [
            'cart_item' => $cartItem,
            'quantity'  => $data['quantity'] ?? $cartItem->quantity,
        ]);

        return $this->handleResponse();
    }

    /**
     * Delete cart item
     *
     * @param  CartItem  $cartItem
     * @return array
     */
    public function delete(CartItem $cartItem): array
    {
        $cartItem->delete();

        // Trigger cart item deleted event
        fire_hook_action('service.cart.delete.after', $cartItem);

        return $this->handleResponse();
    }

    /**
     * @param  $cartIds
     * @return array
     */
    public function select($cartIds): array
    {
        $cartItems = $this->getCartBuilder();
        $cartItems->whereIn('id', $cartIds)->update(['selected' => true]);

        return $this->handleResponse();
    }

    /**
     * @param  $cartIds
     * @return array
     */
    public function unselect($cartIds): array
    {
        $cartItems = $this->getCartBuilder();
        $cartItems->whereIn('id', $cartIds)->update(['selected' => false]);

        return $this->handleResponse();
    }

    /**
     * @return array
     */
    public function selectAll(): array
    {
        $cartItems = $this->getCartBuilder();
        $cartItems->update(['selected' => true]);

        return $this->handleResponse();
    }

    /**
     * @return array
     */
    public function unselectAll(): array
    {
        $cartItems = $this->getCartBuilder();
        $cartItems->update(['selected' => false]);

        return $this->handleResponse();
    }

    /**
     * After logging in or signing in, merge the items from the guest cart into the user's account.
     *
     * @param  $guestID
     * @return void
     */
    public function mergeCart($guestID): void
    {
        $authData       = ['customer_id' => $this->customerID, 'guest_id' => ''];
        $guestCartItems = CartItemRepo::getInstance()->builder(['guest_id' => $guestID])->get();
        foreach ($guestCartItems as $guestCartItem) {
            $filters = [
                'customer_id' => $this->customerID,
                'product_id'  => $guestCartItem->product_id,
                'sku_code'    => $guestCartItem->sku_code,
            ];
            $customerCartItem = CartItemRepo::getInstance()->builder($filters)->first();
            if (empty($customerCartItem)) {
                $guestCartItem->update($authData);
            } else {
                $customerCartItem->increment('quantity', $guestCartItem->quantity);
                $customerCartItem->update($authData);
                $guestCartItem->delete();
            }
        }
    }

    /**
     * @return array
     */
    public function handleResponse(): array
    {
        $allCartItems      = $this->getCartItems();
        $selectedCartItems = $allCartItems->where('selected', true);
        $selectedAmount    = $selectedCartItems->sum('subtotal');
        $quantityTotal     = $selectedCartItems->sum('quantity');

        $data = [
            'total'         => $quantityTotal,
            'total_format'  => $quantityTotal <= 99 ? $quantityTotal : '99+',
            'amount'        => $selectedAmount,
            'amount_format' => currency_format($selectedAmount),
            'list'          => CartListItem::collection($allCartItems)->jsonSerialize(),
        ];

        return fire_hook_filter('service.cart.response', $data);
    }

    /**
     * @param  Order  $order
     * @return void
     * @throws Throwable
     */
    public function addOrderToCart(Order $order): void
    {
        $this->unselectAll();
        foreach ($order->items as $item) {
            $productSku = $item->productSku;
            $data       = [
                'sku_id'   => $productSku->id,
                'quantity' => $item->quantity,
                'selected' => true,
            ];
            $this->addCart($data);
        }
    }

    /**
     * 保存购物车项选项值
     *
     * @param  CartItem  $cartItem
     * @param  array  $options  格式: [option_id => [option_value_id, ...], ...]
     * @return void
     */
    private function saveCartItemOptions(CartItem $cartItem, array $options): void
    {
        // 先删除已有的选项值
        \InnoShop\Common\Models\Cart\OptionValue::where('cart_item_id', $cartItem->id)->delete();

        // 保存新的选项值
        foreach ($options as $optionId => $optionValueIds) {
            if (! is_array($optionValueIds)) {
                $optionValueIds = [$optionValueIds];
            }

            foreach ($optionValueIds as $optionValueId) {
                // 获取选项和选项值信息
                $option      = \InnoShop\Common\Models\Option::find($optionId);
                $optionValue = \InnoShop\Common\Models\OptionValue::find($optionValueId);

                if (! $option || ! $optionValue) {
                    continue;
                }

                // 获取产品选项值配置中的价格调整
                $productOptionValue = \InnoShop\Common\Models\Product\OptionValue::where([
                    'product_id'      => $cartItem->product_id,
                    'option_id'       => $optionId,
                    'option_value_id' => $optionValueId,
                ])->first();

                $priceAdjustment = $productOptionValue ? $productOptionValue->price_adjustment : 0;

                // 创建购物车选项值记录
                \InnoShop\Common\Models\Cart\OptionValue::create([
                    'cart_item_id'      => $cartItem->id,
                    'option_id'         => $optionId,
                    'option_value_id'   => $optionValueId,
                    'option_name'       => $option->name,
                    'option_value_name' => $optionValue->name,
                    'price_adjustment'  => $priceAdjustment,
                ]);
            }
        }
    }

    /**
     * @param  array  $data
     * @return array
     */
    protected function mergeAuthId(array $data): array
    {
        $data['customer_id'] = $this->customerID;

        if (empty($this->customerID)) {
            $data['guest_id'] = $this->guestID;
        }

        return $data;
    }
}
