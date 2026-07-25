<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'MCP 服务',
    'mcp_service_desc'  => '将店铺数据（商品、订单、库存、销售统计）通过 MCP 协议开放给 Claude、Cursor 等客户端。只读，需管理员 Token。',
    'enable_mcp'        => '启用 MCP 端点',
    'endpoint_url'      => '端点地址',
    'auth_header'       => '认证方式',
    'token_hint'        => '使用管理员账号 POST :url 获取 Token，以 Bearer 方式携带。',
    'usage_title'       => '使用方式',
    'usage_cursor'      => 'Cursor：将以下配置加入 ~/.cursor/mcp.json（或 设置 → MCP → 添加服务器）：',
    'usage_claude_code' => 'Claude Code：执行以下命令：',
];
