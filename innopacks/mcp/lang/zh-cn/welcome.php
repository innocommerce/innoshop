<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'MCP 服务',
    'subtitle'          => '将 AI 助手连接到 :name 店铺数据，实时查询。',
    'active'            => '运行中',
    'protocol_label'    => 'MCP',
    'protocol_desc'     => '模型上下文协议',
    'transport'         => 'Streamable HTTP &middot; JSON-RPC 2.0',
    'nav_overview'      => '概述',
    'nav_connect'       => '接入',
    'nav_auth'          => '认证',
    'nav_tools'         => '工具',
    'overview_title'    => '概述',
    'overview_desc'     => '此端点使用 <strong>Model Context Protocol</strong>（Streamable HTTP、JSON-RPC 2.0）。Cursor、Claude Code 等 AI 工具可通过管理员认证通道安全地查询您的商品、订单、库存和销售数据。',
    'endpoint_label'    => '端点地址：',
    'connect_title'     => '接入',
    'cursor_title'      => 'Cursor',
    'cursor_desc'       => '将以下配置加入 <code>~/.cursor/mcp.json</code>，或在 <em>设置 → MCP → 添加服务器</em> 中添加：',
    'claude_code_title' => 'Claude Code',
    'claude_code_desc'  => '在终端执行：',
    'auth_title'        => '认证',
    'auth_desc'         => '使用管理员账号获取 Bearer Token：',
    'tools_title'       => '工具列表',
    'th_tool'           => '工具',
    'th_permission'     => '权限',
    'th_description'    => '说明',
];
