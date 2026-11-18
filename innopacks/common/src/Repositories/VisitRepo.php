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
use InnoShop\Common\Jobs\AggregateVisitDailyJob;
use InnoShop\Common\Models\Visit\ConversionDaily;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Models\Visit\VisitCountryDaily;
use InnoShop\Common\Models\Visit\VisitDaily;
use InnoShop\Common\Models\Visit\VisitDeviceDaily;
use InnoShop\Common\Models\Visit\VisitEvent;
use InnoShop\Common\Models\Visit\VisitHourlyDaily;
use InnoShop\Common\Services\BotClassifier;
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
            if (! VisitCountryDaily::query()->where('date', $today)->exists() && VisitEvent::query()->exists()) {
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
            if (! VisitDeviceDaily::query()->where('date', $today)->exists() && VisitEvent::query()->exists()) {
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

        $range = [$startDate->toDateString(), $endDate->toDateString()];

        // Sessions from the bot_daily aggregate; unique IPs stay a live
        // cross-day distinct over the rolling bot_daily_ips window.
        // QueryBuilder adds the connection prefix itself — never hand-prepend it.
        $sessions  = (int) DB::table('bot_daily')->whereBetween('date', $range)->sum('visits');
        $uniqueIps = (int) DB::table('bot_daily_ips')->whereBetween('date', $range)->distinct()->count('ip');

        return [
            'sessions'        => $sessions,
            'unique_visitors' => $uniqueIps,
        ];
    }

    /**
     * Classify bot sessions by UA brand and aggregate both the Top-N brand
     * table and the 4-bucket category summary (seo/geo/other/suspicious) in
     * a single scan. Brand classification is done in PHP (not SQL) because
     * brand detection depends on multiple substring patterns per brand.
     *
     * @param  array  $filters
     * @param  int  $limit
     * @return array{brands: array, categories: array}
     */
    public function getBotBreakdown(array $filters = [], int $limit = 10): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $range = [$startDate->toDateString(), $endDate->toDateString()];

        // Sessions per brand come pre-classified from bot_daily — no more
        // pulling every raw bot row and re-parsing UAs per request.
        $brandSessions = DB::table('bot_daily')
            ->whereBetween('date', $range)
            ->select('bot_brand', DB::raw('SUM(visits) as sessions'))
            ->groupBy('bot_brand')
            ->pluck('sessions', 'bot_brand');

        // Distinct IPs per brand from the rolling 30d window; category IPs
        // are the union of their member brands' sets.
        $ipsByBrand = DB::table('bot_daily_ips')
            ->whereBetween('date', $range)
            ->select('brand', 'ip')
            ->get()
            ->groupBy('brand')
            ->map(fn ($group) => $group->pluck('ip')->unique()->values());

        $brandCounts = [];
        $brandIps    = [];
        foreach ($brandSessions as $brand => $sessions) {
            $brand               = (string) $brand;
            $brandCounts[$brand] = (int) $sessions;
            $brandIps[$brand]    = $ipsByBrand->get($brand, collect());
        }

        arsort($brandCounts);

        $totalSessions = array_sum($brandCounts);

        $brandRows = [];
        $i         = 0;
        foreach ($brandCounts as $brand => $count) {
            if ($i++ >= $limit) {
                break;
            }
            $category    = BotClassifier::categoryOf($brand);
            $brandRows[] = [
                'brand'          => $brand,
                'brand_label'    => BotClassifier::brandLabel($brand),
                'category'       => $category,
                'category_label' => BotClassifier::categoryLabel($category),
                'category_tip'   => BotClassifier::categoryTip($category),
                'category_color' => BotClassifier::categoryColor($category),
                'sessions'       => $count,
                'unique_ips'     => $brandIps[$brand]->count(),
                'share'          => $totalSessions > 0 ? ($count / $totalSessions * 100) : 0,
            ];
        }

        $categoryRows = [];
        foreach (['seo', 'geo', 'other', 'suspicious'] as $cat) {
            $catBrands = array_keys(array_filter(BotClassifier::categoryMap(), fn ($c) => $c === $cat));
            $catIps    = collect($catBrands)
                ->flatMap(fn ($brand) => $brandIps[$brand] ?? collect())
                ->unique()
                ->values();
            $sessions = (int) collect($brandCounts)
                ->filter(fn ($sessions, $brand) => in_array($brand, $catBrands, true))
                ->sum();

            $categoryRows[$cat] = [
                'category'       => $cat,
                'category_label' => BotClassifier::categoryLabel($cat),
                'category_tip'   => BotClassifier::categoryTip($cat),
                'category_color' => BotClassifier::categoryColor($cat),
                'sessions'       => $sessions,
                'unique_ips'     => $catIps->count(),
                'share'          => $totalSessions > 0 ? ($sessions / $totalSessions * 100) : 0,
            ];
        }

        return ['brands' => $brandRows, 'categories' => $categoryRows];
    }

    /**
     * Inline CSS for each bot category badge. Thin wrapper over BotClassifier.
     *
     * @return array<string, string>
     */
    public static function categoryStyleMap(): array
    {
        return BotClassifier::categoryStyleMap();
    }

    public static function categoryColor(string $category): string
    {
        return BotClassifier::categoryColor($category);
    }

    /**
     * Resolve a category label with translation fallback. Thin wrapper over
     * BotClassifier.
     */
    public static function categoryLabel(string $category): string
    {
        return BotClassifier::categoryLabel($category);
    }

    public static function categoryTip(string $category): string
    {
        return BotClassifier::categoryTip($category);
    }

    /**
     * Resolve a brand label with translation fallback. Thin wrapper over
     * BotClassifier.
     */
    public static function brandLabel(string $brand): string
    {
        return BotClassifier::brandLabel($brand);
    }

    /**
     * Daily bot series for the bot analytics trend chart, read from the
     * bot_daily aggregate (bots stopped hitting the visits table). Range
     * totals are day-sums; range-level unique_ips comes from the live
     * cross-day distinct over bot_daily_ips (see getBotStatistics).
     *
     * event_sessions is retained in the shape for backward compatibility
     * but is always 0: bots no longer produce visit_events.
     *
     * @param  array  $filters
     * @return array{daily: array<int, array{date: string, sessions: int, unique_ips: int, page_views: int}>, page_views: int, event_sessions: int}
     */
    public function getBotAggregates(array $filters = []): array
    {
        $startDate = Carbon::parse($filters['start_date'] ?? Carbon::now()->subDays(30))->startOfDay();
        $endDate   = Carbon::parse($filters['end_date'] ?? Carbon::now())->endOfDay();

        $rows = DB::table('bot_daily')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('date', DB::raw('SUM(visits) as sessions'), DB::raw('SUM(page_views) as page_views'))
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($row) => (string) $row->date);

        $results = [];
        $totalPv = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $row     = $rows->get($dateStr);
            $dayPv   = (int) ($row?->page_views ?? 0);
            $totalPv += $dayPv;
            $results[] = [
                'date'       => $dateStr,
                'sessions'   => (int) ($row?->sessions ?? 0),
                'unique_ips' => 0, // per-day distinct IPs are not tracked; the page uses the range-level count
                'page_views' => $dayPv,
            ];
            $current->addDay();
        }

        return ['daily' => $results, 'page_views' => $totalPv, 'event_sessions' => 0];
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
     * Map ISO 3166-1 alpha-2 codes to ECharts world map names. The
     * country_name column is locale-dependent (GeoLocationService resolves
     * names in the app locale of whichever process enriched the visit), so
     * the world map must be keyed off the locale-independent country_code.
     * Aligned with the region names in public/vendor/echarts/world.json.
     *
     * @return array<string, string>
     */
    public static function iso2Map(): array
    {
        return [
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua and Barb.',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AO' => 'Angola',
            'AR' => 'Argentina',
            'AS' => 'American Samoa',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'AX' => 'Aland',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia and Herz.',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BN' => 'Brunei',
            'BO' => 'Bolivia',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CD' => 'Dem. Rep. Congo',
            'CF' => 'Central African Rep.',
            'CG' => 'Congo',
            'CH' => 'Switzerland',
            'CI' => "Côte d'Ivoire",
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cape Verde',
            'CW' => 'Curaçao',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Rep.',
            'DE' => 'Germany',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Rep.',
            'DZ' => 'Algeria',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'EH' => 'W. Sahara',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Is.',
            'FM' => 'Micronesia',
            'FO' => 'Faeroe Is.',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'United Kingdom',
            'GD' => 'Grenada',
            'GE' => 'Georgia',
            'GF' => 'Fr. Guiana',
            'GH' => 'Ghana',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Eq. Guinea',
            'GR' => 'Greece',
            'GS' => 'S. Geo. and S. Sandw. Is.',
            'GT' => 'Guatemala',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'China',
            'HM' => 'Heard I. and McDonald Is.',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IM' => 'Isle of Man',
            'IN' => 'India',
            'IO' => 'Br. Indian Ocean Ter.',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JE' => 'Jersey',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'St. Kitts and Nevis',
            'KP' => 'Dem. Rep. Korea',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Is.',
            'KZ' => 'Kazakhstan',
            'LA' => 'Lao PDR',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'ME' => 'Montenegro',
            'MG' => 'Madagascar',
            'MK' => 'Macedonia',
            'ML' => 'Mali',
            'MM' => 'Myanmar',
            'MN' => 'Mongolia',
            'MO' => 'China',
            'MP' => 'N. Mariana Is.',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'Fr. Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'St. Pierre and Miquelon',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestine',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Réunion',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Is.',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'Saint Helena',
            'SI' => 'Slovenia',
            'SK' => 'Slovakia',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'SS' => 'S. Sudan',
            'ST' => 'São Tomé and Principe',
            'SV' => 'El Salvador',
            'SY' => 'Syria',
            'SZ' => 'Swaziland',
            'TC' => 'Turks and Caicos Is.',
            'TD' => 'Chad',
            'TF' => 'Fr. S. Antarctic Lands',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'China',
            'TZ' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Vatican',
            'VC' => 'St. Vin. and Gren.',
            'VE' => 'Venezuela',
            'VG' => 'British Virgin Is.',
            'VI' => 'U.S. Virgin Is.',
            'VN' => 'Vietnam',
            'VU' => 'Vanuatu',
            'WS' => 'Samoa',
            'XK' => 'Kosovo',
            'YE' => 'Yemen',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ];
    }

    /**
     * Convert raw visits_by_country rows (from getVisitsByCountry) into the
     * shape ECharts world map expects: [{name, value}, ...] plus the total
     * visit count. Rows are resolved by country_code first (locale
     * independent); country names are only used as fallback via
     * worldMapNameMap(). Values are summed when multiple rows collapse onto
     * the same map name (e.g. Hong Kong + Macao + Taiwan → China).
     *
     * @param  array  $visitsByCountry
     * @return array{0: array<int, array{name: string, value: int}>, 1: int}
     */
    public static function buildWorldMapData(array $visitsByCountry): array
    {
        $nameMap = self::worldMapNameMap();
        $isoMap  = self::iso2Map();
        $bucket  = [];
        $total   = 0;
        foreach ($visitsByCountry as $country) {
            $rawName = $country['country_name'] ?? $country['country_code'] ?? '';
            $code    = strtoupper(trim((string) ($country['country_code'] ?? '')));
            $mapName = ($code !== '' && isset($isoMap[$code])) ? $isoMap[$code] : ($nameMap[$rawName] ?? $rawName);
            if ($mapName === '') {
                continue;
            }
            $value = (int) ($country['visits'] ?? 0);
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
     * Known bot brand patterns keyed by brand code. Thin wrapper — the
     * single source of truth lives in BotClassifier so live writes,
     * historical backfill and this repo can never drift apart.
     *
     * @return array<string, string[]>
     */
    public static function botBrandPatterns(): array
    {
        return BotClassifier::brandPatterns();
    }

    /**
     * Brand => category map. Thin wrapper over BotClassifier.
     *
     * @return array<string, string>
     */
    public static function botBrandCategoryMap(): array
    {
        return BotClassifier::categoryMap();
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
     * Missing dates are queued for aggregation, never aggregated synchronously:
     * a scheduler-less environment (no cron / schedule:work) would otherwise
     * block the panel dashboard for seconds on every first visit of the day.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @param  bool  $includeToday  when true, today is also queued so
     *                              same-day views (bot analytics) get a row
     * @return void
     */
    public function ensureDailyAggregated(Carbon $startDate, Carbon $endDate, bool $includeToday = false): void
    {
        $existingDates = VisitDaily::query()
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date)
            ->flip();

        $datesToAggregate = [];
        $current          = $startDate->copy();
        $lastDay          = $includeToday ? Carbon::today()->addDay() : Carbon::today();
        while ($current->lt($lastDay)) {
            $dateStr = $current->toDateString();
            if (! isset($existingDates[$dateStr])) {
                $datesToAggregate[] = $dateStr;
            }
            $current->addDay();
        }

        if (empty($datesToAggregate)) {
            return;
        }

        if (VisitEvent::query()->doesntExist()) {
            return;
        }

        foreach ($datesToAggregate as $dateStr) {
            AggregateVisitDailyJob::dispatch($dateStr);
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
            if (! VisitHourlyDaily::query()->where('date', $today)->exists() && VisitEvent::query()->exists()) {
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
