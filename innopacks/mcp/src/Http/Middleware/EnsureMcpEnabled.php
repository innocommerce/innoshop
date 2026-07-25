<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\MCP\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MCP is opt-in: hidden (404) unless explicitly enabled in system settings.
 */
class EnsureMcpEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! system_setting('mcp_enabled')) {
            abort(404);
        }

        return $next($request);
    }
}
