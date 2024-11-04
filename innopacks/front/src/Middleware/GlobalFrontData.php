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
use InnoShop\Front\Repositories\FooterMenuRepo;
use InnoShop\Front\Repositories\HeaderMenuRepo;

class GlobalFrontData
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
        $customer = current_customer();
        $favTotal = $customer ? $customer->favorites->count() : 0;

        view()->share('current_locale', current_locale());
        view()->share('header_menus', HeaderMenuRepo::getInstance()->getMenus());
        view()->share('footer_menus', FooterMenuRepo::getInstance()->getMenus());
        view()->share('customer', $customer);
        view()->share('fav_total', $favTotal);

        return $next($request);
    }
}
