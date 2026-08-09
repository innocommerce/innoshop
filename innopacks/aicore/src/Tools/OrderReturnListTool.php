<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\OrderReturn;

class OrderReturnListTool extends BaseTool
{
    public function name(): string
    {
        return 'order_return_list';
    }

    public function description(): string
    {
        return 'List order returns (RMA) with pagination. Supports filtering by order number, customer name, or status.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'order_number'  => ['type' => 'string', 'description' => 'Filter by order number'],
                'customer_name' => ['type' => 'string', 'description' => 'Filter by customer name keyword'],
                'status'        => ['type' => 'string', 'description' => 'Filter by return status'],
                'page'          => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'      => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'order_returns_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = OrderReturn::query()->with('order');

        if ($orderNumber = $arguments['order_number'] ?? '') {
            $query->whereHas('order', function ($q) use ($orderNumber) {
                $q->where('number', 'like', "%{$orderNumber}%");
            });
        }
        if ($customerName = $arguments['customer_name'] ?? '') {
            $query->where('customer_name', 'like', "%{$customerName}%");
        }
        if ($status = $arguments['status'] ?? '') {
            $query->where('status', $status);
        }

        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($arguments['per_page'] ?? 10)));

        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($r) => [
                'id'            => $r->id,
                'return_number' => $r->return_number ?? $r->number ?? '',
                'order_number'  => $r->order->number ?? '',
                'customer_name' => $r->customer_name,
                'status'        => $r->status,
                'total'         => $r->total,
                'created_at'    => (string) $r->created_at,
            ])->values()->all(),
        ];
    }
}
