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
            $localeCode = front_locale_code();
            app()->setLocale($localeCode);
            session(['locale' => $localeCode]);

            return $next($request);
        }

        $availableLocales = locales()->pluck('code')->toArray();
        if (in_array($currentLocale, $availableLocales)) {
            app()->setLocale($currentLocale);
            session(['locale' => $currentLocale]);
        }

        return $next($request);
    }
}
