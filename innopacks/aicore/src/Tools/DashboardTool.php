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
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Panel\Repositories\DashboardRepo;

class DashboardTool extends BaseTool
{
    public function name(): string
    {
        return 'dashboard';
    }

    public function description(): string
    {
        return 'Business overview for today or a specific date: order count, revenue, new customers, with day-over-day comparison.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'date' => ['type' => 'string', 'description' => 'Date to query (YYYY-MM-DD), default today'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'analytics_index';
    }

    public function execute(array $arguments): mixed
    {
        $dashboard = DashboardRepo::getInstance();

        $date      = Carbon::parse($arguments['date'] ?? now());
        $yesterday = (clone $date)->subDay();

        $todayRevenue       = $dashboard->getRevenue($date);
        $yesterdayRevenue   = $dashboard->getRevenue($yesterday);
        $todayCount         = $dashboard->getOrderCount($date);
        $yesterdayCount     = $dashboard->getOrderCount($yesterday);
        $todayCustomers     = Customer::query()->whereDate('created_at', $date)->count();
        $yesterdayCustomers = Customer::query()->whereDate('created_at', $yesterday)->count();

        $byStatus = Order::query()
            ->whereDate('created_at', $date)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        return [
            'date'   => $date->toDateString(),
            'orders' => [
                'count'      => $todayCount,
                'prev_count' => $yesterdayCount,
                'change_pct' => $this->pctChange($todayCount, $yesterdayCount),
            ],
            'revenue' => [
                'amount'      => round($todayRevenue, 2),
                'prev_amount' => round($yesterdayRevenue, 2),
                'change_pct'  => $this->pctChange($todayRevenue, $yesterdayRevenue),
            ],
            'new_customers' => [
                'count'      => $todayCustomers,
                'prev_count' => $yesterdayCustomers,
                'change_pct' => $this->pctChange($todayCustomers, $yesterdayCustomers),
            ],
            'total_customers'  => Customer::query()->count(),
            'total_orders_all' => Order::query()->count(),
            'orders_by_status' => $byStatus,
        ];
    }

    private function pctChange(float|int $today, float|int $yesterday): ?float
    {
        if ($yesterday == 0) {
            return $today > 0 ? null : 0;
        }

        return round((($today - $yesterday) / $yesterday) * 100, 1);
    }
}
