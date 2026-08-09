<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\OrderRepo;
use InvalidArgumentException;

class OrderDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'order_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single order by order number, including line items and shipping info.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'number' => ['type' => 'string', 'description' => 'Order number'],
            ],
            'required' => ['number'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_show';
    }

    public function execute(array $arguments): mixed
    {
        $number = (string) ($arguments['number'] ?? '');
        $order  = OrderRepo::getInstance()->getOrderByNumber($number);
        if (! $order) {
            throw new InvalidArgumentException("Order [{$number}] not found.");
        }

        return [
            'number'        => $order->number,
            'customer_name' => $order->customer_name,
            'email'         => $order->email,
            'total'         => $order->total_format,
            'status'        => $order->status,
            'shipping'      => [
                'method'    => $order->shipping_method_name,
                'recipient' => $order->shipping_customer_name,
                'telephone' => $order->shipping_telephone,
                'address'   => trim(implode(' ', array_filter([
                    $order->shipping_address_1 ?? '',
                    $order->shipping_city ?? '',
                    $order->shipping_country ?? '',
                ]))),
            ],
            'items' => $order->items->map(fn ($item) => [
                'name'     => $item->name,
                'quantity' => $item->quantity,
                'price'    => $item->price,
            ])->values()->all(),
            'created_at' => (string) $order->created_at,
        ];
    }
}
