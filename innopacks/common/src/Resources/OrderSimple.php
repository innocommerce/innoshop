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

class OrderSimple extends JsonResource
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
            'id'                  => $this->id,
            'number'              => $this->number,
            'customer_id'         => $this->customer_id,
            'customer_group_id'   => $this->customer_group_id,
            'shipping_address_id' => $this->shipping_address_id,
            'billing_address_id'  => $this->billing_address_id,
            'customer_name'       => $this->customer_name,
            'email'               => $this->email,
            'calling_code'        => $this->calling_code,
            'telephone'           => $this->telephone,
            'total'               => $this->total,
            'locale'              => $this->locale,
            'currency_code'       => $this->currency_code,
            'currency_value'      => $this->currency_value,
            'ip'                  => $this->ip,
            'user_agent'          => $this->user_agent,
            'status'              => $this->status,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'deleted_at'          => $this->deleted_at,
            'total_format'        => $this->total_format,
            'status_format'       => $this->status_format,
            'comment'             => $this->comment,
            'admin_note'          => $this->admin_note,
            'quantity_total'      => $this->items->sum('quantity'),
        ];
    }
}
