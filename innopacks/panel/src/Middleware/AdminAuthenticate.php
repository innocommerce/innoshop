<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use InnoShop\Panel\Repositories\RouteRepo;

class AdminAuthenticate extends Middleware
{
    /**
     * @param  $request
     * @param  Closure  $next
     * @param  ...$guards
     * @return mixed
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $this->authenticate($request, $guards);

        $routeName = $request->route()->getName();
        $routeCode = str_replace('panel.', '', $routeName);
        if (in_array($routeCode, RouteRepo::IGNORE_LIST)) {
            return $next($request);
        }

        $routeCode = str_replace('.', '_', $routeCode);
        if (! current_admin()->can($routeCode)) {
            abort(403);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|void
     */
    protected function redirectTo(Request $request)
    {
        if (! $request->expectsJson()) {
            session(['panel_redirect_uri' => $request->getUri()]);

            return panel_route('login.index');
        }
    }
}
