<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Models\Product\Sku;

class StockService extends BaseService
{
    /**
     * Cache time in seconds
     */
    const CACHE_TTL = 300; // 5 minutes

    /**
     * Check stock by sku ID
     *
     * @param  int  $skuId  SKU ID
     * @param  int  $quantity  Quantity to check
     * @param  bool  $forceCheck  Force check (ignore cache)
     * @return bool
     */
    public function checkStockBySkuId(int $skuId, int $quantity, bool $forceCheck = false): bool
    {
        $sku = Sku::query()->find($skuId);
        if (! $sku) {
            return false;
        }

        return $this->checkStock($sku->code, $quantity, $forceCheck);
    }

    /**
     * Check stock by cart item
     *
     * @param  CartItem  $cartItem  Cart item
     * @param  int  $quantity  Quantity to check
     * @return bool
     */
    public function checkStockByCartItem(CartItem $cartItem, int $quantity): bool
    {
        $quantity = $cartItem->quantity + $quantity;

        return $this->checkStock($cartItem->sku_code, $quantity, $cartItem->id);
    }

    /**
     * Check if SKU stock is available
     *
     * @param  string  $skuCode  SKU code
     * @param  int  $quantity  Quantity to check
     * @param  bool  $forceCheck  Force check (ignore cache)
     * @return bool
     */
    public function checkStock(string $skuCode, int $quantity, bool $forceCheck = false): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        if (system_setting('allow_out_of_stock', false)) {
            return true;
        }

        // Get SKU info (with cache)
        $sku = $this->getSku($skuCode, $forceCheck);
        if (! $sku) {
            return false;
        }

        return $sku->quantity >= $quantity;
    }

    /**
     * Get available stock for SKU
     *
     * @param  string  $skuCode  SKU code
     * @param  bool  $forceCheck  Force check (ignore cache)
     * @return int
     */
    public function getAvailableStock(string $skuCode, bool $forceCheck = false): int
    {
        // If negative stock is allowed, return maximum quantity
        if (system_setting('allow_out_of_stock', false)) {
            return 999999;
        }

        // Get SKU info (with cache)
        $sku = $this->getSku($skuCode, $forceCheck);
        if (! $sku) {
            return 0;
        }

        // No reserved quantity logic needed
        return max(0, $sku->quantity);
    }

    /**
     * Get SKU info (with cache)
     *
     * @param  string  $skuCode  SKU code
     * @param  bool  $forceCheck  Force check (ignore cache)
     * @return Sku|null
     */
    protected function getSku(string $skuCode, bool $forceCheck = false): ?Sku
    {
        $cacheKey = "sku:$skuCode";

        if (! $forceCheck && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $sku = Sku::query()->where('code', $skuCode)->first();

        if ($sku) {
            Cache::put($cacheKey, $sku, self::CACHE_TTL);
        }

        return $sku;
    }

    /**
     * Clear SKU related cache
     *
     * @param  string  $skuCode  SKU code
     * @return void
     */
    public function clearCache(string $skuCode): void
    {
        Cache::forget("sku:$skuCode");
        Cache::forget("sku_reserved:$skuCode");
    }

    /**
     * Batch check stock for multiple SKUs
     *
     * @param  array  $skuQuantities  [['sku_code' => 'xxx', 'quantity' => 1], ...]
     * @param  bool  $forceCheck  Force check (ignore cache)
     * @return array [['sku_code' => 'xxx', 'available' => true/false, 'available_quantity' => 10], ...]
     */
    public function batchCheckStock(array $skuQuantities, bool $forceCheck = false): array
    {
        $results = [];

        foreach ($skuQuantities as $item) {
            $skuCode  = $item['sku_code'];
            $quantity = $item['quantity'];

            $results[] = [
                'sku_code'           => $skuCode,
                'available'          => $this->checkStock($skuCode, $quantity, $forceCheck),
                'available_quantity' => $this->getAvailableStock($skuCode, $forceCheck),
            ];
        }

        return $results;
    }
}
