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

class OrderItemSimple extends JsonResource
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
        // 获取选项值信息
        $options = [];
        if ($this->optionValues) {
            foreach ($this->optionValues as $optionValue) {
                $options[] = [
                    'option_id'               => $optionValue->option_id,
                    'option_value_id'         => $optionValue->option_value_id,
                    'option_name'             => $optionValue->getLocalizedOptionName(),
                    'option_value_name'       => $optionValue->getLocalizedOptionValueName(),
                    'price_adjustment'        => $optionValue->price_adjustment,
                    'price_adjustment_format' => currency_format($optionValue->price_adjustment),
                ];
            }
        }

        $data = [
            'id'              => $this->id,
            'order_id'        => $this->order_id,
            'product_id'      => $this->product_id,
            'order_number'    => $this->order_number,
            'product_sku'     => $this->product_sku,
            'variant_label'   => $this->variant_label,
            'name'            => $this->name,
            'image'           => $this->image,
            'quantity'        => $this->quantity,
            'price'           => $this->price,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'deleted_at'      => $this->deleted_at,
            'subtotal'        => $this->subtotal,
            'price_format'    => $this->price_format,
            'subtotal_format' => $this->subtotal_format,
            'has_review'      => $this->has_review,
            'item_type'       => $this->item_type,
            'item_type_label' => $this->item_type_label,
            'reference'       => $this->reference,
            'options'         => $options,
        ];

        return fire_hook_filter('resource.order.item.simple', $data);
    }
}
