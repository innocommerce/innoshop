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

class OrderListTool extends BaseTool
{
    public function name(): string
    {
        return 'order_list';
    }

    public function description(): string
    {
        return 'List orders with pagination. Supports filtering by order number, customer name, email, status, and date range (YYYY-MM-DD).';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'number'     => ['type' => 'string', 'description' => 'Order number'],
                'customer'   => ['type' => 'string', 'description' => 'Customer name keyword'],
                'email'      => ['type' => 'string', 'description' => 'Customer email'],
                'status'     => ['type' => 'string', 'description' => 'Filter by order status (unpaid, paid, shipped, completed, cancelled)'],
                'start_date' => ['type' => 'string', 'description' => 'Filter orders created on/after this date (YYYY-MM-DD)'],
                'end_date'   => ['type' => 'string', 'description' => 'Filter orders created on/before this date (YYYY-MM-DD)'],
                'page'       => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'   => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
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
            'status'        => $arguments['status'] ?? null,
            'page'          => max(1, (int) ($arguments['page'] ?? 1)),
            'per_page'      => min(50, max(1, (int) ($arguments['per_page'] ?? 10))),
        ], fn ($value) => ! is_null($value));

        if ($startDate = $arguments['start_date'] ?? '') {
            $filters['created_at_start'] = $startDate.' 00:00:00';
        }
        if ($endDate = $arguments['end_date'] ?? '') {
            $filters['created_at_end'] = $endDate.' 23:59:59';
        }

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
