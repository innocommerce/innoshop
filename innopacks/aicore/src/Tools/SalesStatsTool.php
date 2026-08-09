<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Support\Carbon;
use InnoShop\Common\Models\Order;

class SalesStatsTool extends BaseTool
{
    public function name(): string
    {
        return 'sales_stats';
    }

    public function description(): string
    {
        return 'Sales statistics for a date range: order count, revenue, and average order value, plus per-status breakdown.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'start_date' => ['type' => 'string', 'description' => 'Start date (YYYY-MM-DD), default 30 days ago'],
                'end_date'   => ['type' => 'string', 'description' => 'End date (YYYY-MM-DD), default today'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'analytics_index';
    }

    public function execute(array $arguments): mixed
    {
        $start = Carbon::parse($arguments['start_date'] ?? now()->subDays(30))->startOfDay();
        $end   = Carbon::parse($arguments['end_date'] ?? now())->endOfDay();

        $query = Order::query()->whereBetween('created_at', [$start, $end]);

        $orderCount = (clone $query)->count();
        $revenue    = (float) (clone $query)->sum('total');
        $byStatus   = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        return [
            'start_date'          => $start->toDateString(),
            'end_date'            => $end->toDateString(),
            'order_count'         => $orderCount,
            'revenue'             => round($revenue, 2),
            'average_order_value' => $orderCount > 0 ? round($revenue / $orderCount, 2) : 0,
            'orders_by_status'    => $byStatus,
        ];
    }
}
