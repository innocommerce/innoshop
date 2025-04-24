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

class FavoriteItem extends JsonResource
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
        $product = $this->product;
        $sku     = $product->masterSku;
        if (empty($sku)) {
            throw new Exception('Empty SKU for '.$this->id);
        }

        $data = [
            'id'                  => $this->id,
            'sku_id'              => $sku->id,
            'product_id'          => $product->id,
            'slug'                => $product->slug,
            'url'                 => $product->url,
            'name'                => $product->translation->name,
            'summary'             => $product->translation->summary,
            'image_small'         => $sku->getImageUrl(),
            'image_big'           => $sku->getImageUrl(600, 600),
            'price_format'        => $sku->price_format,
            'origin_price_format' => $sku->origin_price_format,
        ];

        return fire_hook_filter('resource.favorite.item', $data);
    }
}
