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
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Models\Visit\VisitEvent;

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
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        $query = $this->modelQuery()
            ->whereBetween('first_visited_at', [$startDate, $endDate]);

        // Filter by country
        if (isset($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        // Filter by device type
        if (isset($filters['device_type'])) {
            $query->where('device_type', $filters['device_type']);
        }

        $baseQuery = clone $query;

        // Calculate PV using raw SQL JOIN (much faster than WHERE IN)
        $prefix           = DB::getTablePrefix();
        $countryCondition = isset($filters['country_code']) ? "AND v.country_code = '".$filters['country_code']."'" : '';
        $deviceCondition  = isset($filters['device_type']) ? "AND v.device_type = '".$filters['device_type']."'" : '';

        $pageViewsResult = DB::selectOne("
            SELECT COUNT(*) as count
            FROM {$prefix}visit_events e
            INNER JOIN {$prefix}visits v ON e.session_id = v.session_id
            WHERE v.first_visited_at BETWEEN ? AND ?
                AND e.created_at BETWEEN ? AND ?
                AND e.event_type = ?
                {$countryCondition}
                {$deviceCondition}
        ", [$startDate, $endDate, $startDate, $endDate, VisitEvent::TYPE_PRODUCT_VIEW]);

        return [
            'total_visits'    => $baseQuery->count(),
            'unique_visitors' => $baseQuery->distinct('ip_address')->count('ip_address'),
            'unique_sessions' => $baseQuery->distinct('session_id')->count('session_id'),
            'page_views'      => $pageViewsResult->count ?? 0,
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
        // Use event-based funnel analysis (more accurate)
        $eventRepo = new VisitEventRepo;

        return $eventRepo->getConversionFunnel($filters);
    }

    /**
     * Get visits by device type
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByDeviceType(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        // Single query with JOIN to get all device stats including page views
        $prefix  = DB::getTablePrefix();
        $results = DB::select("
            SELECT
                v.device_type,
                COUNT(DISTINCT v.id) as visits,
                COUNT(DISTINCT v.ip_address) as unique_visitors,
                COUNT(e.id) as page_views
            FROM {$prefix}visits v
            LEFT JOIN {$prefix}visit_events e ON v.session_id = e.session_id
                AND e.event_type = ?
                AND e.created_at BETWEEN ? AND ?
            WHERE v.first_visited_at BETWEEN ? AND ?
                AND v.device_type IS NOT NULL
            GROUP BY v.device_type
            ORDER BY visits DESC
        ", [VisitEvent::TYPE_PRODUCT_VIEW, $startDate, $endDate, $startDate, $endDate]);

        return array_map(fn ($item) => [
            'device_type'     => $item->device_type,
            'visits'          => (int) $item->visits,
            'unique_visitors' => (int) $item->unique_visitors,
            'page_views'      => (int) $item->page_views,
        ], $results);
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

        // Use Laravel Query Builder instead of raw SQL
        $results = Visit::query()
            ->selectRaw('DATE(first_visited_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->groupByRaw('DATE(first_visited_at)')
            ->orderBy('date')
            ->get();

        // Get page views separately using Model
        $pageViews = VisitEvent::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as page_views')
            ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->pluck('page_views', 'date')
            ->toArray();

        return $results->map(fn ($item) => [
            'date'            => $item->date,
            'visits'          => (int) $item->visits,
            'unique_visitors' => (int) $item->unique_visitors,
            'page_views'      => (int) ($pageViews[$item->date] ?? 0),
        ])->toArray();
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
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        // Single query to calculate average duration per session
        $prefix = DB::getTablePrefix();
        $result = DB::select("
            SELECT AVG(duration) as avg_duration
            FROM (
                SELECT
                    session_id,
                    TIMESTAMPDIFF(SECOND, MIN(created_at), MAX(created_at)) as duration
                FROM {$prefix}visit_events
                WHERE created_at BETWEEN ? AND ?
                GROUP BY session_id
                HAVING COUNT(*) > 1 AND duration > 0
            ) as session_durations
        ", [$startDate, $endDate]);

        return $result[0]->avg_duration ? round($result[0]->avg_duration, 2) : 0;
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
