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

// The welcome page must NOT live on GET /mcp: the MCP streamable transport
// answers GET /mcp with a spec-mandated 405 (Allow: POST, "no server-initiated
// streams"). Shadowing that with a 200 HTML page makes protocol clients treat
// the stream as dead and reconnect in a hot loop — observed 2026-10-15:
// claude-code and WorkBuddy holding /mcp at 2-3 req/s each, one full Laravel
// boot per hit, enough CPU to trip the panel load alert. Humans get the page
// at /mcp/welcome instead; /mcp itself stays protocol-only.
// 'web' provides the session the locale choice is persisted in; SetMcpLocale
// negotiates the language against the MCP language packs.
Route::get('/mcp/welcome', [McpController::class, 'welcome'])
    ->middleware(['web', EnsureMcpEnabled::class, SetMcpLocale::class]);

Route::get('/mcp/locale/{code}', [McpController::class, 'switchLocale'])
    ->name('mcp.locale.switch')
    ->middleware(['web']);
