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
use Illuminate\Support\Facades\Lang;
use InnoShop\Common\Models\Visit\ConversionDaily;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Models\Visit\VisitCountryDaily;
use InnoShop\Common\Models\Visit\VisitDaily;
use InnoShop\Common\Models\Visit\VisitDeviceDaily;
use InnoShop\Common\Models\Visit\VisitEvent;
use InnoShop\Common\Models\Visit\VisitHourlyDaily;
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
            'total_visits'    => (int) ($aggregated->total_uv ?? 0),
            'unique_visitors' => (int) ($aggregated->total_ip ?? 0),
            'unique_sessions' => (int) ($aggregated->total_uv ?? 0),
            'page_views'      => (int) ($aggregated->total_pv ?? 0),
        ];
    }

    /**
     * Get visits by country. Reads pre-aggregated visit_country_daily.
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByCountry(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();
        $today     = Carbon::today()->toDateString();

        // Ensure today's row exists (auto-aggregate on demand)
        if ($startDate->lessThanOrEqualTo(Carbon::today()) && $endDate->greaterThanOrEqualTo(Carbon::today())) {
            if (! VisitCountryDaily::where('date', $today)->exists() && VisitEvent::exists()) {
                try {
                    (new VisitStatisticsService)->aggregateCountryDaily(Carbon::today());
                } catch (\Throwable $e) {
                    logger()->warning('aggregateCountryDaily(today) failed: '.$e->getMessage());
                }
            }
        }

        return VisitCountryDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('country_code, MAX(country_name) as country_name, SUM(visits) as visits, SUM(ip_count) as unique_visitors')
            ->groupBy('country_code')
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
     * Get visits by device type. Reads pre-aggregated visit_device_daily.
     *
     * @param  array  $filters
     * @return array
     */
    public function getVisitsByDeviceType(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();
        $today     = Carbon::today()->toDateString();

        if ($startDate->lessThanOrEqualTo(Carbon::today()) && $endDate->greaterThanOrEqualTo(Carbon::today())) {
            if (! VisitDeviceDaily::where('date', $today)->exists() && VisitEvent::exists()) {
                try {
                    (new VisitStatisticsService)->aggregateDeviceDaily(Carbon::today());
                } catch (\Throwable $e) {
                    logger()->warning('aggregateDeviceDaily(today) failed: '.$e->getMessage());
                }
            }
        }

        $rows = VisitDeviceDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('device_type, SUM(visits) as visits, SUM(ip_count) as unique_visitors, SUM(page_views) as page_views')
            ->groupBy('device_type')
            ->get()
            ->keyBy('device_type');

        $result = [];
        foreach (['desktop', 'mobile', 'tablet'] as $device) {
            $row      = $rows->get($device);
            $result[] = [
                'device_type'     => $device,
                'visits'          => $row?->visits ?? 0,
                'unique_visitors' => $row?->unique_visitors ?? 0,
                'page_views'      => $row?->page_views ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Get bot/crawler statistics. Counts sessions, unique IPs and (optional)
     * JS-fired page views, so admins can see crawl volume separately from the
     * human-only aggregates.
     *
     * @param  array  $filters
     * @return array
     */
    public function getBotStatistics(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $visitsTable = (new Visit)->getTable();

        // Sessions + unique IPs directly from visits (is_bot=true)
        $sessionStats = DB::table($visitsTable)
            ->where('is_bot', true)
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as sessions, COUNT(DISTINCT ip_address) as unique_ips')
            ->first();

        // Page views fired by bot sessions (usually small, since most crawlers
        // don't execute the JS event tracking). Use prefixed alias in raw SQL
        // because Laravel's grammar also prefixes the alias token.
        $pageViewTypes = [
            VisitEvent::TYPE_PAGE_VIEW,
            VisitEvent::TYPE_HOME_VIEW,
            VisitEvent::TYPE_CATEGORY_VIEW,
            VisitEvent::TYPE_PRODUCT_VIEW,
            VisitEvent::TYPE_CART_VIEW,
        ];
        $prefix      = DB::getTablePrefix();
        $eventsTable = (new VisitEvent)->getTable();
        $eventStats  = DB::table("{$eventsTable} as ve")
            ->join("{$visitsTable} as v", 've.session_id', '=', 'v.session_id')
            ->whereIn('ve.event_type', $pageViewTypes)
            ->whereBetween('ve.created_at', [$startDate, $endDate])
            ->where('v.is_bot', true)
            ->selectRaw('COUNT(*) as pv, COUNT(DISTINCT '.$prefix.'ve.session_id) as event_sessions')
            ->first();

        return [
            'sessions'        => (int) ($sessionStats->sessions ?? 0),
            'unique_visitors' => (int) ($sessionStats->unique_ips ?? 0),
            'page_views'      => (int) ($eventStats->pv ?? 0),
            'event_sessions'  => (int) ($eventStats->event_sessions ?? 0),
        ];
    }

    /**
     * Map bot user-agent strings to known brand buckets and return Top N.
     * Brand classification is done in PHP (not SQL) because brand detection
     * depends on multiple substring patterns per brand.
     *
     * @param  array  $filters
     * @param  int  $limit
     * @return array
     */
    public function getBotByType(array $filters = [], int $limit = 10): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $rows = DB::table((new Visit)->getTable())
            ->where('is_bot', true)
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->select('user_agent', 'ip_address')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $brandPatterns = self::botBrandPatterns();
        $categoryMap   = self::botBrandCategoryMap();

        $counts = [];
        $ips    = [];
        foreach ($rows as $row) {
            $ua      = strtolower((string) $row->user_agent);
            $matched = null;
            foreach ($brandPatterns as $brand => $patterns) {
                foreach ($patterns as $p) {
                    if (str_contains($ua, $p)) {
                        $matched = $brand;
                        break 2;
                    }
                }
            }
            $brand          = $matched ?? 'unknown';
            $counts[$brand] = ($counts[$brand] ?? 0) + 1;
            $ips[$brand][]  = $row->ip_address;
        }

        arsort($counts);

        $totalSessions = array_sum($counts);

        $result = [];
        $i      = 0;
        foreach ($counts as $brand => $count) {
            if ($i++ >= $limit) {
                break;
            }
            $category = $categoryMap[$brand] ?? 'suspicious';
            $result[] = [
                'brand'          => $brand,
                'brand_label'    => self::brandLabel($brand),
                'category'       => $category,
                'category_label' => self::categoryLabel($category),
                'category_tip'   => self::categoryTip($category),
                'category_color' => self::categoryColor($category),
                'sessions'       => $count,
                'unique_ips'     => count(array_unique($ips[$brand])),
                'share'          => $totalSessions > 0 ? ($count / $totalSessions * 100) : 0,
            ];
        }

        return $result;
    }

    /**
     * Aggregate ALL bot sessions across every brand into the 4-bucket
     * taxonomy (seo/geo/other/suspicious). Unlike getBotByType this is not
     * limited — the panel uses it to render the summary bar above the
     * brand table so admins see the SEO/GEO/suspicious split at a glance.
     *
     * @param  array  $filters
     * @return array<string, array{category: string, sessions: int, unique_ips: int}>
     */
    public function getBotCategorySummary(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $rows = DB::table((new Visit)->getTable())
            ->where('is_bot', true)
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->select('user_agent', 'ip_address')
            ->get();

        $brandPatterns = self::botBrandPatterns();
        $categoryMap   = self::botBrandCategoryMap();

        $agg = [
            'seo'        => ['sessions' => 0, 'ips' => []],
            'geo'        => ['sessions' => 0, 'ips' => []],
            'other'      => ['sessions' => 0, 'ips' => []],
            'suspicious' => ['sessions' => 0, 'ips' => []],
        ];

        foreach ($rows as $row) {
            $ua      = strtolower((string) $row->user_agent);
            $matched = null;
            foreach ($brandPatterns as $brand => $patterns) {
                foreach ($patterns as $p) {
                    if (str_contains($ua, $p)) {
                        $matched = $brand;
                        break 2;
                    }
                }
            }
            $brand    = $matched ?? 'unknown';
            $category = $categoryMap[$brand] ?? 'suspicious';
            if (! isset($agg[$category])) {
                $category = 'suspicious';
            }
            $agg[$category]['sessions']++;
            $agg[$category]['ips'][] = $row->ip_address;
        }

        $totalSessions = array_sum(array_column($agg, 'sessions'));

        $result = [];
        foreach (['seo', 'geo', 'other', 'suspicious'] as $cat) {
            $sessions     = $agg[$cat]['sessions'];
            $result[$cat] = [
                'category'       => $cat,
                'category_label' => self::categoryLabel($cat),
                'category_tip'   => self::categoryTip($cat),
                'category_color' => self::categoryColor($cat),
                'sessions'       => $sessions,
                'unique_ips'     => count(array_unique($agg[$cat]['ips'])),
                'share'          => $totalSessions > 0 ? ($sessions / $totalSessions * 100) : 0,
            ];
        }

        return $result;
    }

    /**
     * Group bot brands by intent. Four buckets, chosen so the panel can
     * answer "is this bot SEO-relevant or GEO-relevant?" at a glance:
     *
     *   seo         — search engine crawlers (being indexed drives SEO)
     *   geo         — AI / LLM training & AI search (being cited drives GEO)
     *   other       — friendly bots: SEO tools, monitors, social, archive
     *   suspicious  — scanners, generic libs, unknowns (usually hostile)
     *
     * @return array<string, string> brand_code => category_code
     */
    public static function botBrandCategoryMap(): array
    {
        return [
            // Search engines — being indexed here drives SEO ranking
            'google'     => 'seo',
            'bing'       => 'seo',
            'baidu'      => 'seo',
            'sogou'      => 'seo',
            'shenma'     => 'seo',
            'bytespider' => 'seo',
            'petalbot'   => 'seo',
            'duckduckgo' => 'seo',
            'yandex'     => 'seo',
            '360'        => 'seo',
            'coccoc'     => 'seo',
            'slurp'      => 'seo',

            // AI / LLM training & AI search — being cited here drives GEO
            'openai'     => 'geo',
            'anthropic'  => 'geo',
            'perplexity' => 'geo',
            'ccbot'      => 'geo',
            'meta'       => 'geo',
            'you'        => 'geo',
            'amazon'     => 'geo',
            'applebot'   => 'geo',
            'ai_index'   => 'geo',

            // Friendly bots: SEO tools, monitors, social previews, archive
            'ahrefs'               => 'other',
            'semrush'              => 'other',
            'mj12'                 => 'other',
            'dotbot'               => 'other',
            'exabot'               => 'other',
            'serpstat'             => 'other',
            'seranking'            => 'other',
            'dataforseo'           => 'other',
            'brokenlinks'          => 'other',
            'velen'                => 'other',
            'webtrackr'            => 'other',
            'surdotly'             => 'other',
            'serpminer'            => 'other',
            'agentdata'            => 'other',
            'netcraft'             => 'other',
            'bitsight'             => 'other',
            'recorded_future'      => 'other',
            'internet_measurement' => 'other',
            'pandalytics'          => 'other',
            'pingdom'              => 'other',
            'uptimerobot'          => 'other',
            'statuscake'           => 'other',
            'site24x7'             => 'other',
            'newrelic'             => 'other',
            'twitter'              => 'other',
            'linkedin'             => 'other',
            'telegram'             => 'other',
            'whatsapp'             => 'other',
            'skype'                => 'other',
            'archive'              => 'other',

            // Suspicious: scanners + generic frameworks + unknown
            'scanner'     => 'suspicious',
            'wpbot'       => 'suspicious',
            'cms_checker' => 'suspicious',
            'scrapy'      => 'suspicious',
            'python'      => 'suspicious',
            'curl'        => 'suspicious',
            'wget'        => 'suspicious',
            'java'        => 'suspicious',
            'go'          => 'suspicious',
            'nodejs'      => 'suspicious',
            'web_fetch'   => 'suspicious',
            'httpclient'  => 'suspicious',
            'unknown'     => 'suspicious',
        ];
    }

    /**
     * Inline CSS for each bot category badge. Bootstrap 5 has no purple, so
     * we ship a raw background-color per category.
     *
     * @return array<string, string>
     */
    public static function categoryStyleMap(): array
    {
        return [
            'seo'        => 'background:#0d6efd;',
            'geo'        => 'background:#6f42c1;',
            'other'      => 'background:#6c757d;',
            'suspicious' => 'background:#dc3545;',
        ];
    }

    public static function categoryColor(string $category): string
    {
        return self::categoryStyleMap()[$category] ?? 'background:#6c757d;';
    }

    /**
     * Resolve a category label with translation fallback. Returns the raw
     * code if no translation key exists.
     */
    public static function categoryLabel(string $category): string
    {
        $key = 'panel/visit.bot_category_'.$category;

        return Lang::has($key) ? trans($key) : $category;
    }

    public static function categoryTip(string $category): string
    {
        $key = 'panel/visit.bot_category_'.$category.'_tip';

        return Lang::has($key) ? trans($key) : '';
    }

    /**
     * Resolve a brand label with translation fallback. Returns the raw
     * brand code if no translation key exists.
     */
    public static function brandLabel(string $brand): string
    {
        $key = 'panel/visit.bot_brand_'.$brand;

        return Lang::has($key) ? trans($key) : $brand;
    }

    /**
     * Daily bot session/IP/PV series for the bot analytics trend chart.
     * PV is counted from visit_events JOIN visits(is_bot=true); most crawlers
     * don't fire JS events, so the PV column is usually small relative to
     * session count.
     *
     * @param  array  $filters
     * @return array<int, array{date: string, sessions: int, unique_ips: int, page_views: int}>
     */
    public function getBotDailyStatistics(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $visitsTable = (new Visit)->getTable();

        // Sessions + unique IPs grouped by day, straight from visits table.
        $sessionRows = DB::table($visitsTable)
            ->where('is_bot', true)
            ->whereBetween('first_visited_at', [$startDate, $endDate])
            ->selectRaw('DATE(first_visited_at) as date, COUNT(*) as sessions, COUNT(DISTINCT ip_address) as unique_ips')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Page views from visit_events JOIN visits(is_bot=true), grouped by day.
        $pageViewTypes = [
            VisitEvent::TYPE_PAGE_VIEW,
            VisitEvent::TYPE_HOME_VIEW,
            VisitEvent::TYPE_CATEGORY_VIEW,
            VisitEvent::TYPE_PRODUCT_VIEW,
            VisitEvent::TYPE_CART_VIEW,
        ];
        $prefix      = DB::getTablePrefix();
        $eventsTable = (new VisitEvent)->getTable();
        $pvRows      = DB::table("{$eventsTable} as ve")
            ->join("{$visitsTable} as v", 've.session_id', '=', 'v.session_id')
            ->whereIn('ve.event_type', $pageViewTypes)
            ->whereBetween('ve.created_at', [$startDate, $endDate])
            ->where('v.is_bot', true)
            ->selectRaw('DATE('.$prefix.'ve.created_at) as date, COUNT(*) as pv')
            ->groupBy('date')
            ->pluck('pv', 'date');

        // Fill the full date range, including days with zero bots.
        $results = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr   = $current->toDateString();
            $row       = $sessionRows->get($dateStr);
            $results[] = [
                'date'       => $dateStr,
                'sessions'   => (int) ($row?->sessions ?? 0),
                'unique_ips' => (int) ($row?->unique_ips ?? 0),
                'page_views' => (int) ($pvRows[$dateStr] ?? 0),
            ];
            $current->addDay();
        }

        return $results;
    }

    /**
     * Map raw GeoIP country names to ECharts world map names. ECharts ships
     * its own naming convention (e.g. "United States of America" →
     * "United States", "Czechia" → "Czech Rep.") so we collapse variants
     * onto the map's expected labels.
     *
     * @return array<string, string>
     */
    public static function worldMapNameMap(): array
    {
        return [
            'Hong Kong'                        => 'China',
            'Taiwan'                           => 'China',
            'Macao'                            => 'China',
            'Macau'                            => 'China',
            'South Korea'                      => 'Korea',
            'Republic of Korea'                => 'Korea',
            'Czechia'                          => 'Czech Rep.',
            'Czech Republic'                   => 'Czech Rep.',
            'Bosnia and Herzegovina'           => 'Bosnia and Herz.',
            'North Macedonia'                  => 'Macedonia',
            'Democratic Republic of the Congo' => 'Dem. Rep. Congo',
            "Lao People's Democratic Republic" => 'Lao PDR',
            'Laos'                             => 'Lao PDR',
            'Socialist Republic of Vietnam'    => 'Vietnam',
            'Islamic Republic of Iran'         => 'Iran',
            'Russian Federation'               => 'Russia',
            'Bolivarian Republic of Venezuela' => 'Venezuela',
            'Syrian Arab Republic'             => 'Syria',
            'United Republic of Tanzania'      => 'Tanzania',
            'Republic of Moldova'              => 'Moldova',
            'United States of America'         => 'United States',
        ];
    }

    /**
     * Convert raw visits_by_country rows (from getVisitsByCountry) into the
     * shape ECharts world map expects: [{name, value}, ...] plus the total
     * visit count. Country names are normalised via worldMapNameMap();
     * values are summed when multiple raw names collapse onto the same map
     * name (e.g. Hong Kong + Macao + Taiwan → China).
     *
     * @param  array  $visitsByCountry
     * @return array{0: array<int, array{name: string, value: int}>, 1: int}
     */
    public static function buildWorldMapData(array $visitsByCountry): array
    {
        $nameMap = self::worldMapNameMap();
        $bucket  = [];
        $total   = 0;
        foreach ($visitsByCountry as $country) {
            $rawName = $country['country_name'] ?? $country['country_code'] ?? '';
            $mapName = $nameMap[$rawName] ?? $rawName;
            $value   = (int) ($country['visits'] ?? 0);
            $total += $value;
            if (isset($bucket[$mapName])) {
                $bucket[$mapName]['value'] += $value;
            } else {
                $bucket[$mapName] = ['name' => $mapName, 'value' => $value];
            }
        }

        return [array_values($bucket), $total];
    }

    /**
     * Known bot brand patterns keyed by brand code. Used by getBotByType to
     * classify raw user-agent strings. Keep this list aligned with
     * Console\Commands\TagBots::$botPatterns so newly tagged bots remain
     * classifiable in the analytics view.
     *
     * @return array<string, string[]>
     */
    public static function botBrandPatterns(): array
    {
        return [
            'google'     => ['googlebot'],
            'bing'       => ['bingbot'],
            'baidu'      => ['baiduspider'],
            'sogou'      => ['sogou'],
            'shenma'     => ['yisouspider'],
            'bytespider' => ['bytespider'],
            'petalbot'   => ['petalbot'],
            'duckduckgo' => ['duckduckbot'],
            'yandex'     => ['yandexbot', 'yadirectfetcher'],
            '360'        => ['360spider'],
            'coccoc'     => ['coccocbot'],
            // AI / LLM training & search
            'openai'     => ['gptbot', 'chatgpt-user', 'oai-searchbot'],
            'anthropic'  => ['claudebot', 'claude-user'],
            'perplexity' => ['perplexitybot'],
            'ccbot'      => ['ccbot'],
            'meta'       => ['meta-externalagent', 'meta-webindexer', 'facebot', 'facebookexternalhit'],
            'you'        => ['youbot'],
            'amazon'     => ['amazonbot'],
            'applebot'   => ['applebot'],
            'ai_index'   => ['aiwebindex', 'haibara_ai'],
            // SEO / marketing analytics
            'ahrefs'      => ['ahrefsbot'],
            'semrush'     => ['semrushbot'],
            'mj12'        => ['mj12bot'],
            'dotbot'      => ['dotbot'],
            'exabot'      => ['exabot'],
            'serpstat'    => ['serpstatbot'],
            'seranking'   => ['serankingbacklinksbot'],
            'dataforseo'  => ['dataforseobot'],
            'brokenlinks' => ['brokenlinksbot'],
            'velen'       => ['velenpublicwebcrawler'],
            'webtrackr'   => ['webtrackrcrawler'],
            'surdotly'    => ['surdotlybot'],
            'serpminer'   => ['serpminerbot'],
            'agentdata'   => ['agentdatabot'],
            // Social / messaging
            'twitter'  => ['twitterbot'],
            'linkedin' => ['linkedinbot'],
            'telegram' => ['telegrambot'],
            'whatsapp' => ['whatsapp'],
            'skype'    => ['skypeuripreview'],
            // Archival / web intel
            'archive'              => ['archive.org', 'heritrix'],
            'slurp'                => ['slurp'],
            'netcraft'             => ['netcraftsurveyagent'],
            'bitsight'             => ['bitsightbot'],
            'recorded_future'      => ['recordedfuture'],
            'internet_measurement' => ['internetmeasurement'],
            'pandalytics'          => ['pandalytics'],
            // Uptime monitors
            'pingdom'     => ['pingdom'],
            'uptimerobot' => ['uptimerobot'],
            'statuscake'  => ['statuscake'],
            'site24x7'    => ['site24x7'],
            'newrelic'    => ['newrelicpinger'],
            // WordPress / CMS scanners
            'wpbot'       => ['wpbot', 'wp-safe-scanner'],
            'cms_checker' => ['cms-checker'],
            // Generic frameworks / libraries
            'scrapy'    => ['scrapy/'],
            'python'    => ['python-requests', 'python-urllib', 'aiohttp'],
            'curl'      => ['curl'],
            'wget'      => ['wget'],
            'java'      => ['java/', 'okhttp', 'dalvik'],
            'go'        => ['go-http-client', 'req/v3'],
            'nodejs'    => ['node-fetch', 'httpx', 'axios', 'got '],
            'web_fetch' => ['webfetchtool'],
            // Hostile scanners / CVE feeds
            'scanner'    => ['masscan', 'nmap', 'nikto', 'sqlmap', 'wpscan', 'dirbuster', 'gobuster', 'zgrab', 'censys', 'shodan', 'shadow', 'l9scan', 'intelx', 'visionheight', 'palo alto networks', 'lrvl-livewire', 'root_evidence', 'ev-crawler'],
            'httpclient' => ['httpclient', 'lwp-'],
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
            ->where('is_bot', false)
            ->whereNotNull('browser')
            ->where('browser', '!=', '')
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
            ->where('is_bot', false)
            ->whereNotNull('os')
            ->where('os', '!=', '')
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
                'visits'          => $stat ? ($stat->uv ?? 0) : 0,
                'unique_visitors' => $stat ? ($stat->ip ?? 0) : 0,
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
        // Quick check: if there are no visit_events at all, skip aggregation
        if (VisitEvent::query()->doesntExist()) {
            return;
        }

        $today = Carbon::today()->toDateString();

        $existingDates = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date)
            ->flip();

        $datesToAggregate = [];
        $current          = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            // Aggregate if no record exists, or if it's today (data still growing)
            if (! isset($existingDates[$dateStr]) || $dateStr === $today) {
                $datesToAggregate[] = $current->copy();
            }
            $current->addDay();
        }

        if (empty($datesToAggregate)) {
            return;
        }

        $service = new VisitStatisticsService;
        foreach ($datesToAggregate as $date) {
            try {
                $service->aggregateDaily($date);
            } catch (\Throwable $e) {
                logger()->warning("Failed to aggregate visit stats for {$date->toDateString()}: ".$e->getMessage());
            }
        }
    }

    /**
     * Get hourly visit statistics. Reads pre-aggregated visit_hourly_daily.
     *
     * @param  array  $filters
     * @return array
     */
    public function getHourlyStatistics(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(7))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();
        $today     = Carbon::today()->toDateString();

        if ($startDate->lessThanOrEqualTo(Carbon::today()) && $endDate->greaterThanOrEqualTo(Carbon::today())) {
            if (! VisitHourlyDaily::where('date', $today)->exists() && VisitEvent::exists()) {
                try {
                    (new VisitStatisticsService)->aggregateHourlyDaily(Carbon::today());
                } catch (\Throwable $e) {
                    logger()->warning('aggregateHourlyDaily(today) failed: '.$e->getMessage());
                }
            }
        }

        return VisitHourlyDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('hour, SUM(visits) as visits, SUM(ip_count) as unique_visitors')
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
            ->with(['customer'])
            ->withCount('visitEvents')
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
