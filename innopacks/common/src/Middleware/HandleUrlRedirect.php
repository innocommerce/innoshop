<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use InnoShop\Common\Services\UrlRedirectService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Turns front-side 404s into permanent 301s when the requested path has a
 * redirect rule (#157). Lives on the GLOBAL middleware stack because stale
 * URLs usually 404 at the router level, which never reaches route-group
 * middleware; panel/admin/api traffic is excluded inside. Safe methods
 * only: a 301 on POST would rewrite form endpoints. No rule matched → the
 * original 404 (or exception) continues untouched, keeping the errors.404
 * view behaviour identical.
 */
class HandleUrlRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (NotFoundHttpException $exception) {
            $target = $this->matchRedirectTarget($request);
            if ($target !== null) {
                return Redirect::to($target, 301);
            }

            throw $exception;
        }

        if ($response->getStatusCode() === 404) {
            $target = $this->matchRedirectTarget($request);
            if ($target !== null) {
                return Redirect::to($target, 301);
            }
        }

        return $response;
    }

    private function matchRedirectTarget(Request $request): ?string
    {
        // Sitting on the global stack, we see admin traffic too — stay hands-off.
        if ($request->is('panel/*') || $request->is('panel') || $request->is('admin/*') || $request->is('admin')) {
            return null;
        }

        if ($request->is('api/*') || $request->is('api')) {
            return null;
        }

        // A 301 on a form endpoint would rewrite POST semantics; GET/HEAD only.
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return null;
        }

        // decodedPath() so non-ASCII slugs (not ASCII-normalized in InnoShop)
        // match the raw slug stored in the table — getPathInfo keeps %xx encoded.
        return UrlRedirectService::resolve($request->decodedPath());
    }
}
