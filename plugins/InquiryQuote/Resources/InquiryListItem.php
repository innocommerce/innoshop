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
use InnoShop\Seller\Resources\SellerSimple;

class InquiryListItem extends JsonResource
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
        $seller  = $product->seller;

        return [
            'id'                      => $this->id,
            'seller_id'               => $product->seller_id ?? 0,
            'quantity'                => $this->quantity,
            'product_id'              => $product->id,
            'product_name'            => $product->translation->name ?? '',
            'variant_label'           => $sku->variant_label,
            'tax_class_id'            => $product->tax_class_id,
            'sku_id'                  => $sku->id,
            'sku_code'                => $sku->code,
            'is_virtual'              => $product->is_virtual,
            'weight'                  => $product->weight,
            'origin_price'            => $this->origin_price,
            'origin_price_format'     => $this->origin_price_format,
            'inquiry_price'           => $this->inquiry_price,
            'inquiry_price_format'    => currency_format($this->inquiry_price),
            'inquiry_subtotal'        => $this->inquiry_subtotal,
            'inquiry_subtotal_format' => $this->inquiry_subtotal_format,
            'image'                   => image_resize($sku->image->path ?? ($product->image->path ?? '')),
            'seller'                  => $seller ? (new SellerSimple($seller))->jsonSerialize() : null,
        ];
    }
}
