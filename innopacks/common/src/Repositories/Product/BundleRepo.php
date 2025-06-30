<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

use Illuminate\Support\Collection;
use InnoShop\Common\Models\Product;
use Throwable;

class BundleRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Handle product bundle information
     *
     * @param  Product  $product
     * @param  array  $bundles
     * @return void
     * @throws Throwable
     */
    public function handleBundles(Product $product, array $bundles): void
    {
        if (empty($bundles)) {
            return;
        }

        // Delete existing bundles
        $product->bundles()->delete();

        $bundleData = [];
        foreach ($bundles as $bundle) {
            if (empty($bundle['sku_id']) || empty($bundle['quantity'])) {
                continue;
            }

            $bundleData[] = [
                'sku_id'   => $bundle['sku_id'],
                'quantity' => (int) ($bundle['quantity'] ?? 1),
            ];
        }

        if ($bundleData) {
            $product->bundles()->createMany($bundleData);
        }
    }

    /**
     * Delete product bundles
     *
     * @param  Product  $product
     * @return void
     */
    public function deleteBundles(Product $product): void
    {
        $product->bundles()->delete();
    }

    /**
     * Copy product bundles
     *
     * @param  Product  $originalProduct
     * @param  Product  $newProduct
     * @return void
     */
    public function copyBundles(Product $originalProduct, Product $newProduct): void
    {
        $bundles = $originalProduct->bundles;

        if ($bundles->isEmpty()) {
            return;
        }

        $bundleData = [];
        foreach ($bundles as $bundle) {
            $bundleData[] = [
                'sku_id'   => $bundle->sku_id,
                'quantity' => $bundle->quantity,
            ];
        }

        $newProduct->bundles()->createMany($bundleData);
    }

    /**
     * Get bundle items with related product data for display
     *
     * @param  Product  $product
     * @return Collection
     */
    public function getBundleItemsForDisplay(Product $product): Collection
    {
        if ($product->type !== Product::TYPE_BUNDLE) {
            return collect();
        }

        return $product->bundles()
            ->with(['sku.product.translation'])
            ->get();
    }
}
