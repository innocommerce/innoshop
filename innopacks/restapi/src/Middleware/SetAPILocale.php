<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class SetAPILocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $currentLocale = $request->header('locale');
        if (empty($currentLocale)) {
            $currentLocale = $request->get('locale');
        }

        $availableLocales = locales()->pluck('code')->toArray();
        if (! in_array($currentLocale, $availableLocales)) {
            $currentLocale = setting_locale_code();
        }

        if (env('APP_LOCALE_FORCE')) {
            $currentLocale = env('APP_LOCALE_FORCE');
        }

        app()->setLocale($currentLocale);
        session(['locale' => $currentLocale]);

        return $next($request);
    }
}
