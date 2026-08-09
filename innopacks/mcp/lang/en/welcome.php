<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'MCP Server',
    'subtitle'          => ':name exposes store data query and operation capabilities to external AI agents via MCP.',
    'active'            => 'Active',
    'tools_available'   => 'tools available',
    'nav_overview'      => 'Overview',
    'nav_architecture'  => 'Architecture',
    'nav_connect'       => 'Connect',
    'nav_auth'          => 'Auth',
    'nav_tools'         => 'All Tools',
    'overview_title'    => 'Overview',
    'overview_desc'     => 'This endpoint implements the <strong>Model Context Protocol</strong> (Streamable HTTP, JSON-RPC 2.0). AI tools like Claude Code, Cursor, and Cline can securely query store data through admin authentication. All tools inherit the admin permission system; read-only by default.',
    'write_mode_on'     => 'Write access: ON (external AI can create/modify data)',
    'write_mode_off'    => 'Write access: OFF (read-only)',
    'endpoint_label'    => 'Endpoint:',
    'arch_desc'         => 'MCP is the protocol adapter layer provided by innopacks/mcp. All tools are defined and maintained in innopacks/ai, with the two decoupled through the ToolInterface contract.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => 'AI Tools<br>(data query/ops)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'Protocol Adapter<br>(JSON-RPC + Auth)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'Tip',
    'arch_note'         => 'For in-panel AI chat, install the <strong>ShopBot plugin</strong> (AI Assistant). It uses the same innopacks/ai Tool system but interacts via Web UI without MCP.',
    'connect_title'     => 'Connect',
    'your_token'        => 'your-admin-token',
    'no_token'          => 'No API token found on this page. Please log in to the admin panel first, then navigate to System Settings → AI → MCP to view this page with your token.',
    'auth_title'        => 'Authentication',
    'auth_desc'         => 'Obtain a Bearer token with an admin account:',
    'auth_token_hint'   => 'After obtaining a token, you can view and manage it in the admin panel under',
    'system_settings'   => 'System Settings → Tools → AI',
    'auth_mcp_card'     => 'MCP service card.',
    'tools_title'       => 'All Tools',
    'tools_write_hint'  => 'Tools marked "write" are not callable right now. Enable "Allow write operations" under System Settings → Tools → AI to activate them.',
    'tool_write_badge'  => 'write',
    'tools_plugin_hint' => 'Plugins can register custom tools via the ai.tools hook — they automatically appear in this list and over MCP:',
    'cat_product'       => 'Products',
    'cat_order'         => 'Orders / Returns',
    'cat_customer'      => 'Customers',
    'cat_catalog'       => 'Categories / Brands',
    'cat_content'       => 'Content (CMS)',
    'cat_shipping'      => 'Shipping',
    'cat_analytics'     => 'Analytics / Reports',
    'cat_config'        => 'Config / System',
    'cat_other'         => 'Other',
];
