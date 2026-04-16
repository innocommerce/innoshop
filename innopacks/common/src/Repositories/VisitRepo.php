<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Visit\ConversionDaily;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Models\Visit\VisitDaily;
use InnoShop\Common\Models\Visit\VisitEvent;
use InnoShop\Common\Services\VisitStatisticsService;

class VisitRepo extends BaseRepo
{
    /**
     * Model class name
     *
     * @var string
     */
    protected string $model = Visit::class;

    /**
     * Get criteria for filtering
     *
     * @return array
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'ip_address', 'type' => 'input', 'label' => trans('panel/visit.ip_address')],
            ['name' => 'country_code', 'type' => 'input', 'label' => trans('panel/visit.country_code')],
            [
                'name'    => 'device_type',
                'type'    => 'select',
                'label'   => trans('panel/visit.device_type'),
                'options' => [
                    ['value' => 'desktop', 'label' => trans('panel/visit.device_desktop')],
                    ['value' => 'mobile', 'label' => trans('panel/visit.device_mobile')],
                    ['value' => 'tablet', 'label' => trans('panel/visit.device_tablet')],
                ],
            ],
            ['name' => 'first_visited_at', 'type' => 'date_range', 'label' => trans('panel/visit.first_visited_at')],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'ip_address', 'label' => trans('panel/visit.ip_address')],
            ['value' => 'country_code', 'label' => trans('panel/visit.country_code')],
        ];

        return fire_hook_filter('common.repo.visit.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [
            [
                'name'    => 'device_type',
                'label'   => trans('panel/visit.device_type'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => 'desktop', 'label' => trans('panel/visit.device_desktop')],
                    ['value' => 'mobile', 'label' => trans('panel/visit.device_mobile')],
                    ['value' => 'tablet', 'label' => trans('panel/visit.device_tablet')],
                ],
            ],
        ];

        return fire_hook_filter('common.repo.visit.filter_button_options', $filters);
    }

    /**
     * Get visit statistics
     *
     * @param  array  $filters
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        // Use visit_daily summary table (no JOIN needed)
        $aggregated = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('SUM(pv) as total_pv, SUM(uv) as total_uv, SUM(ip) as total_ip')
            ->first();

        return [
            'total_visits'    => (int) ($aggregated->total_pv ?? 0),
            'unique_visitors' => (int) ($aggregated->total_ip ?? 0),
            'unique_sessions' => (int) ($aggregated->total_uv ?? 0),
            'page_views'      => (int) ($aggregated->total_pv ?? 0),
        ];
    }

    /**
     * Get visits by country
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByCountry(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        return $this->modelQuery()
            ->selectRaw('country_code, country_name, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->whereNotNull('country_code')
            ->groupBy('country_code', 'country_name')
            ->orderByDesc('visits')
            ->get()
            ->toArray();
    }

    /**
     * Get conversion funnel data
     *
     * @param  array  $filters
     * @return array
     */
    public function getConversionFunnel(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $aggregated = ConversionDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('
                SUM(home_views) as home_views,
                SUM(category_views) as category_views,
                SUM(product_views) as product_views,
                SUM(add_to_carts) as add_to_carts,
                SUM(cart_views) as cart_views,
                SUM(checkout_starts) as checkout_starts,
                SUM(order_placed) as order_placed,
                SUM(payment_completed) as payment_completed,
                SUM(order_cancelled) as order_cancelled,
                SUM(registers) as registers,
                SUM(searches) as searches
            ')
            ->first();

        $homeViews        = (int) ($aggregated->home_views ?? 0);
        $categoryViews    = (int) ($aggregated->category_views ?? 0);
        $productViews     = (int) ($aggregated->product_views ?? 0);
        $addToCarts       = (int) ($aggregated->add_to_carts ?? 0);
        $cartViews        = (int) ($aggregated->cart_views ?? 0);
        $checkoutStarts   = (int) ($aggregated->checkout_starts ?? 0);
        $orderPlaced      = (int) ($aggregated->order_placed ?? 0);
        $paymentCompleted = (int) ($aggregated->payment_completed ?? 0);
        $orderCancelled   = (int) ($aggregated->order_cancelled ?? 0);
        $register         = (int) ($aggregated->registers ?? 0);
        $searches         = (int) ($aggregated->searches ?? 0);

        return [
            'home_views'        => $homeViews,
            'category_views'    => $categoryViews,
            'product_views'     => $productViews,
            'add_to_carts'      => $addToCarts,
            'cart_views'        => $cartViews,
            'checkout_starts'   => $checkoutStarts,
            'order_placed'      => $orderPlaced,
            'payment_completed' => $paymentCompleted,
            'order_cancelled'   => $orderCancelled,
            'register'          => $register,
            'searches'          => $searches,
            'conversion_rates'  => [
                'home_to_category'    => $homeViews > 0 ? round(($categoryViews / $homeViews) * 100, 2) : 0,
                'category_to_product' => $categoryViews > 0 ? round(($productViews / $categoryViews) * 100, 2) : 0,
                'product_to_cart'     => $productViews > 0 ? round(($addToCarts / $productViews) * 100, 2) : 0,
                'cart_to_checkout'    => $cartViews > 0 ? round(($checkoutStarts / $cartViews) * 100, 2) : 0,
                'checkout_to_order'   => $checkoutStarts > 0 ? round(($orderPlaced / $checkoutStarts) * 100, 2) : 0,
                'order_to_payment'    => $orderPlaced > 0 ? round(($paymentCompleted / $orderPlaced) * 100, 2) : 0,
                'order_cancel_rate'   => $orderPlaced > 0 ? round(($orderCancelled / $orderPlaced) * 100, 2) : 0,
                'overall_conversion'  => $homeViews > 0 ? round(($paymentCompleted / $homeViews) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get visits by device type
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByDeviceType(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        // Use visit_daily summary table (desktop_pv, mobile_pv, tablet_pv)
        $aggregated = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('SUM(desktop_pv) as desktop_pv, SUM(mobile_pv) as mobile_pv, SUM(tablet_pv) as tablet_pv')
            ->first();

        $desktopPV = (int) ($aggregated->desktop_pv ?? 0);
        $mobilePV  = (int) ($aggregated->mobile_pv ?? 0);
        $tabletPV  = (int) ($aggregated->tablet_pv ?? 0);

        return [
            ['device_type' => 'desktop', 'visits' => $desktopPV, 'unique_visitors' => 0, 'page_views' => $desktopPV],
            ['device_type' => 'mobile', 'visits' => $mobilePV, 'unique_visitors' => 0, 'page_views' => $mobilePV],
            ['device_type' => 'tablet', 'visits' => $tabletPV, 'unique_visitors' => 0, 'page_views' => $tabletPV],
        ];
    }

    /**
     * Get visits by browser
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByBrowser(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        return $this->modelQuery()
            ->selectRaw('browser, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('visits')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get visits by operating system
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByOS(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        return $this->modelQuery()
            ->selectRaw('os, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('visits')
            ->get()
            ->toArray();
    }

    /**
     * Get daily visit statistics
     *
     * @param  array  $filters
     * @return array
     */
    public function getDailyStatistics(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        // Auto-aggregate missing dates into visit_daily before querying
        $this->ensureDailyAggregated($startDate, $endDate);

        // Use visit_daily summary table instead of raw visit_events for performance
        $dailyStats = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($item) => $item->date instanceof \DateTimeInterface ? $item->date->format('Y-m-d') : (string) $item->date);

        // Generate full date range (fills gaps for days with no data)
        $results = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr   = $current->toDateString();
            $stat      = $dailyStats->get($dateStr);
            $results[] = [
                'date'            => $dateStr,
                'visits'          => $stat ? ($stat->pv ?? 0) : 0,
                'unique_visitors' => $stat ? ($stat->uv ?? 0) : 0,
                'page_views'      => $stat ? ($stat->pv ?? 0) : 0,
            ];
            $current->addDay();
        }

        return $results;
    }

    /**
     * Ensure visit_daily is populated for the given date range.
     * Auto-aggregates any missing dates on-the-fly.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return void
     */
    protected function ensureDailyAggregated(Carbon $startDate, Carbon $endDate): void
    {
        $existingDates = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date)
            ->flip();

        $missingDates = [];
        $current      = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            if (! isset($existingDates[$dateStr])) {
                $missingDates[] = $current->copy();
            }
            $current->addDay();
        }

        if (empty($missingDates)) {
            return;
        }

        $service = new VisitStatisticsService;
        foreach ($missingDates as $date) {
            try {
                $service->aggregateDaily($date);
            } catch (\Throwable $e) {
                // Silently skip failed aggregation to avoid blocking the dashboard
                logger()->warning("Failed to aggregate visit stats for {$date->toDateString()}: ".$e->getMessage());
            }
        }
    }

    /**
     * Get hourly visit statistics
     *
     * @param  array  $filters
     * @return array
     */
    public function getHourlyStatistics(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(7);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        return $this->modelQuery()
            ->selectRaw('HOUR(first_visited_at) as hour, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    /**
     * Get top referrers
     *
     * @param  array  $filters
     * @param  int  $limit
     * @return array
     */
    public function getTopReferrers(array $filters = [], int $limit = 10): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        return $this->modelQuery()
            ->selectRaw('referrer, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get average visit duration
     *
     * @param  array  $filters
     * @return float
     */
    public function getAverageVisitDuration(array $filters = []): float
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        // Use visit_daily summary table
        $result = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('AVG(avg_duration) as avg_duration')
            ->first();

        return $result && $result->avg_duration ? round($result->avg_duration, 2) : 0;
    }

    /**
     * Get average page views per visit
     *
     * @param  array  $filters
     * @return float
     */
    public function getAveragePageViews(array $filters = []): float
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        // Single query with JOIN to calculate average page views
        $prefix = DB::getTablePrefix();
        $result = DB::selectOne("
            SELECT
                COUNT(e.id) as total_pv,
                COUNT(DISTINCT v.session_id) as total_sessions
            FROM {$prefix}visits v
            LEFT JOIN {$prefix}visit_events e ON v.session_id = e.session_id
                AND e.event_type = ?
                AND e.created_at BETWEEN ? AND ?
            WHERE v.first_visited_at BETWEEN ? AND ?
        ", [VisitEvent::TYPE_PRODUCT_VIEW, $startDate, $endDate, $startDate, $endDate]);

        if (! $result || $result->total_sessions == 0) {
            return 0;
        }

        return round($result->total_pv / $result->total_sessions, 2);
    }

    /**
     * Get list with pagination
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)
            ->with(['customer', 'visitEvents'])
            ->orderByDesc('id')
            ->paginate();
    }

    /**
     * Build query builder
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $query = $this->modelQuery();

        if (isset($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['ip_address']) && ! empty($filters['ip_address'])) {
            $query->where('ip_address', 'like', '%'.$filters['ip_address'].'%');
        }

        if (isset($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        if (isset($filters['device_type'])) {
            $query->where('device_type', $filters['device_type']);
        }

        // Handle date range filter (from date_range component)
        if (isset($filters['first_visited_at_start']) && ! empty($filters['first_visited_at_start'])) {
            $query->where('first_visited_at', '>=', $filters['first_visited_at_start'].' 00:00:00');
        }

        if (isset($filters['first_visited_at_end']) && ! empty($filters['first_visited_at_end'])) {
            $query->where('first_visited_at', '<=', $filters['first_visited_at_end'].' 23:59:59');
        }

        // Also support legacy start_date/end_date for backward compatibility
        if (isset($filters['start_date']) && ! empty($filters['start_date'])) {
            $query->where('first_visited_at', '>=', $filters['start_date'].' 00:00:00');
        }

        if (isset($filters['end_date']) && ! empty($filters['end_date'])) {
            $query->where('first_visited_at', '<=', $filters['end_date'].' 23:59:59');
        }

        return $query;
    }
}
