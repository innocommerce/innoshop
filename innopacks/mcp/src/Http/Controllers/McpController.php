<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\MCP\Http\Controllers;

use Illuminate\Contracts\View\View;
use InnoShop\AI\Services\ToolRegistry;

class McpController
{
    public function welcome(ToolRegistry $registry): View
    {
        $name = system_setting('panel_name') ?: system_setting('shop_name') ?: config('app.name', 'InnoShop');

        $locale = request()->query('lang') ?: front_locale_code();
        if ($locale) {
            app()->setLocale($locale);
        }

        return view('mcp::welcome', [
            'shopName' => $name,
            'shopLogo' => asset('images/logo-icon-light.svg'),
            'mcpUrl'   => url('/mcp'),
            'loginUrl' => url('/api/panel/login'),
            'tools'    => $registry->all(),
        ]);
    }
}
