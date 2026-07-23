<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Product;

class VariantRepo
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Full-sync a product's variant dimensions and values from the legacy
     * variables JSON shape. Wipes pre-existing normalized rows first to
     * guarantee idempotency.
     *
     * Returns a 2-level position-keyed map so callers can translate the
     * legacy `sku.variants = [0, 1]` index array into value_ids without
     * a second round-trip:
     *   [
     *     'variantIdMap' => [variant_position => variant_id],
     *     'valueIdMap'   => [variant_position => [value_position => value_id]],
     *   ]
     *
     * @param  Product  $product
     * @param  array  $variableDefs  Legacy shape: [{name:{locale}, isImage, values:[{name:{locale}, image}]}]
     * @return array{variantIdMap: array, valueIdMap: array}
     */
    public function syncVariables(Product $product, array $variableDefs): array
    {
        $this->clearProductNormalizedData($product->id);

        $now          = now();
        $variantIdMap = [];
        $valueIdMap   = [];

        foreach (array_values($variableDefs) as $vPos => $variant) {
            if (! is_array($variant)) {
                continue;
            }
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $product->id,
                'position'   => $vPos,
                'is_image'   => (bool) ($variant['isImage'] ?? false),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $variantIdMap[$vPos] = $variantId;

            foreach ($variant['name'] ?? [] as $locale => $name) {
                if (! is_string($name) || $name === '') {
                    continue;
                }
                DB::table('product_variant_translations')->insert([
                    'variant_id' => $variantId,
                    'locale'     => $locale,
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $valueIdMap[$vPos] = [];
            foreach ($variant['values'] ?? [] as $valPos => $value) {
                if (! is_array($value)) {
                    continue;
                }
                $valueId = DB::table('product_variant_values')->insertGetId([
                    'variant_id' => $variantId,
                    'image'      => is_string($value['image'] ?? null) ? $value['image'] : '',
                    'position'   => $valPos,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $valueIdMap[$vPos][$valPos] = $valueId;

                foreach ($value['name'] ?? [] as $locale => $name) {
                    if (! is_string($name) || $name === '') {
                        continue;
                    }
                    DB::table('product_variant_value_translations')->insert([
                        'value_id'   => $valueId,
                        'locale'     => $locale,
                        'name'       => $name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        return ['variantIdMap' => $variantIdMap, 'valueIdMap' => $valueIdMap];
    }

    /**
     * Remove pre-existing normalized rows for a product (idempotent re-runs).
     */
    public function clearProductNormalizedData(int $productId): void
    {
        $variantIds = DB::table('product_variants')
            ->where('product_id', $productId)
            ->pluck('id')
            ->all();

        if (empty($variantIds)) {
            return;
        }

        $valueIds = DB::table('product_variant_values')
            ->whereIn('variant_id', $variantIds)
            ->pluck('id')
            ->all();

        DB::table('product_variant_value_translations')->whereIn('value_id', $valueIds)->delete();
        DB::table('product_variant_values')->whereIn('variant_id', $variantIds)->delete();
        DB::table('product_variant_translations')->whereIn('variant_id', $variantIds)->delete();
        DB::table('product_variants')->where('product_id', $productId)->delete();

        $skuIds = DB::table('product_skus')->where('product_id', $productId)->pluck('id')->all();
        if (! empty($skuIds)) {
            DB::table('product_sku_variant_values')->whereIn('sku_id', $skuIds)->delete();
        }
    }
}
