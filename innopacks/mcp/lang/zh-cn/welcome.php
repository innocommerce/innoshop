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
    'subtitle'          => ':name 通过 MCP 协议向外部 AI Agent 暴露店铺数据查询与操作能力。',
    'active'            => '运行中',
    'tools_available'   => '个工具可用',
    'nav_overview'      => '概述',
    'nav_architecture'  => '架构',
    'nav_connect'       => '接入指南',
    'nav_auth'          => '认证',
    'nav_tools'         => '所有工具',
    'overview_title'    => '概述',
    'overview_desc'     => '此端点实现 <strong>Model Context Protocol</strong>（Streamable HTTP，JSON-RPC 2.0）。Claude Code、Cursor、Cline 等 AI 工具可通过管理员认证安全地查询店铺数据。所有工具均继承后台权限体系，默认只读。',
    'write_mode_on'     => '写操作：已开启（外部 AI 可创建/修改数据）',
    'write_mode_off'    => '写操作：未开启（当前只读）',
    'endpoint_label'    => '端点地址：',
    'arch_desc'         => 'MCP 是 innopacks/mcp 提供的协议适配层，所有工具（Tool）定义和维护在 innopacks/ai，两者通过 ToolInterface 契约解耦。',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => '个 AI Tool<br>（数据查询/操作）',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => '协议适配层<br>（JSON-RPC + 认证）',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => '提示',
    'arch_note'         => '如需在后台面板内使用 AI 对话，请安装 <strong>ShopBot 插件</strong>（AI 助理），它同样基于 innopacks/ai 的 Tool 体系，但通过 Web UI 直接交互，无需 MCP 协议。',
    'connect_title'     => '接入指南',
    'your_token'        => '你的管理员令牌',
    'no_token'          => '当前页面未检测到 API Token。请先登录后台，然后通过 系统设置 → AI → MCP 进入此页面以获取自动填充的令牌。',
    'auth_title'        => '认证',
    'auth_desc'         => '使用管理员账号获取 Bearer Token：',
    'auth_token_hint'   => '获取 Token 后可在后台',
    'system_settings'   => '系统设置 → 工具 → AI',
    'auth_mcp_card'     => 'MCP 服务卡片中查看和管理。',
    'tools_title'       => '所有工具',
    'tools_write_hint'  => '带「写」标记的工具当前未开放调用，需在后台 系统设置 → 工具 → AI 开启「开放写操作」后生效。',
    'tool_write_badge'  => '写',
    'tools_plugin_hint' => '插件可通过 ai.tools Hook 注册自定义工具，自动出现在此列表及 MCP 协议中：',
    'cat_product'       => '商品',
    'cat_order'         => '订单 / 售后',
    'cat_customer'      => '客户',
    'cat_catalog'       => '分类 / 品牌',
    'cat_content'       => '内容 (CMS)',
    'cat_shipping'      => '物流',
    'cat_analytics'     => '统计 / 报表',
    'cat_config'        => '配置 / 系统',
    'cat_other'         => '其他',
];
