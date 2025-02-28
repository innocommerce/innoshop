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
use Illuminate\Support\Str;

class QuoteSimple extends JsonResource
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
            'id'                   => $this->id,
            'number'               => $this->number,
            'customer_id'          => $this->customer_id,
            'shipping_address_id'  => $this->shipping_address_id,
            'shipping_method_code' => $this->shipping_method_code,
            'billing_address_id'   => $this->billing_address_id,
            'billing_method_code'  => $this->billing_method_code,
            'status'               => $this->status,
            'status_format'        => Str::studly($this->status),
            'created_at'           => $this->created_at,
            'total'                => $this->total,
            'total_format'         => currency_format($this->total),
        ];
    }
}
