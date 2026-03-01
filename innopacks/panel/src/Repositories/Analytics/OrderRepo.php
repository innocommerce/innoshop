<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Repositories\Analytics;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Services\StateMachineService;
use InnoShop\Panel\Repositories\BaseRepo;

class OrderRepo extends BaseRepo
{
    /**
     * Get date range from filter
     *
     * @param  string  $dateFilter
     * @param  string|null  $customStartDate
     * @param  string|null  $customEndDate
     * @return array
     */
    public function getDateRange(string $dateFilter, ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $now = Carbon::now();
        switch ($dateFilter) {
            case '':
            case 'all':
                $startDate = null;
                $endDate   = null;
                break;
            case 'today':
                $startDate = $now->format('Y-m-d');
                $endDate   = $now->format('Y-m-d');
                break;
            case 'yesterday':
                $startDate = $now->subDay()->format('Y-m-d');
                $endDate   = $startDate;
                break;
            case 'this_week':
                $startDate = $now->startOfWeek()->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'this_month':
                $startDate = $now->startOfMonth()->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'last_7_days':
                $startDate = Carbon::now()->subDays(6)->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'last_30_days':
                $startDate = Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
            case 'custom':
                $startDate = $customStartDate ?? Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate   = $customEndDate ?? Carbon::now()->format('Y-m-d');
                break;
            default:
                $startDate = Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate   = Carbon::now()->format('Y-m-d');
                break;
        }

        return [
            'start_date' => $startDate ? Carbon::parse($startDate)->startOfDay() : null,
            'end_date'   => $endDate ? Carbon::parse($endDate)->endOfDay() : null,
            'start'      => $startDate,
            'end'        => $endDate,
        ];
    }

    /**
     * Get order statistics summary
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getOrderStatistics(array $dateRange): array
    {
        $filters = ['statuses' => StateMachineService::getValidStatuses()];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        $builder = \InnoShop\Common\Repositories\OrderRepo::getInstance()->builder($filters);

        $totalOrders   = $builder->count();
        $totalRevenue  = (float) $builder->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $orderGrowth = $revenueGrowth = 0;
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $daysDiff      = $dateRange['start_date']->diffInDays($dateRange['end_date']) + 1;
            $previousStart = $dateRange['start_date']->copy()->subDays($daysDiff);
            $previousEnd   = $dateRange['start_date']->copy()->subDay()->endOfDay();

            $previousFilters = [
                'created_at_start' => $previousStart,
                'created_at_end'   => $previousEnd,
                'statuses'         => StateMachineService::getValidStatuses(),
            ];

            $previousBuilder = \InnoShop\Common\Repositories\OrderRepo::getInstance()->builder($previousFilters);
            $previousOrders  = $previousBuilder->count();
            $previousRevenue = (float) $previousBuilder->sum('total');

            $orderGrowth   = $previousOrders > 0 ? (($totalOrders - $previousOrders) / $previousOrders) * 100 : 0;
            $revenueGrowth = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        }

        return [
            'total_orders'    => $totalOrders,
            'total_revenue'   => $totalRevenue,
            'avg_order_value' => $avgOrderValue,
            'order_growth'    => $orderGrowth,
            'revenue_growth'  => $revenueGrowth,
        ];
    }

    /**
     * Get order daily trends
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getOrderDailyTrends(array $dateRange): array
    {
        $filters = ['statuses' => StateMachineService::getValidStatuses()];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
            $trendStart                  = $dateRange['start'];
            $trendEnd                    = $dateRange['end'];
        } else {
            // Default to last 30 days for trends
            $trendStart                  = Carbon::now()->subDays(29)->format('Y-m-d');
            $trendEnd                    = Carbon::now()->format('Y-m-d');
            $filters['created_at_start'] = Carbon::parse($trendStart)->startOfDay();
            $filters['created_at_end']   = Carbon::parse($trendEnd)->endOfDay();
        }

        $orderTotals = \InnoShop\Common\Repositories\OrderRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, count(*) as count, sum(total) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $counts = $totals = [];
        $period = CarbonPeriod::create($trendStart, $trendEnd)->toArray();
        foreach ($period as $date) {
            $dateFormat = $date->format('Y-m-d');
            $orderTotal = $orderTotals[$dateFormat] ?? null;

            $dates[]  = $dateFormat;
            $counts[] = $orderTotal ? $orderTotal->count : 0;
            $totals[] = $orderTotal ? (float) $orderTotal->total : 0;
        }

        return [
            'labels' => $dates,
            'counts' => $counts,
            'totals' => $totals,
        ];
    }

    /**
     * Get order status distribution
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getOrderStatusDistribution(array $dateRange): array
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        $statusData = \InnoShop\Common\Repositories\OrderRepo::getInstance()->builder($filters)
            ->select(DB::raw('status, count(*) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusLabels = [
            'created'   => __('panel/order.created'),
            'unpaid'    => __('panel/order.unpaid'),
            'paid'      => __('panel/order.paid'),
            'shipped'   => __('panel/order.shipped'),
            'completed' => __('panel/order.completed'),
            'cancelled' => __('panel/order.cancelled'),
        ];

        $labels   = $data = $colors = [];
        $colorMap = [
            'created'   => '#6c757d',
            'unpaid'    => '#ffc107',
            'paid'      => '#0d6efd',
            'shipped'   => '#17a2b8',
            'completed' => '#198754',
            'cancelled' => '#dc3545',
        ];

        foreach (array_keys($statusLabels) as $status) {
            $labels[] = $statusLabels[$status];
            $data[]   = isset($statusData[$status]) ? $statusData[$status]->total : 0;
            $colors[] = $colorMap[$status] ?? '#6c757d';
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'colors' => $colors,
        ];
    }

    /**
     * Get pending orders count
     *
     * @param  array  $dateRange
     * @return int
     */
    public function getPendingOrdersCount(array $dateRange): int
    {
        $filters = ['statuses' => [StateMachineService::CREATED, StateMachineService::UNPAID]];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        return \InnoShop\Common\Repositories\OrderRepo::getInstance()->builder($filters)->count();
    }

    /**
     * Get unpaid orders count
     *
     * @param  array  $dateRange
     * @return int
     */
    public function getUnpaidOrdersCount(array $dateRange): int
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        return \InnoShop\Common\Repositories\OrderRepo::getInstance()->builder($filters)
            ->whereIn('status', [StateMachineService::CREATED, StateMachineService::UNPAID])
            ->count();
    }

    /**
     * @return array
     */
    public function getOrderTotalLatestWeek(): array
    {
        $dateRange = $this->getDateRange('last_7_days');
        $trends    = $this->getOrderDailyTrends($dateRange);

        return [
            'period' => $trends['labels'],
            'totals' => $trends['totals'],
        ];
    }

    /**
     * @return array
     */
    public function getOrderTotalLatestMonth(): array
    {
        $dateRange = $this->getDateRange('last_30_days');
        $trends    = $this->getOrderDailyTrends($dateRange);

        return [
            'period' => $trends['labels'],
            'totals' => $trends['totals'],
        ];
    }
}
