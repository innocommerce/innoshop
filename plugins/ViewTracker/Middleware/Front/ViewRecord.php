<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ViewTracker\Middleware\Front;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Plugin\ViewTracker\Repositories\ViewLogRepo;
use Throwable;

class ViewRecord
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     * @throws Exception|Throwable
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $pureRoute = pure_route_name();
        $exclude   = [
            'carts.mini', 'countries.index', 'countries.show',
        ];
        if (in_array($pureRoute, $exclude)) {
            return $next($request);
        }

        Log::info('ViewRecord Middleware - '.$pureRoute);

        $requestData = $request->all();
        $parsedData  = ViewLogRepo::parseRequest($request);
        $allData     = array_merge($requestData, $parsedData);

        $response = $next($request);

        $allData['status_code'] = $response->status();
        ViewLogRepo::getInstance()->create($allData);

        return $response;
    }
}
