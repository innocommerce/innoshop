<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\OrderRepo;
use InvalidArgumentException;

class CustomerDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'customer_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single customer by ID, including address count and recent orders.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Customer ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'customers_index';
    }

    public function execute(array $arguments): mixed
    {
        $customer = Customer::query()->find((int) ($arguments['id'] ?? 0));
        if (! $customer) {
            throw new InvalidArgumentException("Customer [{$arguments['id']}] not found.");
        }

        $recentOrders = OrderRepo::getInstance()->list([
            'customer_id' => $customer->id,
            'page'        => 1,
            'per_page'    => 5,
        ]);

        return [
            'id'            => $customer->id,
            'name'          => $customer->name,
            'email'         => $customer->email,
            'telephone'     => $customer->telephone,
            'calling_code'  => $customer->calling_code,
            'avatar'        => $customer->avatar,
            'locale'        => $customer->locale,
            'active'        => (bool) $customer->active,
            'from'          => $customer->from,
            'group_id'      => $customer->customer_group_id,
            'address_count' => $customer->addresses()->count(),
            'addresses'     => $customer->addresses->map(fn ($addr) => [
                'id'        => $addr->id,
                'name'      => $addr->name,
                'phone'     => $addr->phone,
                'address_1' => $addr->address_1,
                'city'      => $addr->city,
                'country'   => $addr->country_name,
            ])->values()->all(),
            'recent_orders' => $recentOrders->map(fn ($order) => [
                'number'     => $order->number,
                'total'      => $order->total_format,
                'status'     => $order->status,
                'created_at' => (string) $order->created_at,
            ])->values()->all(),
            'total_orders' => OrderRepo::getInstance()->list([
                'customer_id' => $customer->id,
                'per_page'    => 1,
            ])->total(),
            'created_at' => (string) $customer->created_at,
        ];
    }
}
