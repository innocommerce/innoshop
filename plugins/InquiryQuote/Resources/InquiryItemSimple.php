<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquiryItemSimple extends JsonResource
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
        $sku     = $this->productSku;
        $product = $this->product;

        return [
            'id'                   => $this->id,
            'inquiry_quote_id'     => $this->inquiry_quote_id,
            'product_id'           => $product->id,
            'product_name'         => $product->translation->name ?? '',
            'variant_label'        => $sku->variant_label,
            'sku_id'               => $sku->id,
            'sku_code'             => $sku->code,
            'quantity'             => $this->quantity,
            'inquiry_price'        => $this->inquiry_price,
            'inquiry_price_format' => $this->inquiry_price_format,
            'origin_price'         => $this->origin_price,
            'origin_price_format'  => $this->origin_price_format,
        ];
    }
}
