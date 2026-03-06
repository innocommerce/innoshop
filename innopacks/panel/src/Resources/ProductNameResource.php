<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product name resource class
 * Specifically handles name retrieval for product models
 */
class ProductNameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id'   => $this->id,
            'name' => $this->getName(),
        ];
    }

    /**
     * Get product name
     * Uses fallbackName() method
     *
     * @return string
     */
    private function getName(): string
    {
        return $this->resource->fallbackName();
    }
}
