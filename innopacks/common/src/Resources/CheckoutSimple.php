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

class CheckoutSimple extends JsonResource
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
        $parseShipping  = explode('.', $this->shipping_method_code);
        $shippingPlugin = plugin($parseShipping[0]);
        $billingPlugin  = plugin($this->billing_method_code);

        return [
            'id'                   => $this->id,
            'customer_id'          => $this->customer_id,
            'guest_id'             => $this->guest_id,
            'shipping_address_id'  => $this->shipping_address_id,
            'shipping_method_code' => $this->shipping_method_code,
            'shipping_method_name' => $shippingPlugin ? $shippingPlugin->getLocaleName() : '',
            'billing_address_id'   => $this->billing_address_id,
            'billing_method_code'  => $this->billing_method_code,
            'billing_method_name'  => $billingPlugin ? $billingPlugin->getLocaleName() : '',
            'reference'            => $this->reference,
        ];
    }
}
