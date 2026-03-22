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
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates Scribe HTML, OpenAPI, and Postman routes behind system setting {@see api_docs_enabled()}.
 */
class EnsureApiDocumentationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! api_docs_enabled()) {
            abort(404);
        }

        return $next($request);
    }
}
