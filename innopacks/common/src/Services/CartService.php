<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Repositories\CartItemRepo;
use InnoShop\Common\Resources\CartListItem;

class CartService
{
    private int $customerID;

    private string $guestID;

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
    }

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @return static
     */
    public static function getInstance(int $customerID = 0, string $guestID = ''): static
    {
        return new self($customerID, $guestID);
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
    public function getCartItems(array $filters = []): Collection
    {
        $cartItems = $this->getCartBuilder($filters)->get();

        return $cartItems->filter(function ($item) {
            if (empty($item->product) || empty($item->productSku)) {
                $item->delete();
            }

            return $item->product && $item->productSku;
        });
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
     * @throws \Throwable
     */
    public function addCart($data): array
    {
        $data = $this->mergeAuthId($data);
        CartItemRepo::getInstance()->create($data);

        return $this->handleResponse();
    }

    /**
     * @param  $cartItem
     * @param  $data
     * @return array
     */
    public function updateCart($cartItem, $data): array
    {
        $data = $this->mergeAuthId($data);
        CartItemRepo::getInstance()->update($cartItem, $data);

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

        return [
            'total'         => $selectedCartItems->sum('quantity'),
            'amount'        => $selectedAmount,
            'amount_format' => currency_format($selectedAmount),
            'list'          => CartListItem::collection($allCartItems)->jsonSerialize(),
        ];
    }

    /**
     * @param  array  $data
     * @return array
     */
    private function mergeAuthId(array $data): array
    {
        $data['customer_id'] = $this->customerID;

        if (empty($this->customerID)) {
            $data['guest_id'] = $this->guestID;
        }

        return $data;
    }
}
