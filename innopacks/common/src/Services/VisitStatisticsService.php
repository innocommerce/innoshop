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
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Visit\ConversionDaily;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Models\Visit\VisitCountryDaily;
use InnoShop\Common\Models\Visit\VisitDaily;
use InnoShop\Common\Models\Visit\VisitDeviceDaily;
use InnoShop\Common\Models\Visit\VisitEvent;
use InnoShop\Common\Models\Visit\VisitHourlyDaily;

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

        // Guard: a past day with zero detail rows but existing aggregate rows
        // must never be re-aggregated — the detail was pruned or migrated
        // (visits:prune / visits:migrate-bots) and recomputing would zero out
        // good history. Days with no detail AND no aggregates fall through:
        // they aggregate to a zero row once, which also stops
        // ensureDailyAggregated from re-dispatching them forever.
        if ($date->lt(Carbon::today())
            && VisitDaily::query()->where('date', $date->toDateString())->exists()
            && ! $this->dayHasDetailRows($date)) {
            Log::warning('VisitStatisticsService: skipped re-aggregating past day whose detail rows are gone', [
                'date' => $date->toDateString(),
            ]);

            return;
        }

        $this->aggregateVisitDaily($date);
        $this->aggregateConversionDaily($date);
        $this->aggregateCountryDaily($date);
        $this->aggregateHourlyDaily($date);
        $this->aggregateDeviceDaily($date);
    }

    /**
     * Whether visits or visit_events still hold detail rows for the given
     * day. Used by the re-aggregation guard in aggregateDaily().
     */
    protected function dayHasDetailRows(Carbon $date): bool
    {
        $dateStart = $date->copy()->startOfDay();
        $dateEnd   = $date->copy()->endOfDay();

        $hasVisits = DB::table((new Visit)->getTable())
            ->whereBetween('first_visited_at', [$dateStart, $dateEnd])
            ->exists();

        if ($hasVisits) {
            return true;
        }

        return DB::table((new VisitEvent)->getTable())
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->exists();
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

        // All page-view-class events count as PV:
        // page_view / home_view / category_view / product_view / cart_view
        $pageViewTypes = [
            VisitEvent::TYPE_PAGE_VIEW,
            VisitEvent::TYPE_HOME_VIEW,
            VisitEvent::TYPE_CATEGORY_VIEW,
            VisitEvent::TYPE_PRODUCT_VIEW,
            VisitEvent::TYPE_CART_VIEW,
        ];

        // Bot sessions to exclude (from visits.is_bot). Expressed as a SQL
        // subquery instead of an in-memory id list: the bot set reaches tens
        // of thousands of sessions, and plucking them all into PHP exhausted
        // the 128M CLI memory limit on production.
        $excludeBotSessions = function ($query) use ($visitsTableName) {
            $query->whereNotIn('session_id', function ($sub) use ($visitsTableName) {
                $sub->select('session_id')
                    ->from($visitsTableName)
                    ->where('is_bot', true);
            });
        };

        // Get page views (PV/UV/IP) — exclude bot sessions
        $pvQuery = DB::table($eventsTableName)
            ->whereIn('event_type', $pageViewTypes)
            ->whereBetween('created_at', [$dateStart, $dateEnd]);
        $excludeBotSessions($pvQuery);
        $pvData = $pvQuery->select(
            DB::raw('COUNT(*) as pv'),
            DB::raw('COUNT(DISTINCT session_id) as uv'),
            DB::raw('COUNT(DISTINCT ip_address) as ip')
        )->first();

        // Get device breakdown — exclude bot sessions (they have device_type='bot')
        $deviceQuery = DB::table("{$eventsTableName} as ve")
            ->join("{$visitsTableName} as v", 've.session_id', '=', 'v.session_id')
            ->whereIn('ve.event_type', $pageViewTypes)
            ->whereBetween('ve.created_at', [$dateStart, $dateEnd])
            ->where('v.is_bot', false);
        $deviceData = $deviceQuery->select(
            'v.device_type',
            DB::raw('COUNT(*) as pv')
        )
            ->groupBy('v.device_type')
            ->pluck('pv', 'device_type');

        // Calculate new visitors (sessions that started on this date and had no prior visits)
        $newVisitors = DB::table($visitsTableName)
            ->where('first_visited_at', '>=', $dateStart)
            ->where('first_visited_at', '<=', $dateEnd)
            ->where('is_bot', false)
            ->whereNotExists(function ($query) use ($dateStart, $visitsTableName) {
                $query->select(DB::raw(1))
                    ->from("{$visitsTableName} as v2")
                    ->whereColumn("{$visitsTableName}.session_id", '=', 'v2.session_id')
                    ->where('v2.first_visited_at', '<', $dateStart);
            })
            ->count();

        // Calculate bounces (sessions with only one page view) — exclude bot sessions
        $bounceQuery = DB::table($eventsTableName)
            ->select('session_id', DB::raw('COUNT(*) as event_count'))
            ->whereIn('event_type', $pageViewTypes)
            ->whereBetween('created_at', [$dateStart, $dateEnd]);
        $excludeBotSessions($bounceQuery);

        $bounces = $bounceQuery->groupBy('session_id')
            ->having('event_count', '=', 1)
            ->count();

        // Calculate average session duration — exclude bot sessions (crawlers
        // fire quick successive fetches that would drag the average down)
        $durationQuery = DB::table($eventsTableName)
            ->select('session_id', DB::raw('MIN(created_at) as first_time'), DB::raw('MAX(created_at) as last_time'))
            ->whereBetween('created_at', [$dateStart, $dateEnd]);
        $excludeBotSessions($durationQuery);
        $sessionDurations = $durationQuery->groupBy('session_id')->get();

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
        VisitDaily::query()->updateOrCreate(
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
        $visitsTableName = (new Visit)->getTable();

        // Exclude bot sessions so crawler page/product views don't dilute
        // the funnel's conversion rates. SQL subquery instead of an in-memory
        // id list — see aggregateVisitDaily() for the memory rationale.
        $eventQuery = DB::table($eventsTableName)
            ->whereBetween('created_at', [$dateStart, $dateEnd]);
        $eventQuery->whereNotIn('session_id', function ($sub) use ($visitsTableName) {
            $sub->select('session_id')
                ->from($visitsTableName)
                ->where('is_bot', true);
        });

        // Count events by type (unique sessions)
        $eventCounts = $eventQuery
            ->select('event_type', DB::raw('COUNT(DISTINCT session_id) as count'))
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
        ConversionDaily::query()->updateOrCreate(
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
     * Aggregate per-country daily statistics (visits + pre-computed ip_count).
     * Replaces runtime COUNT(DISTINCT ip_address) GROUP BY country on raw visits table.
     */
    public function aggregateCountryDaily(Carbon $date): void
    {
        $dateStart   = $date->copy()->startOfDay();
        $dateEnd     = $date->copy()->endOfDay();
        $visitsTable = (new Visit)->getTable();
        $table       = (new VisitCountryDaily)->getTable();

        $rows = DB::table($visitsTable)
            ->selectRaw('country_code, MAX(country_name) as country_name')
            ->selectRaw('COUNT(*) as visits, COUNT(DISTINCT ip_address) as ip_count')
            ->whereBetween('first_visited_at', [$dateStart, $dateEnd])
            ->where('is_bot', false)
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            // Group by code only: country_name is locale-dependent (the same
            // code can appear as both "Argentina" and its Chinese name), which would
            // violate the (date, country_code) primary key.
            ->groupBy('country_code')
            ->get();

        DB::table($table)->where('date', $date->toDateString())->delete();

        if ($rows->isEmpty()) {
            return;
        }

        $insertRows = [];
        $now        = now();
        foreach ($rows as $row) {
            $insertRows[] = [
                'date'         => $date->toDateString(),
                'country_code' => $row->country_code,
                'country_name' => $row->country_name ?? '',
                'visits'       => $row->visits,
                'ip_count'     => $row->ip_count,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }
        foreach (array_chunk($insertRows, 500) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * Aggregate per-hour daily statistics.
     * Replaces runtime HOUR(first_visited_at) + COUNT(DISTINCT ip_address) GROUP BY hour.
     */
    public function aggregateHourlyDaily(Carbon $date): void
    {
        $dateStart   = $date->copy()->startOfDay();
        $dateEnd     = $date->copy()->endOfDay();
        $visitsTable = (new Visit)->getTable();
        $table       = (new VisitHourlyDaily)->getTable();

        $rows = DB::table($visitsTable)
            ->selectRaw('HOUR(first_visited_at) as hour, COUNT(*) as visits, COUNT(DISTINCT ip_address) as ip_count')
            ->whereBetween('first_visited_at', [$dateStart, $dateEnd])
            ->where('is_bot', false)
            ->groupBy('hour')
            ->get();

        DB::table($table)->where('date', $date->toDateString())->delete();

        if ($rows->isEmpty()) {
            return;
        }

        $insertRows = [];
        $now        = now();
        foreach ($rows as $row) {
            $insertRows[] = [
                'date'       => $date->toDateString(),
                'hour'       => $row->hour,
                'visits'     => $row->visits,
                'ip_count'   => $row->ip_count,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table($table)->insert($insertRows);
    }

    /**
     * Aggregate per-device daily statistics (visits + ip_count + page_views).
     * Replaces runtime GROUP BY device_type on raw visits table.
     */
    public function aggregateDeviceDaily(Carbon $date): void
    {
        $dateStart   = $date->copy()->startOfDay();
        $dateEnd     = $date->copy()->endOfDay();
        $prefix      = DB::getTablePrefix();
        $eventsTable = (new VisitEvent)->getTable();
        $visitsTable = (new Visit)->getTable();
        $table       = (new VisitDeviceDaily)->getTable();

        $pageViewTypes = [
            VisitEvent::TYPE_PAGE_VIEW,
            VisitEvent::TYPE_HOME_VIEW,
            VisitEvent::TYPE_CATEGORY_VIEW,
            VisitEvent::TYPE_PRODUCT_VIEW,
            VisitEvent::TYPE_CART_VIEW,
        ];

        $pvRows = DB::table("{$eventsTable} as ve")
            ->join("{$visitsTable} as v", 've.session_id', '=', 'v.session_id')
            ->whereIn('ve.event_type', $pageViewTypes)
            ->whereBetween('ve.created_at', [$dateStart, $dateEnd])
            ->where('v.is_bot', false)
            ->select('v.device_type', DB::raw('COUNT(*) as pv'), DB::raw('COUNT(DISTINCT '.$prefix.'ve.session_id) as event_sessions'))
            ->groupBy('v.device_type')
            ->get()
            ->keyBy('device_type');

        $visitRows = DB::table($visitsTable)
            ->select('device_type')
            ->selectRaw('COUNT(*) as visits, COUNT(DISTINCT ip_address) as ip_count')
            ->whereBetween('first_visited_at', [$dateStart, $dateEnd])
            ->where('is_bot', false)
            ->groupBy('device_type')
            ->get();

        // Bot row keyed off is_bot (not device_type) so it matches the panel
        // bot analytics semantics even when is_bot tagging lags behind UA parsing.
        $botVisit = DB::table($visitsTable)
            ->selectRaw('COUNT(*) as visits, COUNT(DISTINCT ip_address) as ip_count')
            ->whereBetween('first_visited_at', [$dateStart, $dateEnd])
            ->where('is_bot', true)
            ->first();

        $botPv = DB::table("{$eventsTable} as ve")
            ->join("{$visitsTable} as v", 've.session_id', '=', 'v.session_id')
            ->whereIn('ve.event_type', $pageViewTypes)
            ->whereBetween('ve.created_at', [$dateStart, $dateEnd])
            ->where('v.is_bot', true)
            ->selectRaw('COUNT(*) as pv, COUNT(DISTINCT '.$prefix.'ve.session_id) as event_sessions')
            ->first();

        DB::table($table)->where('date', $date->toDateString())->delete();

        $insertRows = [];
        $now        = now();
        foreach ($visitRows as $row) {
            $deviceType = $row->device_type ?: 'unknown';
            // 'bot' rows come exclusively from the is_bot query below.
            if ($deviceType === 'bot') {
                continue;
            }
            $insertRows[] = [
                'date'           => $date->toDateString(),
                'device_type'    => $deviceType,
                'visits'         => $row->visits,
                'ip_count'       => $row->ip_count,
                'page_views'     => (int) ($pvRows[$deviceType]->pv ?? 0),
                'event_sessions' => (int) ($pvRows[$deviceType]->event_sessions ?? 0),
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if ($botVisit && $botVisit->visits > 0) {
            $insertRows[] = [
                'date'           => $date->toDateString(),
                'device_type'    => 'bot',
                'visits'         => $botVisit->visits,
                'ip_count'       => $botVisit->ip_count,
                'page_views'     => (int) ($botPv->pv ?? 0),
                'event_sessions' => (int) ($botPv->event_sessions ?? 0),
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (empty($insertRows)) {
            return;
        }

        DB::table($table)->insert($insertRows);
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
        $stats      = VisitDaily::query()->where('date', $date->toDateString())->first();
        $conversion = ConversionDaily::query()->where('date', $date->toDateString())->first();

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
        $visitSum = VisitDaily::query()->whereBetween('date', [$start->toDateString(), $end->toDateString()])
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

        $conversionSum = ConversionDaily::query()->whereBetween('date', [$start->toDateString(), $end->toDateString()])
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
