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

        $images = [];
        foreach ($this->images as $image) {
            $images[] = image_resize($image->path, 600, 600);
        }

        $skuImagePath = $sku->image->path ?? '';
        if ($skuImagePath) {
            $imageUrl = image_resize($skuImagePath, 600, 600);
            if (! in_array($imageUrl, $images)) {
                $images[] = $imageUrl;
            }
        }

        return [
            'sku_id'              => $sku->id,
            'product_id'          => $this->id,
            'slug'                => $this->slug,
            'url'                 => $this->url,
            'name'                => $this->translation->name,
            'summary'             => $this->translation->summary,
            'content'             => $this->translation->content,
            'image_small'         => image_resize($sku->image->path ?? ($this->image->path ?? '')),
            'images'              => $images,
            'price_format'        => $sku->price_format,
            'origin_price_format' => $sku->origin_price_format,
            'sku'                 => (new SkuListItem($sku))->jsonSerialize(),
            'skus'                => SkuListItem::collection($this->skus)->jsonSerialize(),
            'variants'            => $this->variables,
            'attributes'          => $this->groupedAttributes(),
        ];
    }
}
