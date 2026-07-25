<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Tools;

use InnoShop\Common\Repositories\OrderRepo;

class OrderListTool extends BaseTool
{
    public function name(): string
    {
        return 'order_list';
    }

    public function description(): string
    {
        return 'List orders with pagination. Supports filtering by order number, customer name, or email.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'number'   => ['type' => 'string', 'description' => 'Order number'],
                'customer' => ['type' => 'string', 'description' => 'Customer name keyword'],
                'email'    => ['type' => 'string', 'description' => 'Customer email'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_index';
    }

    public function execute(array $arguments): mixed
    {
        $filters = array_filter([
            'number'        => $arguments['number'] ?? null,
            'customer_name' => $arguments['customer'] ?? null,
            'email'         => $arguments['email'] ?? null,
            'page'          => max(1, (int) ($arguments['page'] ?? 1)),
        ], fn ($value) => ! is_null($value));

        $paginator = OrderRepo::getInstance()->list($filters);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($order) => [
                'number'        => $order->number,
                'customer_name' => $order->customer_name,
                'total'         => $order->total_format,
                'status'        => $order->status,
                'created_at'    => (string) $order->created_at,
            ])->values()->all(),
        ];
    }
}
