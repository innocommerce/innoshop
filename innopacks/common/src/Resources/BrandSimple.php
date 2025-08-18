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

class BrandSimple extends JsonResource
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
        $data = [
            'id'            => $this->id,
            'first'         => $this->first,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'logo_url'      => image_resize($this->logo),
            'logo_original' => $this->logo, // 原图
            'logo_large'    => image_resize($this->logo, 300, 150), // 大图，适合横向logo (2:1比例)
            'logo_medium'   => image_resize($this->logo, 200, 100), // 中等尺寸 (2:1比例)
            'logo_small'    => image_resize($this->logo, 120, 60), // 小尺寸 (2:1比例)
            'active'        => (bool) $this->active,
        ];

        return fire_hook_filter('resource.brand.simple', $data);
    }
}
