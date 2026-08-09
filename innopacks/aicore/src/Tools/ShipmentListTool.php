<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Order\Shipment;

class ShipmentListTool extends BaseTool
{
    public function name(): string
    {
        return 'shipment_list';
    }

    public function description(): string
    {
        return 'List order shipments with pagination. Supports filtering by order number.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'order_number' => ['type' => 'string', 'description' => 'Filter by order number'],
                'page'         => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'     => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'orders_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = Shipment::query()->with('order');

        if ($orderNumber = $arguments['order_number'] ?? '') {
            $query->whereHas('order', function ($q) use ($orderNumber) {
                $q->where('number', 'like', "%{$orderNumber}%");
            });
        }

        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($arguments['per_page'] ?? 10)));

        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($shipment) => [
                'id'              => $shipment->id,
                'order_number'    => $shipment->order->number ?? '',
                'express_code'    => $shipment->express_code,
                'express_company' => $shipment->express_company,
                'express_number'  => $shipment->express_number,
                'created_at'      => (string) $shipment->created_at,
            ])->values()->all(),
        ];
    }
}
