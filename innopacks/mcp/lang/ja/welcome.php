<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'MCP サービス',
    'subtitle'          => ':name は MCP プロトコルを通じて、店舗データの照会・操作機能を外部の AI Agent に公開します。',
    'active'            => '稼働中',
    'tools_available'   => '個のツールが利用可能',
    'nav_overview'      => '概要',
    'nav_architecture'  => 'アーキテクチャ',
    'nav_connect'       => '接続ガイド',
    'nav_auth'          => '認証',
    'nav_tools'         => 'すべてのツール',
    'overview_title'    => '概要',
    'overview_desc'     => 'このエンドポイントは <strong>Model Context Protocol</strong>（Streamable HTTP、JSON-RPC 2.0）を実装しています。Claude Code、Cursor、Cline などの AI ツールは、管理者認証を経て店舗データを安全に照会できます。すべてのツールはバックオフィスの権限体系を継承し、既定では読み取り専用です。',
    'write_mode_on'     => '書き込み操作：有効（外部 AI がデータを作成・変更可能）',
    'write_mode_off'    => '書き込み操作：無効（現在は読み取り専用）',
    'endpoint_label'    => 'エンドポイント：',
    'arch_desc'         => 'MCP は innopacks/mcp が提供するプロトコル適合層です。すべてのツール（Tool）は innopacks/ai で定義・管理され、両者は ToolInterface 契約によって疎結合になっています。',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => '個の AI Tool<br>（データ照会/操作）',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'プロトコル適合層<br>（JSON-RPC + 認証）',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'ヒント',
    'arch_note'         => 'バックオフィス内で AI 会話を利用する場合は、<strong>ShopBot プラグイン</strong>（AI アシスタント）をインストールしてください。ShopBot も innopacks/ai の Tool 体系に基づきますが、MCP プロトコルを介さず Web UI で直接対話します。',
    'connect_title'     => '接続ガイド',
    'your_token'        => 'あなたの管理者トークン',
    'no_token'          => '現在のページで API トークンが検出されませんでした。バックオフィスにログイン後、システム設定 → AI → MCP からこのページに入ると、自動入力されたトークンを取得できます。',
    'auth_title'        => '認証',
    'auth_desc'         => '管理者アカウントで Bearer Token を取得します：',
    'auth_token_hint'   => 'トークン取得後はバックオフィスの',
    'system_settings'   => 'システム設定 → ツール → AI',
    'auth_mcp_card'     => 'MCP サービスカードで確認・管理できます。',
    'tools_title'       => 'すべてのツール',
    'tools_write_hint'  => '「書込」マークのツールは現在呼び出せません。バックオフィスの システム設定 → ツール → AI で「書き込み操作を許可」を有効にすると利用できます。',
    'tool_write_badge'  => '書込',
    'tools_plugin_hint' => 'プラグインは ai.tools フックでカスタムツールを登録でき、このリストと MCP プロトコルに自動で反映されます：',
    'cat_product'       => '商品',
    'cat_order'         => '注文 / 返品',
    'cat_customer'      => '顧客',
    'cat_catalog'       => 'カテゴリ / ブランド',
    'cat_content'       => 'コンテンツ（CMS）',
    'cat_shipping'      => '物流',
    'cat_analytics'     => '統計 / レポート',
    'cat_config'        => '設定 / システム',
    'cat_other'         => 'その他',
];
