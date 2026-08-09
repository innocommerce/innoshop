<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\Mcp\Http\Controllers\McpController;
use InnoShop\Mcp\Http\Middleware\EnsureMcpEnabled;
use InnoShop\Mcp\Http\Middleware\SetMcpLocale;
use InnoShop\Mcp\Http\Middleware\ValidateMcpOrigin;
use InnoShop\Mcp\Server\InnoShopMcpServer;
use InnoShop\Restapi\Middleware\EnsureUserIsAdmin;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', InnoShopMcpServer::class)
    ->middleware([
        EnsureMcpEnabled::class,
        ValidateMcpOrigin::class,
        'auth:sanctum',
        EnsureUserIsAdmin::class,
    ]);

// 'web' provides the session the locale choice is persisted in; SetMcpLocale
// negotiates the language against the MCP language packs.
Route::get('/mcp', [McpController::class, 'welcome'])
    ->middleware(['web', EnsureMcpEnabled::class, SetMcpLocale::class]);

Route::get('/mcp/locale/{code}', [McpController::class, 'switchLocale'])
    ->name('mcp.locale.switch')
    ->middleware(['web']);
