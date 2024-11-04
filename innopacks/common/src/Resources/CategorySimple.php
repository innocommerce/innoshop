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

class CategorySimple extends JsonResource
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
        return [
            'id'       => $this->id,
            'slug'     => $this->slug,
            'locale'   => $this->translation->locale ?? '',
            'name'     => $this->translation->name   ?? '',
            'url'      => $this->url,
            'image'    => image_resize($this->image, 300, 300),
            'active'   => $this->active,
            'children' => self::collection($this->children)->jsonSerialize(),
        ];
    }
}
