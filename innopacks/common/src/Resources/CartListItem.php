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

class CartListItem extends JsonResource
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
        $sku      = $this->productSku;
        $product  = $this->product;
        $subtotal = $this->subtotal;

        return [
            'id'                  => $this->id,
            'quantity'            => $this->quantity,
            'product_id'          => $product->id,
            'product_name'        => $product->translation->name ?? '',
            'variant_label'       => $sku->variant_label,
            'tax_class_id'        => $product->tax_class_id,
            'sku_id'              => $sku->id,
            'sku_code'            => $sku->code,
            'is_virtual'          => $product->is_virtual,
            'price'               => $sku->price,
            'price_format'        => $sku->price_format,
            'origin_price'        => $sku->origin_price,
            'origin_price_format' => $sku->origin_price_format,
            'subtotal'            => $subtotal,
            'subtotal_format'     => currency_format($subtotal),
            'image'               => image_resize($sku->image->path ?? ($product->image->path ?? '')),
            'selected'            => (bool) $this->selected,
        ];
    }
}
