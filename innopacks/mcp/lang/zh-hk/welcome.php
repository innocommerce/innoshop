<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'MCP 服務',
    'subtitle'          => ':name 透過 MCP 協議向外部 AI Agent 暴露店鋪數據查詢與操作能力。',
    'active'            => '運行中',
    'tools_available'   => '個工具可用',
    'nav_overview'      => '概述',
    'nav_architecture'  => '架構',
    'nav_connect'       => '接入指南',
    'nav_auth'          => '認證',
    'nav_tools'         => '所有工具',
    'overview_title'    => '概述',
    'overview_desc'     => '此端點實現 <strong>Model Context Protocol</strong>（Streamable HTTP，JSON-RPC 2.0）。Claude Code、Cursor、Cline 等 AI 工具可通過管理員認證安全地查詢店鋪數據。所有工具均繼承後台權限體系，默認只讀。',
    'write_mode_on'     => '寫操作：已開啟（外部 AI 可建立/修改數據）',
    'write_mode_off'    => '寫操作：未開啟（當前只讀）',
    'endpoint_label'    => '端點地址：',
    'arch_desc'         => 'MCP 是 innopacks/mcp 提供的協議適配層，所有工具（Tool）定義和維護在 innopacks/ai，兩者通過 ToolInterface 契約解耦。',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => '個 AI Tool<br>（數據查詢/操作）',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => '協議適配層<br>（JSON-RPC + 認證）',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => '提示',
    'arch_note'         => '如需在後台面板內使用 AI 對話，請安裝 <strong>ShopBot 插件</strong>（AI 助理），它同樣基於 innopacks/ai 的 Tool 體系，但通過 Web UI 直接交互，無需 MCP 協議。',
    'connect_title'     => '接入指南',
    'your_token'        => '你的管理員令牌',
    'no_token'          => '當前頁面未檢測到 API Token。請先登錄後台，然後透過 系統設置 → AI → MCP 進入此頁面以獲取自動填充的令牌。',
    'auth_title'        => '認證',
    'auth_desc'         => '使用管理員帳號獲取 Bearer Token：',
    'auth_token_hint'   => '獲取 Token 後可在後台',
    'system_settings'   => '系統設置 → 工具 → AI',
    'auth_mcp_card'     => 'MCP 服務卡片中查看和管理。',
    'tools_title'       => '所有工具',
    'tools_write_hint'  => '帶「寫」標記的工具當前未開放調用，需在後台 系統設置 → 工具 → AI 開啟「開放寫操作」後生效。',
    'tool_write_badge'  => '寫',
    'tools_plugin_hint' => '插件可透過 ai.tools Hook 註冊自定義工具，自動出現在此列表及 MCP 協議中：',
    'cat_product'       => '商品',
    'cat_order'         => '訂單 / 售後',
    'cat_customer'      => '客戶',
    'cat_catalog'       => '分類 / 品牌',
    'cat_content'       => '內容 (CMS)',
    'cat_shipping'      => '物流',
    'cat_analytics'     => '統計 / 報表',
    'cat_config'        => '配置 / 系統',
    'cat_other'         => '其他',
];
