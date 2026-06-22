<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Repositories\VisitRepo;
use InnoShop\Common\Services\VisitStatisticsService;
use InnoShop\Panel\Repositories\Analytics\CustomerRepo;
use InnoShop\Panel\Repositories\Analytics\OrderRepo;
use InnoShop\Panel\Repositories\Analytics\ProductRepo;

class AnalyticsController extends BaseController
{
    /**
     * Overview analytics page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $dateFilter      = $request->input('date_filter', '');
        $customStartDate = $request->input('start_date');
        $customEndDate   = $request->input('end_date');

        $orderRepo   = new OrderRepo;
        $productRepo = new ProductRepo;
        $dateRange   = $orderRepo->getDateRange($dateFilter, $customStartDate, $customEndDate);

        // Get overall statistics
        $orderStats    = $orderRepo->getOrderStatistics($dateRange);
        $productStats  = $productRepo->getProductStatistics($dateRange);
        $customerStats = (new CustomerRepo)->getCustomerStatistics($dateRange);

        // Get trends
        $orderTrends    = $orderRepo->getOrderDailyTrends($dateRange);
        $productTrends  = $productRepo->getProductDailyTrends($dateRange);
        $customerTrends = (new CustomerRepo)->getCustomerDailyTrends($dateRange);

        // Get additional metrics
        $pendingOrders = $orderRepo->getPendingOrdersCount($dateRange);
        $unpaidOrders  = $orderRepo->getUnpaidOrdersCount($dateRange);
        $topProducts   = $productRepo->getTopSellingProducts($dateRange, 10);
        $statusDist    = $orderRepo->getOrderStatusDistribution($dateRange);

        // Get visit statistics
        $visitFilters = [
            'start_date' => $dateRange['start_date'],
            'end_date'   => $dateRange['end_date'],
        ];
        $visitRepo       = new VisitRepo;
        $visitStatistics = $visitRepo->getStatistics($visitFilters);

        $data = [
            'date_filter'         => $dateFilter,
            'start_date'          => $dateRange['start'],
            'end_date'            => $dateRange['end'],
            'order_statistics'    => $orderStats,
            'product_statistics'  => $productStats,
            'customer_statistics' => $customerStats,
            'order_trends'        => $orderTrends,
            'product_trends'      => $productTrends,
            'customer_trends'     => $customerTrends,
            'pending_orders'      => $pendingOrders,
            'unpaid_orders'       => $unpaidOrders,
            'top_products'        => $topProducts,
            'status_distribution' => $statusDist,
            'visit_statistics'    => [
                'page_views'      => $visitStatistics['page_views'] ?? 0,
                'unique_visitors' => $visitStatistics['unique_visitors'] ?? 0,
            ],
        ];

        return inno_view('panel::analytics.index', $data);
    }

    /**
     * Order analytics page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function order(Request $request): mixed
    {
        $dateFilter      = $request->input('date_filter', '');
        $customStartDate = $request->input('start_date');
        $customEndDate   = $request->input('end_date');

        $orderRepo = new OrderRepo;
        $dateRange = $orderRepo->getDateRange($dateFilter, $customStartDate, $customEndDate);

        // Get order statistics
        $orderStats = $orderRepo->getOrderStatistics($dateRange);

        // Get daily trends
        $dailyTrends = $orderRepo->getOrderDailyTrends($dateRange);

        // Get status distribution
        $statusDistribution = $orderRepo->getOrderStatusDistribution($dateRange);

        // Get top selling products
        $topProducts = (new ProductRepo)->getTopSellingProducts($dateRange, 10);

        $data = [
            'date_filter'         => $dateFilter,
            'start_date'          => $dateRange['start'],
            'end_date'            => $dateRange['end'],
            'order_statistics'    => $orderStats,
            'daily_trends'        => $dailyTrends,
            'status_distribution' => $statusDistribution,
            'top_sale_products'   => $topProducts,
        ];

        return inno_view('panel::analytics.order', $data);
    }

    /**
     * Product analytics page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function product(Request $request): mixed
    {
        $dateFilter      = $request->input('date_filter', '');
        $customStartDate = $request->input('start_date');
        $customEndDate   = $request->input('end_date');

        $productRepo = new ProductRepo;
        $dateRange   = $productRepo->getDateRange($dateFilter, $customStartDate, $customEndDate);

        // Get product statistics
        $productStats = $productRepo->getProductStatistics($dateRange);

        // Get daily trends
        $dailyTrends = $productRepo->getProductDailyTrends($dateRange);

        // Get top selling products
        $topProducts = $productRepo->getTopSellingProducts($dateRange, 10);

        // Get category distribution
        $categoryDistribution = $productRepo->getProductCategoryDistribution($dateRange);

        $data = [
            'date_filter'           => $dateFilter,
            'start_date'            => $dateRange['start'],
            'end_date'              => $dateRange['end'],
            'product_statistics'    => $productStats,
            'daily_trends'          => $dailyTrends,
            'top_products'          => $topProducts,
            'category_distribution' => $categoryDistribution,
        ];

        return inno_view('panel::analytics.product', $data);
    }

    /**
     * Customer analytics page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function customer(Request $request): mixed
    {
        $dateFilter      = $request->input('date_filter', '');
        $customStartDate = $request->input('start_date');
        $customEndDate   = $request->input('end_date');

        $customerRepo = new CustomerRepo;
        $dateRange    = $customerRepo->getDateRange($dateFilter, $customStartDate, $customEndDate);

        // Get customer statistics
        $customerStats = $customerRepo->getCustomerStatistics($dateRange);

        // Get daily trends
        $dailyTrends = $customerRepo->getCustomerDailyTrends($dateRange);

        // Get source distribution
        $sourceDistribution = $customerRepo->getCustomerSourceData($dateRange);

        $data = [
            'date_filter'         => $dateFilter,
            'start_date'          => $dateRange['start'],
            'end_date'            => $dateRange['end'],
            'customer_statistics' => $customerStats,
            'daily_trends'        => $dailyTrends,
            'source_distribution' => $sourceDistribution,
        ];

        return inno_view('panel::analytics.customer', $data);
    }

    /**
     * Visit statistics page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function visit(Request $request): mixed
    {
        $statService = new VisitStatisticsService;
        $repo        = new VisitRepo;

        [$startDate, $endDate, $dateFilter] = $this->parseDateRangeFromRequest($request);

        // Build filters array
        $filters = [];
        if ($startDate && $endDate) {
            $filters['start_date'] = Carbon::parse($startDate)->startOfDay();
            $filters['end_date']   = Carbon::parse($endDate)->endOfDay();
        }

        // Cache the heavy aggregation queries for 60s. Today's data is still
        // fresh because ensureDailyAggregated re-runs daily and visit_*_daily
        // tables are repopulated when missing.
        $cacheKey = 'analytics_visit:'.($startDate ?: 'null').':'.($endDate ?: 'null');
        $data     = Cache::remember($cacheKey, 60, function () use ($repo, $filters, $startDate, $endDate, $dateFilter) {
            // Trigger auto-aggregation first (populates visit_daily & conversion_daily)
            $dailyStatistics = collect();
            if ($startDate && $endDate) {
                $dailyStatistics = collect($repo->getDailyStatistics($filters));
            }

            // Now all summary queries hit pre-aggregated data
            $statistics       = $repo->getStatistics($filters);
            $deviceStats      = $repo->getVisitsByDeviceType($filters);
            $conversionFunnel = $repo->getConversionFunnel($filters);
            $avgDuration      = $repo->getAverageVisitDuration($filters);

            // These now also read pre-aggregated tables
            $hourlyStats  = $repo->getHourlyStatistics($filters);
            $countryStats = $repo->getVisitsByCountry($filters);

            [$worldMapData, $worldMapTotal] = VisitRepo::buildWorldMapData($countryStats);

            return [
                'statistics'         => $statistics,
                'visits_by_country'  => $countryStats,
                'world_map_data'     => $worldMapData,
                'world_map_total'    => $worldMapTotal,
                'visits_by_device'   => $deviceStats,
                'daily_statistics'   => $dailyStatistics,
                'hourly_statistics'  => $hourlyStats,
                'conversion_funnel'  => $conversionFunnel,
                'avg_visit_duration' => $avgDuration,
                'start_date'         => $startDate,
                'end_date'           => $endDate,
                'date_filter'        => $dateFilter,
            ];
        });

        return inno_view('panel::analytics.visit', $data);
    }

    /**
     * Bot / crawler analytics page. Mirrors visit() but every query hits
     * visits(is_bot=true). Daily series, summary cards, brand breakdown.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function bot(Request $request): mixed
    {
        $repo = new VisitRepo;

        [$startDate, $endDate, $dateFilter] = $this->parseDateRangeFromRequest($request);

        $filters = [];
        if ($startDate && $endDate) {
            $filters['start_date'] = Carbon::parse($startDate)->startOfDay();
            $filters['end_date']   = Carbon::parse($endDate)->endOfDay();
        }

        $cacheKey = 'analytics_bot:'.($startDate ?: 'null').':'.($endDate ?: 'null');
        $data     = Cache::remember($cacheKey, 60, function () use ($repo, $filters, $startDate, $endDate, $dateFilter) {
            $daily = $repo->getBotDailyStatistics($filters);

            return [
                'statistics'           => $repo->getBotStatistics($filters),
                'daily_statistics'     => $daily,
                'chart_labels'         => array_column($daily, 'date'),
                'chart_sessions'       => array_column($daily, 'sessions'),
                'chart_unique_ips'     => array_column($daily, 'unique_ips'),
                'chart_page_views'     => array_column($daily, 'page_views'),
                'bot_by_brand'         => $repo->getBotByType($filters, 20),
                'bot_category_summary' => $repo->getBotCategorySummary($filters),
                'start_date'           => $startDate,
                'end_date'             => $endDate,
                'date_filter'          => $dateFilter,
            ];
        });

        return inno_view('panel::analytics.bot', $data);
    }

    /**
     * Resolve the analytics date range from request query. Returns the same
     * shape used by visit() and bot(): start/end as Y-m-d strings plus the
     * raw date_filter code for the date-range component.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    protected function parseDateRangeFromRequest(Request $request): array
    {
        $dateFilter      = $request->input('date_filter');
        $customStartDate = $request->input('start_date');
        $customEndDate   = $request->input('end_date');

        $now = Carbon::now();
        switch ($dateFilter) {
            case '':
                // "全部" — 从 visits 表最早的 first_visited_at 到今天
                $earliest  = Visit::min('first_visited_at');
                $startDate = $earliest
                    ? Carbon::parse($earliest)->startOfDay()->format('Y-m-d')
                    : Carbon::now()->subDays(29)->format('Y-m-d');
                $endDate = Carbon::now()->format('Y-m-d');
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

        return [$startDate, $endDate, $dateFilter];
    }

    /**
     * Re-aggregate visit statistics for a date range.
     *
     * @param  Request  $request
     * @return array
     */
    public function reaggregate(Request $request)
    {
        try {
            $startInput = $request->input('start_date');
            $endInput   = $request->input('end_date');

            $startDate = $startInput ? Carbon::parse($startInput) : Carbon::now()->subDays(29);
            $endDate   = $endInput ? Carbon::parse($endInput) : Carbon::now();

            $service = new VisitStatisticsService;
            $service->aggregateRange($startDate, $endDate);

            return json_success(trans('panel/analytics.reaggregate_success'));
        } catch (\Throwable $e) {
            return json_fail($e->getMessage());
        }
    }
}
