<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use InnoShop\Common\Models\Product;

/**
 * Synthesizes the legacy `products.variables` JSON shape from the normalized
 * product_variants / product_variant_values tables.
 *
 * Target shape (consumed by Panel Vue editor + front jQuery + API clients):
 *
 *   [
 *     [
 *       'name'    => ['en' => 'Color', 'zh-cn' => '颜色'],
 *       'isImage' => false,
 *       'values'  => [
 *         ['name' => ['en' => 'Red', 'zh-cn' => '红色'], 'image' => 'red.png'],
 *         ...
 *       ],
 *     ],
 *     ...
 *   ]
 *
 * Used by Product::getVariablesAttribute() so every `$product->variables`
 * caller stays source-agnostic after the read path switches to normalized.
 */
class LegacyVariablesBuilder
{
    public function __construct(private Product $product) {}

    /**
     * Build the legacy shape. Requires the product to have its variants and
     * values pre-loaded with translations; callers should eager-load via
     * ProductRepo::with(['variants.translations', 'variants.values.translations']).
     */
    public function build(): array
    {
        $out = [];
        foreach ($this->product->variants as $variant) {
            $names = [];
            foreach ($variant->translations as $t) {
                if ($t->name !== null && $t->name !== '') {
                    $names[$t->locale] = $t->name;
                }
            }

            $values = [];
            foreach ($variant->values as $value) {
                $valueNames = [];
                foreach ($value->translations as $vt) {
                    if ($vt->name !== null && $vt->name !== '') {
                        $valueNames[$vt->locale] = $vt->name;
                    }
                }
                $values[] = [
                    'name'  => $valueNames,
                    'image' => $value->image ?? '',
                ];
            }

            $out[] = [
                'name'    => $names,
                'isImage' => (bool) $variant->is_image,
                'values'  => $values,
            ];
        }

        return $out;
    }
}
