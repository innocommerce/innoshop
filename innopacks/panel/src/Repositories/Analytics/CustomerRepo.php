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
use InnoShop\Common\Models\Customer;
use InnoShop\Panel\Repositories\BaseRepo;

class CustomerRepo extends BaseRepo
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
     * Get customer statistics summary
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getCustomerStatistics(array $dateRange): array
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        $builder = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder($filters);

        $totalCustomers  = $builder->count();
        $activeCustomers = clone $builder;
        $activeCustomers = $activeCustomers->where('active', 1)->count();

        $growth = 0;
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            // Get previous period for comparison
            $daysDiff      = $dateRange['start_date']->diffInDays($dateRange['end_date']) + 1;
            $previousStart = $dateRange['start_date']->copy()->subDays($daysDiff);
            $previousEnd   = $dateRange['start_date']->copy()->subDay()->endOfDay();

            $previousFilters = [
                'created_at_start' => $previousStart,
                'created_at_end'   => $previousEnd,
            ];

            $previousBuilder   = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder($previousFilters);
            $previousCustomers = $previousBuilder->count();

            $growth = $previousCustomers > 0 ? (($totalCustomers - $previousCustomers) / $previousCustomers) * 100 : 0;
        }

        return [
            'total_customers'  => $totalCustomers,
            'active_customers' => $activeCustomers,
            'growth'           => $growth,
        ];
    }

    /**
     * Get customer daily trends
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getCustomerDailyTrends(array $dateRange): array
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
            $trendStart                  = $dateRange['start'];
            $trendEnd                    = $dateRange['end'];
        } else {
            $trendStart                  = Carbon::now()->subDays(89)->format('Y-m-d');
            $trendEnd                    = Carbon::now()->format('Y-m-d');
            $filters['created_at_start'] = Carbon::parse($trendStart)->startOfDay();
            $filters['created_at_end']   = Carbon::parse($trendEnd)->endOfDay();
        }

        $customerTotals = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder($filters)
            ->select(DB::raw('DATE(created_at) as date, count(*) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates  = $totals = [];
        $period = CarbonPeriod::create($trendStart, $trendEnd)->toArray();
        foreach ($period as $date) {
            $dateFormat    = $date->format('Y-m-d');
            $customerTotal = $customerTotals[$dateFormat] ?? null;

            $dates[]  = $dateFormat;
            $totals[] = $customerTotal ? $customerTotal->total : 0;
        }

        return [
            'labels' => $dates,
            'totals' => $totals,
        ];
    }

    /**
     * Get customer source data
     *
     * @param  array  $dateRange
     * @return array
     */
    public function getCustomerSourceData(array $dateRange): array
    {
        $filters = [];
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $filters['created_at_start'] = $dateRange['start_date'];
            $filters['created_at_end']   = $dateRange['end_date'];
        }

        $sourceData = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder($filters)
            ->select(DB::raw('`from`, count(*) as total'))
            ->groupBy('from')
            ->get()
            ->keyBy('from');

        $labels      = $data = [];
        $fromOptions = \InnoShop\Common\Repositories\CustomerRepo::getFromList();

        foreach ($fromOptions as $option) {
            $key   = $option['key'];
            $label = $option['value'];

            $total    = isset($sourceData[$key]) ? $sourceData[$key]->total : 0;
            $labels[] = $label;
            $data[]   = $total;
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }

    /**
     * Get top customers by order amount
     *
     * @param  array  $dateRange
     * @param  int  $limit
     * @return array
     */
    public function getTopCustomers(array $dateRange, int $limit = 10): array
    {
        $query = Customer::withCount('orders')
            ->withSum('orders', 'total');

        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $query->whereBetween('customers.created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        return $query->orderByDesc('orders_sum_total')
            ->limit($limit)
            ->get()
            ->map(fn ($customer) => [
                'id'           => $customer->id,
                'email'        => $customer->email,
                'order_count'  => $customer->orders_count ?? 0,
                'total_amount' => $customer->orders_sum_total ?? 0,
            ])
            ->toArray();
    }

    /**
     * @return array
     */
    public function getCustomerCountLatestWeek(): array
    {
        $dateRange = $this->getDateRange('last_7_days');
        $trends    = $this->getCustomerDailyTrends($dateRange);

        return [
            'period' => $trends['labels'],
            'totals' => $trends['totals'],
        ];
    }

    /**
     * @deprecated Use getCustomerSourceData with date range instead
     * @return array
     */
    public function getCustomerSourceDataLegacy(): array
    {
        $sourceData = \InnoShop\Common\Repositories\CustomerRepo::getInstance()->builder()
            ->select(DB::raw('`from`, count(*) as total'))
            ->groupBy('from')
            ->get()
            ->keyBy('from');

        $labels      = $data = [];
        $fromOptions = \InnoShop\Common\Repositories\CustomerRepo::getFromList();

        foreach ($fromOptions as $option) {
            $key   = $option['key'];
            $label = $option['value'];

            $total    = isset($sourceData[$key]) ? $sourceData[$key]->total : 0;
            $labels[] = $label;
            $data[]   = $total;
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }
}
