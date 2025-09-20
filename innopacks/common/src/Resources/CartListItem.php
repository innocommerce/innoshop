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
        $price    = $this->price;

        $optionValues = [];
        if ($this->optionValues) {
            foreach ($this->optionValues as $optionValue) {
                $optionName      = $optionValue->getLocalizedOptionName();
                $optionValueName = $optionValue->getLocalizedOptionValueName();

                $optionValues[] = [
                    'option_id'               => $optionValue->option_id, // 正确的选项ID
                    'option_value_id'         => $optionValue->option_value_id, // 正确的选项值ID
                    'option_name'             => $optionName, // 使用本地化的选项名称
                    'option_value_name'       => $optionValueName, // 使用本地化的选项值名称
                    'price_adjustment'        => $optionValue->price_adjustment,
                    'price_adjustment_format' => currency_format($optionValue->price_adjustment),
                ];
            }
        }

        $data = [
            'id'                  => $this->id,
            'quantity'            => $this->quantity,
            'product_id'          => $product->id,
            'product_name'        => $product->translation->name ?? '',
            'variant_label'       => $sku->variant_label,
            'tax_class_id'        => $product->tax_class_id,
            'sku_id'              => $sku->id,
            'sku_code'            => $sku->code,
            'is_virtual'          => $product->is_virtual,
            'weight'              => $product->weight,
            'price'               => $price,
            'price_format'        => currency_format($price),
            'origin_price'        => $sku->origin_price,
            'origin_price_format' => $sku->origin_price_format,
            'subtotal'            => $subtotal,
            'subtotal_format'     => currency_format($subtotal),
            'image'               => $sku->getImageUrl(),
            'url'                 => $product->url,
            'item_type'           => $this->item_type,
            'item_type_label'     => $this->item_type_label,
            'reference'           => $this->reference,
            'selected'            => (bool) $this->selected,
            'is_stock_enough'     => $this->is_stock_enough,
            'options'             => $optionValues,
        ];

        return fire_hook_filter('resource.cart.item', $data);
    }
}
