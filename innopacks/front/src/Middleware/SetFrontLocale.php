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
        $currentLocale = $request->segment(1);
        if (empty($currentLocale)) {
            $currentLocale = front_locale_code();
        }

        $availableLocales = locales()->pluck('code')->toArray();
        if (! in_array($currentLocale, $availableLocales)) {
            $currentLocale = $this->detectBrowserLocale($request, $availableLocales) ?? setting_locale_code();
        }

        if (env('APP_LOCALE_FORCE')) {
            $currentLocale = env('APP_LOCALE_FORCE');
        }

        app()->setLocale($currentLocale);
        session(['locale' => $currentLocale]);

        return $next($request);
    }

    /**
     * Detect locale from browser Accept-Language header.
     * Only applies when the user has no previous locale in session.
     */
    private function detectBrowserLocale(Request $request, array $availableLocales): ?string
    {
        if (session()->has('locale')) {
            return null;
        }

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
}
