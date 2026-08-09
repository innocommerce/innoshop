<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MCP spec requirement: validate the Origin header to prevent DNS rebinding
 * attacks. Non-browser MCP clients send no Origin and are allowed through;
 * a present Origin must match the app host or a local address.
 */
class ValidateMcpOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        if ($origin && ! $this->isAllowed($origin)) {
            abort(403, 'Forbidden origin.');
        }

        return $next($request);
    }

    private function isAllowed(string $origin): bool
    {
        $host = parse_url($origin, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)) {
            return true;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return $appHost && strcasecmp($host, $appHost) === 0;
    }
}
