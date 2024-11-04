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

class ProductSimple extends JsonResource
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

        return [
            'sku_id'              => $sku->id,
            'product_id'          => $this->id,
            'slug'                => $this->slug,
            'url'                 => $this->url,
            'name'                => $this->translation->name,
            'summary'             => $this->translation->summary,
            'image_small'         => image_resize($sku->image->path ?? ($this->image->path ?? '')),
            'image_big'           => image_resize($sku->image->path ?? ($this->image->path ?? ''), 600, 600),
            'price_format'        => $sku->price_format,
            'origin_price_format' => $sku->origin_price_format,
        ];
    }
}
