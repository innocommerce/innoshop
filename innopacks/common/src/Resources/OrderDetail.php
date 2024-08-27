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

class OrderDetail extends JsonResource
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
            'id'                     => $this->id,
            'number'                 => $this->number,
            'customer_id'            => $this->customer_id,
            'customer_group_id'      => $this->customer_group_id,
            'shipping_address_id'    => $this->shipping_address_id,
            'billing_address_id'     => $this->billing_address_id,
            'customer_name'          => $this->customer_name,
            'email'                  => $this->email,
            'calling_code'           => $this->calling_code,
            'telephone'              => $this->telephone,
            'total'                  => $this->total,
            'locale'                 => $this->locale,
            'currency_code'          => $this->currency_code,
            'currency_value'         => $this->currency_value,
            'ip'                     => $this->ip,
            'user_agent'             => $this->user_agent,
            'status'                 => $this->status,
            'shipping_method_code'   => $this->shipping_method_code,
            'shipping_method_name'   => $this->shipping_method_name,
            'shipping_customer_name' => $this->shipping_customer_name,
            'shipping_calling_code'  => $this->shipping_calling_code,
            'shipping_telephone'     => $this->shipping_telephone,
            'shipping_country'       => $this->shipping_country,
            'shipping_country_id'    => $this->shipping_country_id,
            'shipping_state_id'      => $this->shipping_state_id,
            'shipping_state'         => $this->shipping_state,
            'shipping_city'          => $this->shipping_city,
            'shipping_address_1'     => $this->shipping_address_1,
            'shipping_address_2'     => $this->shipping_address_2,
            'shipping_zipcode'       => $this->shipping_zipcode,
            'billing_method_code'    => $this->billing_method_code,
            'billing_method_name'    => $this->billing_method_name,
            'billing_customer_name'  => $this->billing_customer_name,
            'billing_calling_code'   => $this->billing_calling_code,
            'billing_telephone'      => $this->billing_telephone,
            'billing_country'        => $this->billing_country,
            'billing_country_id'     => $this->billing_country_id,
            'billing_state_id'       => $this->billing_state_id,
            'billing_state'          => $this->billing_state,
            'billing_city'           => $this->billing_city,
            'billing_address_1'      => $this->billing_address_1,
            'billing_address_2'      => $this->billing_address_2,
            'billing_zipcode'        => $this->billing_zipcode,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
            'deleted_at'             => $this->deleted_at,
            'total_format'           => $this->total_format,
            'status_format'          => $this->status_format,
            'quantity_total'         => $this->items->sum('quantity'),
            'items'                  => OrderItemSimple::collection($this->items),
            'fees'                   => OrderFeeSimple::collection($this->fees),
        ];
    }
}
