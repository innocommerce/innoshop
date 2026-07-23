<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkuListItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws Exception
     */
    public function toArray(Request $request): array
    {
        $imagePath      = $this->image ?? '';
        $imageUrl       = $imagePath ? image_resize($imagePath) : '';
        $originImageUrl = $imagePath ? image_origin($imagePath) : '';
        $finalPrice     = $this->getFinalPrice();

        $data = [
            'id'               => $this->id,
            'product_id'       => $this->product_id,
            'product_image_id' => $this->product_image_id,
            'image'            => $imagePath,
            'image_url'        => $imageUrl,
            'origin_image_url' => $originImageUrl,
            // Stable IDs for front-end SKU matching. Front-end JS matches by
            // set equality on variant_value_ids, eliminating the positional
            // index array fragility of the legacy design.
            'variant_value_ids'   => $this->variantValueIds(),
            'variant_values'      => $this->structuredVariantValues(),
            'model'               => $this->model,
            'code'                => $this->code,
            'price'               => $finalPrice,
            'price_format'        => currency_format($finalPrice),
            'origin_price'        => $this->origin_price,
            'origin_price_format' => $this->origin_price_format,
            'quantity'            => $this->quantity,
            'is_default'          => $this->is_default,
            'position'            => $this->position,
            'weight'              => $this->weight,
        ];

        $result = fire_hook_filter('resource.sku.item', $data);

        return $result;
    }

    /**
     * Stable list of value IDs picked by this SKU, ordered by the product's
     * variant dimensions (Color, Size, ...). Use this for front-end matching.
     */
    private function variantValueIds(): array
    {
        $this->loadMissing(['variantValues.variant', 'product.variants']);

        if ($this->variantValues->isEmpty() || $this->product->variants->isEmpty()) {
            return [];
        }

        $valueByVariant = $this->variantValues->keyBy('variant_id');
        $ids            = [];
        foreach ($this->product->variants as $variant) {
            $value = $valueByVariant->get($variant->id);
            if ($value) {
                $ids[] = (string) $value->id;
            }
        }

        return $ids;
    }

    /**
     * Structured variant_values with localized names. Lets the front-end render
     * SKU labels without round-tripping through product.variables.
     */
    private function structuredVariantValues(): array
    {
        $this->loadMissing([
            'variantValues.translation',
            'variantValues.variant.translation',
            'product.variants',
        ]);

        if ($this->variantValues->isEmpty()) {
            return [];
        }

        $valueByVariant = $this->variantValues->keyBy('variant_id');
        $out            = [];
        foreach ($this->product->variants as $variant) {
            $value = $valueByVariant->get($variant->id);
            if (! $value) {
                continue;
            }
            $out[] = [
                'variant_id'   => (string) $variant->id,
                'value_id'     => (string) $value->id,
                'variant_name' => $variant->translation?->name ?? '',
                'value_name'   => $value->translation?->name ?? '',
            ];
        }

        return $out;
    }
}
