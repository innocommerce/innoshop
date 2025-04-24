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
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;

class CustomerDetail extends JsonResource
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
        $customerOrders = Order::query()->where('customer_id', $this->id)->get();

        $data = [
            'id'                  => $this->id,
            'email'               => $this->email,
            'name'                => $this->name,
            'avatar'              => image_resize($this->avatar),
            'locale'              => $this->locale,
            'has_password'        => $this->has_password,
            'unpaid_order_total'  => $customerOrders->where('status', StateMachineService::UNPAID)->count(),
            'paid_order_total'    => $customerOrders->where('status', StateMachineService::PAID)->count(),
            'shipped_order_total' => $customerOrders->where('status', StateMachineService::SHIPPED)->count(),
        ];

        return fire_hook_filter('resource.customer.detail', $data);
    }
}
