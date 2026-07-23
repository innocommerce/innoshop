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

class ProductDetail extends JsonResource
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
        $sku = $this->masterSku;
        if (empty($sku)) {
            throw new Exception('Empty SKU for '.$this->id);
        }

        $images = [];
        foreach ($this->images as $image) {
            $resizedImage = image_resize($image, 600, 600);
            if ($resizedImage) {
                $images[] = $resizedImage;
            }
        }

        $skuImagePath = $sku->image;
        if ($skuImagePath) {
            $imageUrl = image_resize($skuImagePath, 600, 600);
            if ($imageUrl && ! in_array($imageUrl, $images)) {
                $images[] = $imageUrl;
            }
        }

        $data = [
            'id'                  => $this->id,
            'sku_id'              => $sku->id,
            'product_id'          => $this->id,
            'slug'                => $this->slug,
            'url'                 => $this->url,
            'name'                => $this->translation->name,
            'summary'             => $this->translation->summary,
            'content'             => $this->translation->content,
            'image_small'         => $sku->getImageUrl(),
            'images'              => $images,
            'price_format'        => $sku->price_format,
            'origin_price_format' => $sku->origin_price_format,
            'sku'                 => (new SkuListItem($sku))->jsonSerialize(),
            'skus'                => SkuListItem::collection($this->skus)->jsonSerialize(),
            // Structured variant dimensions for front-end ID-based matching.
            'variant_dimensions' => $this->structuredVariantDimensions(),
            'attributes'         => $this->groupedAttributes(),
            'sales'              => $this->sales,
            'viewed'             => $this->viewed,
            'rating'             => (float) $this->rating,
            'reviews_count'      => (int) $this->reviews_count,
            'category_id'        => $this->categories()->first()?->id,
            'related'            => ProductSimple::collection($this->relationProducts),
        ];

        return fire_hook_filter('resource.product.detail', $data);
    }

    /**
     * Build the structured variant dimensions shape consumed by the new
     * ID-based front-end. Each value carries a stable `id` (DB value_id cast
     * to string) that the front-end uses for SKU matching without depending
     * on positional ordering.
     */
    private function structuredVariantDimensions(): array
    {
        $this->resource->loadMissing([
            'variants.translations',
            'variants.values.translations',
        ]);

        $out = [];
        foreach ($this->resource->variants as $variant) {
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
                    'id'    => (string) $value->id,
                    'image' => $value->image ?? '',
                    'name'  => $valueNames,
                ];
            }

            $out[] = [
                'id'       => (string) $variant->id,
                'is_image' => (bool) $variant->is_image,
                'name'     => $names,
                'values'   => $values,
            ];
        }

        return $out;
    }
}
