<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'MCP サービス',
    'mcp_service_desc'  => '店舗データ（商品、注文、在庫、売上統計）を MCP プロトコルで Claude、Cursor などのクライアントに公開します。既定は読み取り専用で、管理者トークンが必要です。',
    'enable_mcp'        => 'MCP エンドポイントを有効化',
    'enable_mcp_write'  => '書き込み操作を許可',
    'write_hint'        => 'オフの間は外部 AI は照会のみ可能です。オンにすると商品の作成/変更、注文ステータス変更、出荷などの書き込みツールが公開されます。',
    'endpoint_url'      => 'エンドポイント URL',
    'auth_header'       => '認証方式',
    'token_hint'        => '管理者アカウントで :url に POST して Token を取得し、Bearer として送信します。',
    'usage_title'       => '使用方法',
    'usage_cursor'      => 'Cursor：次の設定を ~/.cursor/mcp.json に追加します（または 設定 → MCP → サーバーを追加）：',
    'usage_claude_code' => 'Claude Code：次のコマンドを実行します：',
];
