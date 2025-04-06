<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectToLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Get the first segment of the URL (potential locale)
        $segment = $request->segment(1);
        
        // Get available locales
        $availableLocales = locales()->pluck('code')->toArray();
        
        // If the first segment is not a valid locale, redirect to the default locale
        if (!in_array($segment, $availableLocales)) {
            // Get the default locale
            $defaultLocale = setting_locale_code();
            
            // Get the full path
            $path = $request->path();
            if ($path === '/') {
                $path = '';
            }
            
            // Redirect to the same path with the default locale prefix
            return redirect()->to('/' . $defaultLocale . ($path ? '/' . $path : ''));
        }
        
        return $next($request);
    }
}
