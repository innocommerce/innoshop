<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Services\CountryLocaleResolver;
use Throwable;

class SetFrontLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $availableLocales = $this->usableLocales();

        $currentLocale = $this->resolveLocale($request, $availableLocales);

        if (env('APP_LOCALE_FORCE')) {
            $currentLocale = env('APP_LOCALE_FORCE');
        }

        app()->setLocale($currentLocale);
        session(['locale' => $currentLocale]);

        if ($this->shouldRedirectToLocalePrefix($request, $currentLocale)) {
            return redirect()->to($this->localePrefixedUrl($request, $currentLocale), 302);
        }

        return $next($request);
    }

    /**
     * Resolve the storefront locale for the current request.
     *
     * Priority: URL locale prefix > session > IP country > browser language >
     * storefront default locale.
     *
     * @param  string[]  $availableLocales
     */
    private function resolveLocale(Request $request, array $availableLocales): string
    {
        // 1. The locale prefix in the URL always wins, it is an explicit choice.
        $urlLocale = $request->segment(1);
        if (in_array($urlLocale, $availableLocales, true)) {
            return $urlLocale;
        }

        // 2. A locale stored in the session, either picked by the visitor through
        //    the language switcher or detected from the IP on an earlier request.
        $sessionLocale = session('locale');
        if (in_array($sessionLocale, $availableLocales, true)) {
            return $sessionLocale;
        }

        // 3. The country the visitor's IP belongs to.
        $ipLocale = $this->detectIpLocale($request, $availableLocales);
        if ($ipLocale !== null) {
            return $ipLocale;
        }

        // 4. The language preferred by the browser.
        $browserLocale = $this->detectBrowserLocale($request, $availableLocales);
        if ($browserLocale !== null) {
            return $browserLocale;
        }

        // 5. The storefront default language configured in the panel.
        return $this->fallbackLocale($availableLocales);
    }

    /**
     * Detect the locale from the country the visitor's IP belongs to.
     *
     * @param  string[]  $availableLocales
     */
    private function detectIpLocale(Request $request, array $availableLocales): ?string
    {
        if (! system_setting('auto_detect_locale_by_ip', false)) {
            return null;
        }

        $ip = $this->clientIp($request);
        if ($ip === null) {
            return null;
        }

        $locale = app(CountryLocaleResolver::class)->resolveFromIp($ip, $availableLocales);

        // The resolver must only return a locale that can actually be served,
        // otherwise the visitor would end up on a 404.
        return in_array($locale, $availableLocales, true) ? $locale : null;
    }

    /**
     * Detect locale from browser Accept-Language header.
     */
    private function detectBrowserLocale(Request $request, array $availableLocales): ?string
    {
        $acceptLanguage = $request->headers->get('Accept-Language', '');
        if (! $acceptLanguage) {
            return null;
        }

        $browserLocales = [];
        preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)\s*(?:;\s*q\s*=\s*(\d+(?:\.\d+)?))?/i', $acceptLanguage, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $lang                  = strtolower(str_replace('_', '-', $match[1]));
            $qual                  = isset($match[2]) ? (float) $match[2] : 1.0;
            $browserLocales[$lang] = $qual;
        }
        arsort($browserLocales);

        $localeMap = [];
        foreach ($availableLocales as $code) {
            $localeMap[strtolower(str_replace('_', '-', $code))] = $code;
        }

        foreach ($browserLocales as $browserLang => $q) {
            if (isset($localeMap[$browserLang])) {
                return $localeMap[$browserLang];
            }
            $primary = explode('-', $browserLang)[0];
            foreach ($localeMap as $key => $code) {
                if (str_starts_with($key, $primary)) {
                    return $code;
                }
            }
        }

        return null;
    }

    /**
     * The storefront default locale, or the first enabled locale when the
     * configured default is not enabled any more.
     *
     * @param  string[]  $availableLocales
     */
    private function fallbackLocale(array $availableLocales): string
    {
        $default = setting_locale_code();
        if (in_array($default, $availableLocales, true)) {
            return $default;
        }

        return $availableLocales[0] ?? config('app.locale', 'en');
    }

    /**
     * Get the visitor IP address, skipping private and reserved ranges.
     */
    private function clientIp(Request $request): ?string
    {
        $ip = (string) $request->ip();

        // Handle IPv6 mapped IPv4 addresses
        if (str_starts_with($ip, '::ffff:')) {
            $ip = substr($ip, 7);
        }

        if ($ip === '' || $this->isReservedIp($ip)) {
            return null;
        }

        return $ip;
    }

    /**
     * Check whether an IP address is private, loopback or otherwise reserved.
     */
    private function isReservedIp(string $ip): bool
    {
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return filter_var($ip, FILTER_VALIDATE_IP, $flags) === false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return filter_var($ip, FILTER_VALIDATE_IP, $flags) === false;
        }

        return true;
    }

    /**
     * Whether the storefront uses locale prefixes in URLs.
     */
    private function localePrefixEnabled(): bool
    {
        return ! hide_url_locale() && locales()->isNotEmpty();
    }

    /**
     * The locales that can actually be served.
     *
     * A locale is only usable when it is enabled in the panel *and* its
     * storefront routes are registered. The routes are registered while the
     * application boots, so a language that was enabled after the last route
     * cache clear has no /{locale} routes yet and would answer with a 404.
     *
     * @return string[]
     */
    private function usableLocales(): array
    {
        $enabled = locales()->pluck('code')->toArray();
        if (empty($enabled)) {
            return [];
        }

        // Without locale prefixes in the URL every enabled language is served
        // on the same routes.
        if (! $this->localePrefixEnabled()) {
            return $enabled;
        }

        $routed = $this->registeredLocaleCodes();
        if (empty($routed)) {
            return $enabled;
        }

        return array_values(array_intersect($enabled, $routed));
    }

    /**
     * The locale codes that have storefront routes registered.
     *
     * @return string[]
     */
    private function registeredLocaleCodes(): array
    {
        $codes = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! is_string($name)) {
                continue;
            }

            if (preg_match('/^([a-z0-9-]+)\.front\./', $name, $matches)) {
                $codes[$matches[1]] = true;
            }
        }

        return array_keys($codes);
    }

    /**
     * Whether the request should be redirected to its locale prefixed variant.
     *
     * Only the storefront root is redirected. The other routes without a locale
     * prefix (/social/*, /upload/*, /sitemap.xml) must keep working as they are.
     *
     * The root is left alone when the visitor is served the storefront default
     * language, redirecting it would only add a hop without changing anything.
     */
    private function shouldRedirectToLocalePrefix(Request $request, string $currentLocale): bool
    {
        if (! system_setting('auto_detect_locale_by_ip', false)) {
            return false;
        }

        if (! $this->localePrefixEnabled()) {
            return false;
        }

        if ($request->method() !== 'GET') {
            return false;
        }

        if ($request->path() !== '/') {
            return false;
        }

        // Never send the visitor to a locale without routes, it would be a 404.
        if (! in_array($currentLocale, $this->usableLocales(), true)) {
            return false;
        }

        return $currentLocale !== setting_locale_code();
    }

    /**
     * Build the locale prefixed URL for the storefront root.
     */
    private function localePrefixedUrl(Request $request, string $locale): string
    {
        try {
            $url = route($locale.'.front.home.index');
        } catch (Throwable $e) {
            $url = url('/'.$locale);
        }

        $query = $request->getQueryString();
        if (! empty($query)) {
            $url .= (str_contains($url, '?') ? '&' : '?').$query;
        }

        return $url;
    }
}
