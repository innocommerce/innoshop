<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'MCP 服務',
    'mcp_service_desc'  => '將店鋪資料（商品、訂單、庫存、銷售統計）透過 MCP 協議開放給 Claude、Cursor 等客戶端。唯讀，需管理員 Token。',
    'enable_mcp'        => '啟用 MCP 端點',
    'endpoint_url'      => '端點地址',
    'auth_header'       => '認證方式',
    'token_hint'        => '使用管理員帳號 POST :url 取得 Token，以 Bearer 方式攜帶。',
    'usage_title'       => '使用方式',
    'usage_cursor'      => 'Cursor：將以下配置加入 ~/.cursor/mcp.json（或 設定 → MCP → 新增伺服器）：',
    'usage_claude_code' => 'Claude Code：執行以下命令：',
];
