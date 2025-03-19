<?php

namespace Plugin\Cloak\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Plugin\Cloak\Models\Cloak;
use Plugin\Cloak\Repositories\CloakRepo;

class CloakService
{
    protected $cloakRepo;

    // Common bot user agents
    protected $botUserAgents = [
        'googlebot', 'bingbot', 'yandex', 'baiduspider', 'facebookexternalhit',
        'twitterbot', 'rogerbot', 'linkedinbot', 'embedly', 'quora link preview',
        'showyoubot', 'outbrain', 'pinterest', 'slackbot', 'vkShare', 'W3C_Validator',
        'redditbot', 'Applebot', 'WhatsApp', 'flipboard', 'tumblr', 'bitlybot',
        'SkypeUriPreview', 'nuzzel', 'Discordbot', 'Google Page Speed', 'Qwantify',
        'pinterestbot', 'Bitrix link preview', 'XING-contenttabreceiver', 'Chrome-Lighthouse',
        'TelegramBot', 'Google-AdSense-Snapshot', 'Googlebot-Image', 'PhantomJS', 'Safari',
        'headless', 'Chrome', 'Firefox', 'Browser', 'android',
    ];

    // IP ranges for common ad networks and bots
    protected $botIpRanges = [
        '66.249.64.0/19',   // Google
        '17.0.0.0/8',       // Apple
        '8.8.4.0/24',       // Google DNS
        '8.8.8.0/24',       // Google DNS
    ];

    public function __construct(CloakRepo $cloakRepo)
    {
        $this->cloakRepo = $cloakRepo;
    }

    /**
     * Get cloaks filtered by status
     *
     * @param  string|null  $status
     * @param  int  $paginate
     * @return mixed
     */
    public function getCloaksFiltered(?string $status, $paginate = 20): mixed
    {
        return $this->cloakRepo->findCloaksByFilters($status, $paginate);
    }

    /**
     * Create a new cloak
     *
     * @param  Request  $request
     * @return Cloak
     */
    public function createCloak(Request $request): Cloak
    {
        return $this->cloakRepo->create($request->all());
    }

    /**
     * Update an existing cloak
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Cloak
     */
    public function updateCloak(Request $request, int $id): Cloak
    {
        $cloak = $this->findById($id);

        return $this->cloakRepo->update($cloak, $request->all());
    }

    /**
     * Find cloak by ID
     *
     * @param  int  $id
     * @return Cloak|null
     */
    public function findById(int $id): ?Cloak
    {
        return $this->cloakRepo->detail($id);
    }

    /**
     * Get URL to redirect to based on request analysis
     *
     * @param  Cloak  $cloak
     * @param  Request  $request
     * @return string
     */
    public function getRedirectUrl(Cloak $cloak, Request $request): string
    {
        // Increment visits counter
        $cloak->incrementVisits();

        // Check if visitor is a bot or should see the safe page
        if ($this->shouldShowSafePage($cloak, $request)) {
            return $cloak->safe_url;
        }

        // For regular visitors, increment redirects and show target page
        $cloak->incrementRedirects();

        // Handle one-time redirect
        if ($cloak->one_time_redirect) {
            $this->setVisitedCookie($cloak->id);
        }

        return $cloak->target_url;
    }

    /**
     * Check if the visitor should see the safe page
     *
     * @param  Cloak  $cloak
     * @param  Request  $request
     * @return bool
     */
    public function shouldShowSafePage(Cloak $cloak, Request $request): bool
    {
        // 1. Check one-time redirect - if already visited and one_time_redirect is true
        if ($cloak->one_time_redirect && $this->hasVisitedBefore($cloak->id)) {
            return true;
        }

        // 2. Check for bots if detection is enabled
        if ($cloak->detect_bots && $this->isBot($request)) {
            return true;
        }

        // 3. Check IP filters
        if (! empty($cloak->ip_filters) && $this->matchesIpFilter($request->ip(), $cloak->ip_filters)) {
            return true;
        }

        // 4. Check country filters
        if (! empty($cloak->country_filters) && $this->matchesCountryFilter($request->ip(), $cloak->country_filters)) {
            return true;
        }

        // 5. Check user agent filters
        if (! empty($cloak->user_agent_filters) && $this->matchesUserAgentFilter($request->userAgent(), $cloak->user_agent_filters)) {
            return true;
        }

        // 6. Check referrer filters
        if (! empty($cloak->referrer_filters) && $this->matchesReferrerFilter($request->header('referer'), $cloak->referrer_filters)) {
            return true;
        }

        // 7. Check UTM parameters if specified
        if ($this->hasUtmMismatch($cloak, $request)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the visitor has visited before (for one-time redirect)
     *
     * @param  int  $cloakId
     * @return bool
     */
    protected function hasVisitedBefore(int $cloakId): bool
    {
        return Cookie::has('cloak_visited_'.$cloakId);
    }

    /**
     * Set cookie to mark visitor has visited
     *
     * @param  int  $cloakId
     * @return void
     */
    public function setVisitedCookie(int $cloakId): void
    {
        Cookie::queue('cloak_visited_'.$cloakId, '1', 43200); // 30 days
    }

    /**
     * Check if the request is from a bot
     *
     * @param  Request  $request
     * @return bool
     */
    protected function isBot(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        // Empty user agent is suspicious
        if (empty($userAgent)) {
            return true;
        }

        // Check for common bot user agents
        foreach ($this->botUserAgents as $botAgent) {
            if (strpos($userAgent, strtolower($botAgent)) !== false) {
                return true;
            }
        }

        // Check for suspicious IP ranges
        foreach ($this->botIpRanges as $ipRange) {
            if ($this->ipInRange($request->ip(), $ipRange)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in a CIDR range
     *
     * @param  string  $ip
     * @param  string  $range
     * @return bool
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') !== false) {
            // CIDR notation
            [$subnet, $bits] = explode('/', $range);
            $ip              = ip2long($ip);
            $subnet          = ip2long($subnet);
            $mask            = -1 << (32 - $bits);
            $subnet &= $mask;

            return ($ip & $mask) == $subnet;
        } else {
            // Single IP
            return $ip === $range;
        }
    }

    /**
     * Check if IP matches any filter
     *
     * @param  string  $ip
     * @param  array  $filters
     * @return bool
     */
    protected function matchesIpFilter(string $ip, array $filters): bool
    {
        foreach ($filters as $filter) {
            if ($this->ipInRange($ip, $filter)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if country matches any filter
     *
     * @param  string  $ip
     * @param  array  $countries
     * @return bool
     */
    protected function matchesCountryFilter(string $ip, array $countries): bool
    {
        try {
            // Get country from IP (using a third-party service)
            $response = Http::get("http://ip-api.com/json/{$ip}");
            if ($response->successful()) {
                $data    = $response->json();
                $country = $data['countryCode'] ?? '';

                return in_array($country, $countries);
            }
        } catch (\Exception $e) {
            // If API fails, ignore country check
        }

        return false;
    }

    /**
     * Check if user agent matches any filter
     *
     * @param  string|null  $userAgent
     * @param  array  $filters
     * @return bool
     */
    protected function matchesUserAgentFilter(?string $userAgent, array $filters): bool
    {
        if (empty($userAgent)) {
            return true; // Empty user agent is suspicious
        }

        $userAgent = strtolower($userAgent);

        foreach ($filters as $filter) {
            if (strpos($userAgent, strtolower($filter)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if referrer matches any filter
     *
     * @param  string|null  $referrer
     * @param  array  $filters
     * @return bool
     */
    protected function matchesReferrerFilter(?string $referrer, array $filters): bool
    {
        if (empty($referrer)) {
            return false;
        }

        $referrer = strtolower($referrer);

        foreach ($filters as $filter) {
            if (strpos($referrer, strtolower($filter)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if UTM parameters match
     *
     * @param  Cloak  $cloak
     * @param  Request  $request
     * @return bool
     */
    protected function hasUtmMismatch(Cloak $cloak, Request $request): bool
    {
        // If UTM parameters are specified in the cloak configuration,
        // check if they match the request

        if (! empty($cloak->utm_source) && $request->query('utm_source') !== $cloak->utm_source) {
            return true;
        }

        if (! empty($cloak->utm_medium) && $request->query('utm_medium') !== $cloak->utm_medium) {
            return true;
        }

        if (! empty($cloak->utm_campaign) && $request->query('utm_campaign') !== $cloak->utm_campaign) {
            return true;
        }

        return false;
    }
}
