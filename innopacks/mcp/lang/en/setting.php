<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'MCP Service',
    'mcp_service_desc'  => 'Expose store data (products, orders, stock, sales stats) to MCP clients such as Claude and Cursor. Read-only, requires an admin token.',
    'enable_mcp'        => 'Enable MCP endpoint',
    'endpoint_url'      => 'Endpoint URL',
    'auth_header'       => 'Authentication',
    'token_hint'        => 'Obtain a token via POST :url with an admin account, then pass it as a Bearer token.',
    'usage_title'       => 'How to use',
    'usage_cursor'      => 'Cursor: add this to ~/.cursor/mcp.json (or Settings → MCP → Add new server):',
    'usage_claude_code' => 'Claude Code: run this command:',
];
