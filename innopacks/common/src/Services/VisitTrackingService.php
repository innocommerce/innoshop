<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Detection\MobileDetect;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Visit\Visit;

class VisitTrackingService
{
    /**
     * GeoLocation service instance
     *
     * @var GeoLocationService
     */
    private GeoLocationService $geoLocationService;

    /**
     * User agent parser
     *
     * @var MobileDetect
     */
    private MobileDetect $detect;

    /**
     * Behavioural bot classifier
     *
     * @var BotBehaviorClassifier
     */
    private BotBehaviorClassifier $behaviorClassifier;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->geoLocationService = new GeoLocationService;
        $this->detect             = new MobileDetect;
        $this->behaviorClassifier = new BotBehaviorClassifier;
    }

    /**
     * Track a visit
     *
     * @param  Request  $request
     * @param  string  $sessionId
     * @param  int|null  $customerId
     * @return Visit|null
     */
    public function trackVisit(Request $request, string $sessionId, ?int $customerId = null): ?Visit
    {
        try {
            // Skip tracking for certain routes
            if ($this->shouldSkipTracking($request)) {
                return null;
            }

            // Classify first: bots never touch the visits/visit_events tables
            // (they get one lightweight upsert into bot_daily instead) and
            // skip the geo lookup entirely.
            $classification = BotClassifier::classify($request->userAgent());

            $ip = $this->getClientIp($request);

            if ($classification['is_bot']) {
                $this->recordBotHit($classification, $ip, $request->path(), $request->isMethod('GET'));

                return null;
            }

            // Behavioural check: a crawler wearing a real browser UA passes
            // the UA rules above and would be stored as a genuine visitor.
            // The behavioural rules catch the cookie-less burst pattern.
            // Evaluated before the geo lookup so a blocked bot costs no
            // geolocation work.
            $behavior = $this->behaviorClassifier->classify($request);
            if ($behavior !== null) {
                $this->recordBotHit($behavior, $ip, $request->path(), $request->isMethod('GET'));

                return null;
            }

            // Parse user agent
            $this->detect->setUserAgent($request->userAgent() ?? '');

            // Get location information
            $location = $this->geoLocationService->getLocation($ip);

            // Get or create visit record (single table design: one record per session).
            // firstOrCreate absorbs the concurrent-first-request race on session_id.
            $visit = Visit::query()->firstOrCreate(
                ['session_id' => $sessionId],
                [
                    'customer_id'      => $customerId,
                    'ip_address'       => $ip,
                    'user_agent'       => $request->userAgent(),
                    'country_code'     => $location['country_code'],
                    'country_name'     => $location['country_name'],
                    'city'             => $location['city'],
                    'referrer'         => $request->header('referer'),
                    'device_type'      => $this->getDeviceType(),
                    'is_bot'           => false,
                    'browser'          => $this->getBrowser(),
                    'os'               => $this->getOperatingSystem(),
                    'locale'           => front_locale_code(),
                    'first_visited_at' => now(),
                    'last_visited_at'  => now(),
                ]
            );

            if ($visit->wasRecentlyCreated) {
                return $visit;
            }

            // Update existing visit: update last visited time
            $updateData = [
                'last_visited_at' => now(),
                'customer_id'     => $customerId ?: $visit->customer_id,
            ];

            // Backfill browser/os if empty
            if (empty($visit->browser) || empty($visit->os)) {
                $updateData['browser'] = $this->getBrowser();
                $updateData['os']      = $this->getOperatingSystem();
            }

            $visit->update($updateData);

            return $visit;
        } catch (Exception $e) {
            Log::error('VisitTrackingService: Failed to track visit', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Count a bot request into the aggregated bot tables. Bots are never
     * written to visits/visit_events — two lightweight statements replace
     * the old INSERT + event INSERT, and the tables stay tiny forever
     * (bot_daily grows by one row per brand per day at most).
     *
     * Written as insertOrIgnore + atomic row increment (not upsert/VALUES())
     * so it runs identically on MySQL and sqlite.
     *
     * @param  array{is_bot: bool, brand: string, category: string}  $classification
     */
    protected function recordBotHit(array $classification, string $ip, string $path, bool $isGet): void
    {
        $date     = now()->toDateString();
        $brand    = $classification['brand'] ?: 'unknown';
        $category = $classification['category'] ?: 'suspicious';
        $lastPath = mb_substr($path, 0, 255);

        DB::table('bot_daily')->insertOrIgnore([
            'date'       => $date,
            'bot_brand'  => $brand,
            'category'   => $category,
            'visits'     => 0,
            'page_views' => 0,
            'last_ip'    => $ip,
            'last_path'  => $lastPath,
        ]);

        DB::table('bot_daily')
            ->where('date', $date)
            ->where('bot_brand', $brand)
            ->update([
                'visits'     => DB::raw('visits + 1'),
                'page_views' => DB::raw('page_views + '.($isGet ? '1' : '0')),
                'last_ip'    => $ip,
                'last_path'  => $lastPath,
                'updated_at' => now(),
            ]);

        DB::table('bot_daily_ips')->insertOrIgnore([
            'date'  => $date,
            'ip'    => $ip,
            'brand' => $brand,
        ]);
    }

    /**
     * Check if should skip tracking
     *
     * @param  Request  $request
     * @return bool
     */
    private function shouldSkipTracking(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        // Skip loopback traffic (dev browser, server-side health checks) —
        // no analytics value and it pollutes geo/IP stats.
        if (in_array($request->ip(), ['127.0.0.1', '::1'], true)) {
            return true;
        }

        // Skip API routes and admin routes
        if (str_starts_with($request->path(), 'api/') || str_starts_with($request->path(), 'panel/')) {
            return true;
        }

        // Skip excluded routes
        $excludedRoutes = [
            'carts.mini',
            'countries.index',
            'countries.show',
        ];

        return in_array($routeName, $excludedRoutes);
    }

    /**
     * Get client IP address
     *
     * @param  Request  $request
     * @return string
     */
    private function getClientIp(Request $request): string
    {
        $ip = $request->ip();

        // Handle IPv6 mapped IPv4 addresses
        if (str_starts_with($ip, '::ffff:')) {
            $ip = substr($ip, 7);
        }

        return $ip;
    }

    /**
     * Get device type
     *
     * @return string
     */
    private function getDeviceType(): string
    {
        if ($this->detect->isMobile()) {
            return 'mobile';
        }

        if ($this->detect->isTablet()) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Get browser name from User-Agent string.
     * MobileDetect only matches mobile browsers, so we parse the UA directly.
     *
     * @return string
     */
    private function getBrowser(): string
    {
        $ua = $this->detect->getUserAgent() ?? '';

        $patterns = [
            'Edg/'            => 'Edge',
            'OPR/'            => 'Opera',
            'Opera'           => 'Opera',
            'Vivaldi/'        => 'Vivaldi',
            'Brave/'          => 'Brave',
            'SamsungBrowser/' => 'Samsung Browser',
            'UCBrowser/'      => 'UC Browser',
            'MicroMessenger/' => 'WeChat',
            'QQBrowser/'      => 'QQ Browser',
            'Firefox/'        => 'Firefox',
            'FxiOS/'          => 'Firefox',
            'Chrome/'         => 'Chrome',
            'CriOS/'          => 'Chrome',
            'Safari/'         => 'Safari',
            'MSIE'            => 'IE',
            'Trident/'        => 'IE',
        ];

        foreach ($patterns as $pattern => $name) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return '';
    }

    /**
     * Get operating system name from User-Agent string.
     * MobileDetect only matches mobile OS, so we parse the UA directly.
     *
     * @return string
     */
    private function getOperatingSystem(): string
    {
        $ua = $this->detect->getUserAgent() ?? '';

        $patterns = [
            'HarmonyOS'     => 'HarmonyOS',
            'Android'       => 'Android',
            'iPhone'        => 'iOS',
            'iPad'          => 'iPadOS',
            'iPod'          => 'iOS',
            'Windows Phone' => 'Windows Phone',
            'Windows NT'    => 'Windows',
            'Mac OS X'      => 'macOS',
            'Macintosh'     => 'macOS',
            'Linux'         => 'Linux',
            'CrOS'          => 'Chrome OS',
        ];

        foreach ($patterns as $pattern => $name) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return '';
    }
}
