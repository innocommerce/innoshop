<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Models\Product\Sku;
use Throwable;

class CartItemRepo extends BaseRepo
{
    /**
     * Get filter builder.
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = CartItem::query()->with([
            'product.translation',
        ]);

        $skuCode = $filters['sku_code'] ?? '';
        if ($skuCode) {
            $builder->where('sku_code', $skuCode);
        }

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $guestID = $filters['guest_id'] ?? 0;
        if ($guestID) {
            $builder->where('guest_id', $guestID);
        }

        $itemType = $filters['item_type'] ?? '';
        if ($itemType) {
            $builder->where('item_type', $itemType);
        }

        $selected = $filters['selected'] ?? false;
        if ($selected) {
            $builder->where('selected', true);
        }

        $reference = $filters['reference'] ?? [];
        if ($reference) {
            $builder->whereJsonContains('reference', $reference);
        }

        return fire_hook_filter('repo.cart_item.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        if (system_setting('disable_online_order')) {
            throw new Exception('The online order is disabled.');
        }

        $data    = $this->handleData($data);
        $filters = [
            'sku_code'    => $data['sku_code'],
            'customer_id' => $data['customer_id'],
            'guest_id'    => $data['guest_id'],
            'item_type'   => $data['item_type'],
            'reference'   => $data['reference'],
        ];

        $cart = $this->builder($filters)->first();
        if (empty($cart)) {
            $cart           = new CartItem($data);
            $cart->selected = true;
            $cart->saveOrFail();
        } else {
            $cart->selected = true;
            $cart->saveOrFail();
            $cart->increment('quantity', $data['quantity']);
        }

        return $cart;
    }

    /**
     * @param  $requestData
     * @return array
     */
    private function handleData($requestData): array
    {
        $skuId = $requestData['skuId'] ?? ($requestData['sku_id'] ?? 0);
        if ($skuId) {
            $sku = Sku::query()->findOrFail($skuId);
        } else {
            $sku = Sku::query()->where('code', $requestData['sku_code'] ?? '')->firstOrFail();
        }

        $customerID = $requestData['customer_id'] ?? 0;
        $guestID    = $requestData['guest_id']    ?? 0;
        $product    = $sku->product;
        $itemType   = $this->determineItemType($product, $requestData);

        $cartData = [
            'product_id'  => $sku->product_id,
            'sku_code'    => $sku->code,
            'customer_id' => $customerID,
            'guest_id'    => $customerID ? '' : $guestID,
            'selected'    => true,
            'quantity'    => (int) ($requestData['quantity'] ?? 1),
            'item_type'   => $itemType,
            'reference'   => $requestData['reference'] ?? null,
        ];

        if ($itemType === 'bundle' && empty($cartData['reference'])) {
            $cartData['reference'] = $this->prepareBundleReference($product, $requestData);
        }

        return $cartData;
    }

    /**
     * Get item type by product type.
     *
     * @param  $product
     * @param  $requestData
     * @return string
     */
    private function determineItemType($product, $requestData): string
    {
        if (isset($requestData['item_type']) && ! empty($requestData['item_type'])) {
            return $requestData['item_type'];
        }

        switch ($product->type) {
            case $product::TYPE_BUNDLE:
                return 'bundle';
            case $product::TYPE_NORMAL:
            default:
                return 'normal';
        }
    }

    /**
     * Prepare bundle product reference data.
     *
     * @param  $product
     * @param  $requestData
     * @return array
     */
    private function prepareBundleReference($product, $requestData): array
    {
        $bundleItems = $product->bundles()->with(['sku.product'])->get();
        $bundleData  = [];

        foreach ($bundleItems as $bundleItem) {
            $bundleData[] = [
                'sku_id'        => $bundleItem->sku_id,
                'sku_code'      => $bundleItem->sku->code,
                'product_id'    => $bundleItem->sku->product_id,
                'product_name'  => $bundleItem->sku->product->fallbackName(),
                'variant_label' => $bundleItem->sku->variant_label,
                'quantity'      => $bundleItem->quantity,
                'price'         => $bundleItem->sku->price,
                'image'         => $bundleItem->sku->getImageUrl(100, 100),
            ];
        }

        return [
            'bundles' => $bundleData,
        ];
    }
}
