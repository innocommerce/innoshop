<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories;

use Carbon\Carbon;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;

class DashboardRepo extends BaseRepo
{
    /**
     * Get dashboard cards with growth rates.
     *
     * @return array[]
     */
    public function getCards(): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today's revenue
        $todayRevenue = OrderRepo::getInstance()->builder([
            'statuses'         => StateMachineService::getValidStatuses(),
            'created_at_start' => $today->copy()->startOfDay(),
            'created_at_end'   => $today->copy()->endOfDay(),
        ])->sum('total');

        $yesterdayRevenue = OrderRepo::getInstance()->builder([
            'statuses'         => StateMachineService::getValidStatuses(),
            'created_at_start' => $yesterday->copy()->startOfDay(),
            'created_at_end'   => $yesterday->copy()->endOfDay(),
        ])->sum('total');

        // Today's orders
        $todayOrders = OrderRepo::getInstance()->builder([
            'created_at_start' => $today->copy()->startOfDay(),
            'created_at_end'   => $today->copy()->endOfDay(),
        ])->count();

        $yesterdayOrders = OrderRepo::getInstance()->builder([
            'created_at_start' => $yesterday->copy()->startOfDay(),
            'created_at_end'   => $yesterday->copy()->endOfDay(),
        ])->count();

        // Today's new customers
        $todayCustomers     = Customer::whereDate('created_at', $today)->count();
        $yesterdayCustomers = Customer::whereDate('created_at', $yesterday)->count();

        // Today's visits (unique visitors by IP)
        $todayVisits = Visit::whereDate('first_visited_at', $today)
            ->distinct('ip_address')
            ->count('ip_address');

        $yesterdayVisits = Visit::whereDate('first_visited_at', $yesterday)
            ->distinct('ip_address')
            ->count('ip_address');

        return [
            [
                'title'       => panel_trans('dashboard.today_revenue'),
                'icon'        => 'bi bi-currency-dollar',
                'quantity'    => currency_format($todayRevenue),
                'growth'      => $this->calculateGrowth($todayRevenue, $yesterdayRevenue),
                'growth_type' => 'day',
                'url'         => panel_route('orders.index'),
            ],
            [
                'title'       => panel_trans('dashboard.today_orders'),
                'icon'        => 'bi bi-cart-check',
                'quantity'    => $todayOrders,
                'growth'      => $this->calculateGrowth($todayOrders, $yesterdayOrders),
                'growth_type' => 'day',
                'url'         => panel_route('orders.index'),
            ],
            [
                'title'       => panel_trans('dashboard.new_customers'),
                'icon'        => 'bi bi-person-plus',
                'quantity'    => $todayCustomers,
                'growth'      => $this->calculateGrowth($todayCustomers, $yesterdayCustomers),
                'growth_type' => 'day',
                'url'         => panel_route('customers.index'),
            ],
            [
                'title'       => panel_trans('dashboard.today_visits'),
                'icon'        => 'bi bi-graph-up-arrow',
                'quantity'    => $todayVisits,
                'growth'      => $this->calculateGrowth($todayVisits, $yesterdayVisits),
                'growth_type' => 'day',
                'url'         => panel_route('analytics.index'),
            ],
        ];
    }

    /**
     * Calculate growth rate percentage.
     *
     * @param  float|int  $current
     * @param  float|int  $previous
     * @return float
     */
    private function calculateGrowth(float|int $current, float|int $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
