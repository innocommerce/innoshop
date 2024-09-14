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

class SkuListItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws \Exception
     */
    public function toArray(Request $request): array
    {
        $imagePath = $this->image->path ?? '';
        $imageUrl  = $imagePath ? image_resize($imagePath) : '';

        return [
            'id'                  => $this->id,
            'product_id'          => $this->product_id,
            'product_image_id'    => $this->product_image_id,
            'image'               => $imagePath,
            'image_url'           => $imageUrl,
            'variants'            => $this->variants,
            'model'               => $this->model,
            'code'                => $this->code,
            'price'               => $this->price,
            'price_format'        => $this->price_format,
            'origin_price'        => $this->origin_price,
            'origin_price_format' => $this->origin_price_format,
            'quantity'            => $this->quantity,
            'is_default'          => $this->is_default,
            'position'            => $this->position,
        ];
    }
}
