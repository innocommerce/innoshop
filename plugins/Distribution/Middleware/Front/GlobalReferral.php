<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Middleware\Front;

use Closure;
use Exception;
use Illuminate\Http\Request;

class GlobalReferral
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
        $refCode = $request->get('ref');
        if (empty($refCode)) {
            return $next($request);
        }

        session(['ref_code' => $refCode]);

        return $next($request);
    }
}
