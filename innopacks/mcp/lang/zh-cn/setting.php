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
    'mcp_service_desc'  => '将店铺数据（商品、订单、库存、销售统计）通过 MCP 协议开放给 Claude、Cursor 等客户端。默认只读，需管理员 Token。',
    'enable_mcp'        => '启用 MCP 端点',
    'enable_mcp_write'  => '开放写操作',
    'write_hint'        => '关闭时外部 AI 只能查询；开启后才允许创建/修改商品、订单改状态、发货等写工具。',
    'endpoint_url'      => '端点地址',
    'auth_header'       => '认证方式',
    'token_hint'        => '前往 <a href=":url">账号设置 → API Token</a> 复制你的 Token，以 Bearer 方式携带。',
    'usage_title'       => '使用方式',
    'usage_cursor'      => 'Cursor：将以下配置加入 ~/.cursor/mcp.json（或 设置 → MCP → 添加服务器）：',
    'usage_claude_code' => 'Claude Code：执行以下命令：',
];
