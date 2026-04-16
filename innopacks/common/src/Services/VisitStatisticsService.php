<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Visit\ConversionDaily;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Models\Visit\VisitDaily;
use InnoShop\Common\Models\Visit\VisitEvent;

class VisitStatisticsService
{
    /**
     * Aggregate daily statistics for a specific date.
     * Called by scheduled task (e.g., daily after midnight).
     *
     * @param  Carbon|string|null  $date
     * @return void
     */
    public function aggregateDaily(Carbon|string|null $date = null): void
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        } elseif ($date === null) {
            $date = Carbon::yesterday();
        }

        $this->aggregateVisitDaily($date);
        $this->aggregateConversionDaily($date);
    }

    /**
     * Aggregate visit statistics for a specific date.
     *
     * @param  Carbon  $date
     * @return void
     */
    public function aggregateVisitDaily(Carbon $date): void
    {
        $dateStart       = $date->copy()->startOfDay();
        $dateEnd         = $date->copy()->endOfDay();
        $prefix          = DB::getTablePrefix();
        $eventsTableName = (new VisitEvent)->getTable();
        $visitsTableName = (new Visit)->getTable();
        // Full table names for raw SQL (with prefix)
        $eventsTable = $prefix.$eventsTableName;
        $visitsTable = $prefix.$visitsTableName;

        // Get page views (product_view events)
        $pvData = DB::table($eventsTableName)
            ->select(
                DB::raw('COUNT(*) as pv'),
                DB::raw('COUNT(DISTINCT session_id) as uv'),
                DB::raw('COUNT(DISTINCT ip_address) as ip')
            )
            ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->first();

        // Get device breakdown
        $deviceData = DB::table("{$eventsTableName} as ve")
            ->join("{$visitsTableName} as v", 've.session_id', '=', 'v.session_id')
            ->select(
                'v.device_type',
                DB::raw('COUNT(*) as pv')
            )
            ->where('ve.event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->whereBetween('ve.created_at', [$dateStart, $dateEnd])
            ->groupBy('v.device_type')
            ->pluck('pv', 'device_type');

        // Calculate new visitors (sessions that started on this date and had no prior visits)
        $newVisitors = DB::table($visitsTableName)
            ->where('first_visited_at', '>=', $dateStart)
            ->where('first_visited_at', '<=', $dateEnd)
            ->whereNotExists(function ($query) use ($dateStart, $visitsTableName) {
                $query->select(DB::raw(1))
                    ->from("{$visitsTableName} as v2")
                    ->whereColumn("{$visitsTableName}.session_id", '=', 'v2.session_id')
                    ->where('v2.first_visited_at', '<', $dateStart);
            })
            ->count();

        // Calculate bounces (sessions with only one page view)
        $bounces = DB::table($eventsTableName)
            ->select('session_id', DB::raw('COUNT(*) as event_count'))
            ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->groupBy('session_id')
            ->having('event_count', '=', 1)
            ->get()
            ->count();

        // Calculate average session duration
        // For each session, calculate time difference between first and last event
        $sessionDurations = DB::table($eventsTableName)
            ->select('session_id', DB::raw('MIN(created_at) as first_time'), DB::raw('MAX(created_at) as last_time'))
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->groupBy('session_id')
            ->get();

        $avgDuration = 0;
        if ($sessionDurations->isNotEmpty()) {
            $totalDuration = 0;
            foreach ($sessionDurations as $session) {
                $firstTime = $session->first_time;
                $lastTime  = $session->last_time;
                if ($firstTime && $lastTime) {
                    $totalDuration += strtotime($lastTime) - strtotime($firstTime);
                }
            }
            $avgDuration = (int) round($totalDuration / $sessionDurations->count());
        }

        // Upsert daily statistics
        VisitDaily::updateOrCreate(
            ['date' => $date->toDateString()],
            [
                'pv'           => $pvData->pv ?? 0,
                'uv'           => $pvData->uv ?? 0,
                'ip'           => $pvData->ip ?? 0,
                'new_visitors' => $newVisitors,
                'bounces'      => $bounces,
                'avg_duration' => (int) $avgDuration,
                'desktop_pv'   => $deviceData['desktop'] ?? 0,
                'mobile_pv'    => $deviceData['mobile'] ?? 0,
                'tablet_pv'    => $deviceData['tablet'] ?? 0,
            ]
        );
    }

    /**
     * Aggregate conversion statistics for a specific date.
     *
     * @param  Carbon  $date
     * @return void
     */
    public function aggregateConversionDaily(Carbon $date): void
    {
        $dateStart       = $date->copy()->startOfDay();
        $dateEnd         = $date->copy()->endOfDay();
        $eventsTableName = (new VisitEvent)->getTable();

        // Count events by type (unique sessions)
        $eventCounts = DB::table($eventsTableName)
            ->select('event_type', DB::raw('COUNT(DISTINCT session_id) as count'))
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->groupBy('event_type')
            ->pluck('count', 'event_type');

        $productViews     = $eventCounts->get(VisitEvent::TYPE_PRODUCT_VIEW, 0);
        $addToCarts       = $eventCounts->get(VisitEvent::TYPE_ADD_TO_CART, 0);
        $checkoutStarts   = $eventCounts->get(VisitEvent::TYPE_CHECKOUT_START, 0);
        $orderPlaced      = $eventCounts->get(VisitEvent::TYPE_ORDER_PLACED, 0);
        $paymentCompleted = $eventCounts->get(VisitEvent::TYPE_PAYMENT_COMPLETED, 0);
        $registers        = $eventCounts->get(VisitEvent::TYPE_REGISTER, 0);

        // New event types
        $homeViews      = $eventCounts->get(VisitEvent::TYPE_HOME_VIEW, 0);
        $categoryViews  = $eventCounts->get(VisitEvent::TYPE_CATEGORY_VIEW, 0);
        $searches       = $eventCounts->get(VisitEvent::TYPE_SEARCH, 0);
        $cartViews      = $eventCounts->get(VisitEvent::TYPE_CART_VIEW, 0);
        $orderCancelled = $eventCounts->get(VisitEvent::TYPE_ORDER_CANCELLED, 0);

        // Calculate conversion rates (x100 for precision)
        $cartToCheckoutRate = $addToCarts > 0
            ? (int) round(($checkoutStarts / $addToCarts) * 10000)
            : 0;

        $checkoutToOrderRate = $checkoutStarts > 0
            ? (int) round(($orderPlaced / $checkoutStarts) * 10000)
            : 0;

        $orderToPaymentRate = $orderPlaced > 0
            ? (int) round(($paymentCompleted / $orderPlaced) * 10000)
            : 0;

        $overallConversionRate = $productViews > 0
            ? (int) round(($paymentCompleted / $productViews) * 10000)
            : 0;

        // Upsert daily conversion statistics
        ConversionDaily::updateOrCreate(
            ['date' => $date->toDateString()],
            [
                'home_views'              => $homeViews,
                'category_views'          => $categoryViews,
                'product_views'           => $productViews,
                'add_to_carts'            => $addToCarts,
                'checkout_starts'         => $checkoutStarts,
                'order_placed'            => $orderPlaced,
                'payment_completed'       => $paymentCompleted,
                'registers'               => $registers,
                'searches'                => $searches,
                'cart_views'              => $cartViews,
                'order_cancelled'         => $orderCancelled,
                'cart_to_checkout_rate'   => $cartToCheckoutRate,
                'checkout_to_order_rate'  => $checkoutToOrderRate,
                'order_to_payment_rate'   => $orderToPaymentRate,
                'overall_conversion_rate' => $overallConversionRate,
            ]
        );
    }

    /**
     * Aggregate statistics for a date range.
     * Useful for backfilling historical data.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return void
     */
    public function aggregateRange(Carbon $startDate, Carbon $endDate): void
    {
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $this->aggregateDaily($currentDate);
            $currentDate->addDay();
        }
    }

    /**
     * Get statistics for a specific period (day/week/month/year).
     *
     * @param  string  $period  day, week, month, year
     * @param  Carbon|null  $date
     * @return array
     */
    public function getPeriodStatistics(string $period = 'day', ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::now();

        return match ($period) {
            'day'   => $this->getDayStatistics($date),
            'week'  => $this->getWeekStatistics($date),
            'month' => $this->getMonthStatistics($date),
            'year'  => $this->getYearStatistics($date),
            default => $this->getDayStatistics($date),
        };
    }

    /**
     * Get statistics for a specific day.
     *
     * @param  Carbon  $date
     * @return array
     */
    public function getDayStatistics(Carbon $date): array
    {
        $stats      = VisitDaily::where('date', $date->toDateString())->first();
        $conversion = ConversionDaily::where('date', $date->toDateString())->first();

        return [
            'visit'      => $stats ? $this->formatVisitStats($stats) : $this->emptyVisitStats(),
            'conversion' => $conversion ? $this->formatConversionStats($conversion) : $this->emptyConversionStats(),
        ];
    }

    /**
     * Get statistics for a week.
     *
     * @param  Carbon  $date
     * @return array
     */
    public function getWeekStatistics(Carbon $date): array
    {
        $start = $date->copy()->startOfWeek();
        $end   = $date->copy()->endOfWeek();

        return $this->getRangeStatistics($start, $end);
    }

    /**
     * Get statistics for a month.
     *
     * @param  Carbon  $date
     * @return array
     */
    public function getMonthStatistics(Carbon $date): array
    {
        $start = $date->copy()->startOfMonth();
        $end   = $date->copy()->endOfMonth();

        return $this->getRangeStatistics($start, $end);
    }

    /**
     * Get statistics for a year.
     *
     * @param  Carbon  $date
     * @return array
     */
    public function getYearStatistics(Carbon $date): array
    {
        $start = $date->copy()->startOfYear();
        $end   = $date->copy()->endOfYear();

        return $this->getRangeStatistics($start, $end);
    }

    /**
     * Get aggregated statistics for a date range.
     *
     * @param  Carbon  $start
     * @param  Carbon  $end
     * @return array
     */
    protected function getRangeStatistics(Carbon $start, Carbon $end): array
    {
        $visitSum = VisitDaily::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('
                SUM(pv) as pv,
                SUM(uv) as uv,
                SUM(ip) as ip,
                SUM(new_visitors) as new_visitors,
                SUM(bounces) as bounces,
                AVG(avg_duration) as avg_duration,
                SUM(desktop_pv) as desktop_pv,
                SUM(mobile_pv) as mobile_pv,
                SUM(tablet_pv) as tablet_pv
            ')
            ->first();

        $conversionSum = ConversionDaily::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('
                SUM(product_views) as product_views,
                SUM(add_to_carts) as add_to_carts,
                SUM(checkout_starts) as checkout_starts,
                SUM(order_placed) as order_placed,
                SUM(payment_completed) as payment_completed,
                SUM(registers) as registers
            ')
            ->first();

        // Calculate aggregated conversion rates
        $productViews     = $conversionSum->product_views ?? 0;
        $addToCarts       = $conversionSum->add_to_carts ?? 0;
        $checkoutStarts   = $conversionSum->checkout_starts ?? 0;
        $orderPlaced      = $conversionSum->order_placed ?? 0;
        $paymentCompleted = $conversionSum->payment_completed ?? 0;

        return [
            'visit' => [
                'pv'           => (int) ($visitSum->pv ?? 0),
                'uv'           => (int) ($visitSum->uv ?? 0),
                'ip'           => (int) ($visitSum->ip ?? 0),
                'new_visitors' => (int) ($visitSum->new_visitors ?? 0),
                'bounces'      => (int) ($visitSum->bounces ?? 0),
                'avg_duration' => (int) ($visitSum->avg_duration ?? 0),
                'desktop_pv'   => (int) ($visitSum->desktop_pv ?? 0),
                'mobile_pv'    => (int) ($visitSum->mobile_pv ?? 0),
                'tablet_pv'    => (int) ($visitSum->tablet_pv ?? 0),
            ],
            'conversion' => [
                'product_views'     => (int) $productViews,
                'add_to_carts'      => (int) $addToCarts,
                'checkout_starts'   => (int) $checkoutStarts,
                'order_placed'      => (int) $orderPlaced,
                'payment_completed' => (int) $paymentCompleted,
                'registers'         => (int) ($conversionSum->registers ?? 0),
                'rates'             => [
                    'cart_to_checkout'   => $addToCarts > 0 ? round(($checkoutStarts / $addToCarts) * 100, 2) : 0,
                    'checkout_to_order'  => $checkoutStarts > 0 ? round(($orderPlaced / $checkoutStarts) * 100, 2) : 0,
                    'order_to_payment'   => $orderPlaced > 0 ? round(($paymentCompleted / $orderPlaced) * 100, 2) : 0,
                    'overall_conversion' => $productViews > 0 ? round(($paymentCompleted / $productViews) * 100, 2) : 0,
                ],
            ],
        ];
    }

    /**
     * Format visit statistics for API response.
     *
     * @param  VisitDaily  $stats
     * @return array
     */
    protected function formatVisitStats(VisitDaily $stats): array
    {
        return [
            'pv'           => $stats->pv,
            'uv'           => $stats->uv,
            'ip'           => $stats->ip,
            'new_visitors' => $stats->new_visitors,
            'bounces'      => $stats->bounces,
            'avg_duration' => $stats->avg_duration,
            'desktop_pv'   => $stats->desktop_pv,
            'mobile_pv'    => $stats->mobile_pv,
            'tablet_pv'    => $stats->tablet_pv,
        ];
    }

    /**
     * Format conversion statistics for API response.
     *
     * @param  ConversionDaily  $stats
     * @return array
     */
    protected function formatConversionStats(ConversionDaily $stats): array
    {
        return [
            'product_views'     => $stats->product_views,
            'add_to_carts'      => $stats->add_to_carts,
            'checkout_starts'   => $stats->checkout_starts,
            'order_placed'      => $stats->order_placed,
            'payment_completed' => $stats->payment_completed,
            'registers'         => $stats->registers,
            'rates'             => [
                'cart_to_checkout'   => $stats->cart_to_checkout_percent,
                'checkout_to_order'  => $stats->checkout_to_order_percent,
                'order_to_payment'   => $stats->order_to_payment_percent,
                'overall_conversion' => $stats->overall_conversion_percent,
            ],
        ];
    }

    /**
     * Return empty visit statistics.
     *
     * @return array
     */
    protected function emptyVisitStats(): array
    {
        return [
            'pv'         => 0, 'uv' => 0, 'ip' => 0, 'new_visitors' => 0,
            'bounces'    => 0, 'avg_duration' => 0,
            'desktop_pv' => 0, 'mobile_pv' => 0, 'tablet_pv' => 0,
        ];
    }

    /**
     * Return empty conversion statistics.
     *
     * @return array
     */
    protected function emptyConversionStats(): array
    {
        return [
            'product_views' => 0, 'add_to_carts' => 0, 'checkout_starts' => 0,
            'order_placed'  => 0, 'payment_completed' => 0, 'registers' => 0,
            'rates'         => [
                'cart_to_checkout'   => 0,
                'checkout_to_order'  => 0,
                'order_to_payment'   => 0,
                'overall_conversion' => 0,
            ],
        ];
    }
}
