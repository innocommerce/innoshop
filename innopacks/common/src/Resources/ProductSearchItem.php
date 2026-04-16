<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSearchItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $sku = $this->masterSku;

        return [
            'id'                  => $this->id,
            'name'                => $this->fallbackName(),
            'url'                 => $this->url,
            'image_url'           => $this->image_url,
            'sku_code'            => $sku->code ?? '',
            'price_format'        => $sku->price_format ?? '',
            'origin_price_format' => $sku->origin_price_format ?? '',
        ];
    }
}
