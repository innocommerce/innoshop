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
            $this->detect->setUserAgent($request->userAgent());

            // Get location information
            $ip       = $this->getClientIp($request);
            $location = $this->geoLocationService->getLocation($ip);

            // Get or create visit record (single table design: one record per session)
            $visit = Visit::where('session_id', $sessionId)->first();

            if ($visit) {
                // Update existing visit: update last visited time
                $visit->update([
                    'last_visited_at' => now(),
                    'customer_id'     => $customerId ?: $visit->customer_id,
                ]);
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
                    'device_type'      => $this->getDeviceType(),
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
     * Get browser name
     *
     * @return string
     */
    private function getBrowser(): string
    {
        if ($this->detect->isChrome()) {
            return 'Chrome';
        }
        if ($this->detect->isFirefox()) {
            return 'Firefox';
        }
        if ($this->detect->isSafari()) {
            return 'Safari';
        }
        if ($this->detect->isOpera()) {
            return 'Opera';
        }
        if ($this->detect->isIE()) {
            return 'IE';
        }

        return '';
    }

    /**
     * Get operating system name
     *
     * @return string
     */
    private function getOperatingSystem(): string
    {
        if ($this->detect->isIOS()) {
            return 'iOS';
        }
        if ($this->detect->isAndroidOS()) {
            return 'Android';
        }
        if ($this->detect->isWindowsPhoneOS()) {
            return 'Windows Phone';
        }
        if ($this->detect->isWindows()) {
            return 'Windows';
        }
        if ($this->detect->isMac()) {
            return 'macOS';
        }
        if ($this->detect->isLinux()) {
            return 'Linux';
        }

        return '';
    }
}
