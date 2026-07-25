<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use InnoShop\MCP\Http\Controllers\McpController;
use InnoShop\MCP\Http\Middleware\EnsureMcpEnabled;
use InnoShop\MCP\Http\Middleware\ValidateMcpOrigin;
use InnoShop\MCP\Server\InnoShopMcpServer;
use InnoShop\RestAPI\Middleware\EnsureUserIsAdmin;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', InnoShopMcpServer::class)
    ->middleware([
        EnsureMcpEnabled::class,
        ValidateMcpOrigin::class,
        'auth:sanctum',
        EnsureUserIsAdmin::class,
    ]);

Route::get('/mcp', [McpController::class, 'welcome'])
    ->middleware([EnsureMcpEnabled::class]);
