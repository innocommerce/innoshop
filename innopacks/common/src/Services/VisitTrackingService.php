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
     * Constructor
     */
    public function __construct()
    {
        $this->geoLocationService = new GeoLocationService;
        $this->detect             = new MobileDetect;
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

            // Parse user agent
            $this->detect->setUserAgent($request->userAgent() ?? '');

            // Get location information
            $ip       = $this->getClientIp($request);
            $location = $this->geoLocationService->getLocation($ip);

            // Get or create visit record (single table design: one record per session)
            $visit = Visit::where('session_id', $sessionId)->first();

            $isBot     = $this->isBotUserAgent($request->userAgent());
            $deviceTyp = $isBot ? 'bot' : $this->getDeviceType();

            if ($visit) {
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

                // Set is_bot/device_type once (don't overwrite real visit data on bot traffic)
                if (! $visit->is_bot && $isBot) {
                    $updateData['is_bot']      = true;
                    $updateData['device_type'] = 'bot';
                }

                $visit->update($updateData);
            } else {
                // Create new visit record (first visit of the session)
                $visit = Visit::create([
                    'session_id'       => $sessionId,
                    'customer_id'      => $customerId,
                    'ip_address'       => $ip,
                    'user_agent'       => $request->userAgent(),
                    'country_code'     => $location['country_code'],
                    'country_name'     => $location['country_name'],
                    'city'             => $location['city'],
                    'referrer'         => $request->header('referer'),
                    'device_type'      => $deviceTyp,
                    'is_bot'           => $isBot,
                    'browser'          => $this->getBrowser(),
                    'os'               => $this->getOperatingSystem(),
                    'locale'           => front_locale_code(),
                    'first_visited_at' => now(),
                    'last_visited_at'  => now(),
                ]);
            }

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
     * Check if should skip tracking
     *
     * @param  Request  $request
     * @return bool
     */
    private function shouldSkipTracking(Request $request): bool
    {
        $routeName = $request->route()?->getName();

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
     * Detect crawler/bot/spider/scanner User-Agent.
     * Common bot UA keywords covering Googlebot, Bingbot, Baiduspider, AhrefsBot,
     * semrush, GPTBot, python-requests, curl, wget, scanner families, etc.
     *
     * @param  string|null  $userAgent
     * @return bool
     */
    private function isBotUserAgent(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $ua = strtolower($userAgent);

        $botPatterns = [
            'bot', 'crawler', 'spider', 'scrap', 'scout', 'scan', 'check', 'fetch',
            'archive', 'heritrix', 'archive.org',
            'slurp', 'teoma', 'ia_archiver',
            'googlebot', 'bingbot', 'baiduspider', 'sogou', 'yisouspider', 'bytespider',
            'duckduckbot', 'yandexbot', 'exabot', 'konqueror', 'facebot', 'facebookexternalhit',
            'twitterbot', 'linkedinbot', 'telegrambot', 'whatsapp', 'skypeuripreview',
            'ahrefsbot', 'semrushbot', 'dotbot', 'mj12bot', 'petalbot', 'applebot',
            'gptbot', 'chatgpt-user', 'claudebot', 'claude-user', 'ccbot', 'perplexitybot',
            'uptimerobot', 'statuscake', 'pingdom', 'site24x7', 'newrelicpinger',
            'python-requests', 'python-urllib', 'aiohttp', 'httpx', 'axios',
            'curl', 'wget', 'httpclient', 'okhttp', 'java/', 'go-http-client',
            'node-fetch', 'got ', 'lwp-',
            'masscan', 'nmap', 'nikto', 'sqlmap', 'wpscan', 'dirbuster', 'gobuster',
            'zgrab', 'censys', 'shodan', 'shadow',
        ];

        foreach ($botPatterns as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
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
