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
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class CustomerAuthentication extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $this->authenticate($request, $guards);

        $customer = current_customer();
        if ($customer && ! $customer->active) {
            auth('customer')->logout();

            return redirect(front_route('login.index'));
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  Request  $request
     * @return string|void
     * @throws Exception
     */
    protected function redirectTo(Request $request)
    {
        if (! $request->expectsJson()) {
            session(['front_redirect_uri' => $request->getUri()]);

            return front_route('login.index');
        }
    }
}
