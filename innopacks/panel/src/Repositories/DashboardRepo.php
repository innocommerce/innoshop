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
use InnoShop\Common\Repositories\VisitRepo;
use InnoShop\Common\Services\StateMachineService;
use InnoShop\Panel\Repositories\Analytics\CustomerRepo;
use InnoShop\Panel\Repositories\Analytics\OrderRepo as AnalyticsOrderRepo;
use InnoShop\Panel\Repositories\Analytics\ProductRepo;

class DashboardRepo extends BaseRepo
{
    /**
     * Get dashboard cards with growth rates (for Panel UI).
     *
     * @return array[]
     */
    public function getCards(): array
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayRevenue     = $this->getRevenue($today);
        $yesterdayRevenue = $this->getRevenue($yesterday);

        $todayOrders     = $this->getOrderCount($today);
        $yesterdayOrders = $this->getOrderCount($yesterday);

        $todayCustomers     = Customer::whereDate('created_at', $today)->count();
        $yesterdayCustomers = Customer::whereDate('created_at', $yesterday)->count();

        $todayVisits     = Visit::whereDate('first_visited_at', $today)->distinct('ip_address')->count('ip_address');
        $yesterdayVisits = Visit::whereDate('first_visited_at', $yesterday)->distinct('ip_address')->count('ip_address');

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
     * Get complete daily report data for a specific date (for API / Plugin).
     *
     * @param  Carbon|string|null  $date  Report date, defaults to yesterday
     * @return array
     */
    public function getDailyReport(Carbon|string|null $date = null): array
    {
        $reportDate   = $date ? Carbon::parse($date) : Carbon::yesterday();
        $previousDate = $reportDate->copy()->subDay();

        $locale = locale_code();

        return [
            'date'             => $reportDate->format('Y-m-d'),
            'date_formatted'   => $reportDate->locale($locale)->isoFormat('MMM D'),
            'week_day'         => $reportDate->locale($locale)->isoFormat('dddd'),
            'summary'          => $this->getSummary($reportDate, $previousDate),
            'orders'           => $this->getOrderAnalysis($reportDate),
            'products'         => $this->getProductAnalysis($reportDate),
            'traffic'          => $this->getTrafficAnalysis($reportDate),
            'customer_sources' => $this->getCustomerSources($reportDate),
        ];
    }

    /**
     * Core metrics overview.
     */
    public function getSummary(Carbon $date, Carbon $prevDate): array
    {
        $revenue     = $this->getRevenue($date);
        $prevRevenue = $this->getRevenue($prevDate);

        $orders     = $this->getOrderCount($date);
        $prevOrders = $this->getOrderCount($prevDate);

        $customers     = Customer::whereDate('created_at', $date)->count();
        $prevCustomers = Customer::whereDate('created_at', $prevDate)->count();

        $visits     = Visit::whereDate('first_visited_at', $date)->distinct('ip_address')->count('ip_address');
        $prevVisits = Visit::whereDate('first_visited_at', $prevDate)->distinct('ip_address')->count('ip_address');

        return [
            'revenue' => [
                'title'     => trans('panel/dashboard.revenue'),
                'value'     => round($revenue, 2),
                'formatted' => currency_format($revenue),
                'growth'    => $this->calculateGrowth($revenue, $prevRevenue),
                'trend'     => $revenue >= $prevRevenue ? 'up' : 'down',
            ],
            'orders' => [
                'title'     => trans('panel/dashboard.orders'),
                'value'     => $orders,
                'formatted' => $orders.' '.trans('panel/dashboard.orders_unit'),
                'growth'    => $this->calculateGrowth($orders, $prevOrders),
                'trend'     => $orders >= $prevOrders ? 'up' : 'down',
            ],
            'customers' => [
                'title'     => trans('panel/dashboard.new_customers'),
                'value'     => $customers,
                'formatted' => $customers.' '.trans('panel/dashboard.customers_unit'),
                'growth'    => $this->calculateGrowth($customers, $prevCustomers),
                'trend'     => $customers >= $prevCustomers ? 'up' : 'down',
            ],
            'visits' => [
                'title'     => trans('panel/dashboard.visits'),
                'value'     => $visits,
                'formatted' => $visits.' UV',
                'growth'    => $this->calculateGrowth($visits, $prevVisits),
                'trend'     => $visits >= $prevVisits ? 'up' : 'down',
            ],
        ];
    }

    /**
     * Order analysis.
     */
    public function getOrderAnalysis(Carbon $date): array
    {
        $dateRange = $this->getDateRange($date);

        $orderRepo = new AnalyticsOrderRepo;

        $distribution = $orderRepo->getOrderStatusDistribution($dateRange);
        $pending      = $orderRepo->getPendingOrdersCount($dateRange);
        $unpaid       = $orderRepo->getUnpaidOrdersCount($dateRange);

        $totalOrders = $this->getOrderCount($date);
        $revenue     = $this->getRevenue($date);
        $avgValue    = $totalOrders > 0 ? $revenue / $totalOrders : 0;

        return [
            'total'               => $totalOrders,
            'status_distribution' => $distribution,
            'pending'             => $pending,
            'unpaid'              => $unpaid,
            'avg_order_value'     => round($avgValue, 2),
            'avg_order_formatted' => currency_format($avgValue),
        ];
    }

    /**
     * Get top selling products by date range.
     *
     * @param  array  $dateRange  Array with start_date and end_date keys
     * @param  int  $limit
     * @return array
     */
    public function getTopSellingProducts(array $dateRange, int $limit = 10): array
    {
        return (new ProductRepo)->getTopSellingProducts($dateRange, $limit);
    }

    /**
     * Product analysis for daily report.
     */
    public function getProductAnalysis(Carbon $date, int $limit = 10): array
    {
        $dateRange = $this->getDateRange($date);

        return [
            'top_products' => $this->getTopSellingProducts($dateRange, $limit),
        ];
    }

    /**
     * Traffic analysis.
     */
    public function getTrafficAnalysis(Carbon $date): array
    {
        $filters   = $this->getDateRange($date);
        $visitRepo = new VisitRepo;

        $statistics   = $visitRepo->getStatistics($filters);
        $deviceStats  = $visitRepo->getVisitsByDeviceType($filters);
        $countryStats = $visitRepo->getVisitsByCountry($filters);

        return [
            'page_views'      => $statistics['page_views'] ?? 0,
            'unique_visitors' => $statistics['unique_visitors'] ?? 0,
            'devices'         => $deviceStats,
            'top_countries'   => array_slice($countryStats, 0, 5),
        ];
    }

    /**
     * Customer sources.
     */
    public function getCustomerSources(Carbon $date): array
    {
        $dateRange    = $this->getDateRange($date);
        $customerRepo = new CustomerRepo;

        return $customerRepo->getCustomerSourceData($dateRange);
    }

    /**
     * Get revenue.
     */
    public function getRevenue(Carbon $date): float
    {
        // Use DATE() function to avoid timezone issues
        $dateStr = $date->format('Y-m-d');

        return OrderRepo::getInstance()->builder([
            'statuses' => StateMachineService::getValidStatuses(),
        ])
            ->whereRaw('DATE(created_at) = ?', [$dateStr])
            ->sum('total');
    }

    /**
     * Get order count.
     */
    public function getOrderCount(Carbon $date): int
    {
        // Use DATE() function to avoid timezone issues
        $dateStr = $date->format('Y-m-d');

        return OrderRepo::getInstance()->builder()
            ->whereRaw('DATE(created_at) = ?', [$dateStr])
            ->count();
    }

    /**
     * Get date range.
     */
    protected function getDateRange(Carbon $date): array
    {
        return [
            'start_date' => $date->copy()->startOfDay(),
            'end_date'   => $date->copy()->endOfDay(),
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
