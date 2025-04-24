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
        if (empty($sku)) {
            throw new Exception('Empty SKU for '.$this->id);
        }

        $data = [
            'id'                  => $this->id,
            'master_sku_id'       => $sku->id,
            'slug'                => $this->slug,
            'url'                 => $this->url,
            'name'                => $this->fallbackName('name'),
            'summary'             => $this->fallbackName('summary'),
            'image_small'         => $sku->getImageUrl(),
            'image_big'           => $sku->getImageUrl(600, 600),
            'price_format'        => $sku->price_format,
            'origin_price_format' => $sku->origin_price_format,
            'sales'               => $this->sales,
            'viewed'              => $this->viewed,
            'active'              => (bool) $this->active,
        ];

        return fire_hook_filter('resource.product.simple', $data);
    }
}
