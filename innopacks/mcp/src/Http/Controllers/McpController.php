<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use InnoShop\Aicore\Services\ToolRegistry;
use InnoShop\Mcp\McpAccess;

class McpController
{
    public function welcome(ToolRegistry $registry): View
    {
        $name    = system_setting('panel_name') ?: system_setting('shop_name') ?: config('app.name', 'InnoShop');
        $locales = McpAccess::locales();

        return view('mcp::welcome', [
            'shopName' => $name,
            'shopLogo' => asset('images/logo-icon-light.svg'),
            'mcpUrl'   => url('/mcp'),
            'loginUrl' => url('/api/panel/login'),
            // The doc page lists every tool; write tools carry a badge and the
            // note below. The protocol itself still filters them (see server).
            'tools'         => $registry->all(),
            'writeEnabled'  => McpAccess::writeEnabled(),
            'locales'       => $locales,
            'currentLocale' => $locales[app()->getLocale()] ?? reset($locales),
        ]);
    }

    /**
     * Session-only locale switch for the welcome page: no locale code ever
     * appears in the /mcp URL itself.
     */
    public function switchLocale(string $code): RedirectResponse
    {
        if (array_key_exists($code, McpAccess::locales())) {
            session(['locale' => $code]);
        }

        return redirect('/mcp');
    }
}
