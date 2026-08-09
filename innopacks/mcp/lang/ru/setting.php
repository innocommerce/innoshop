<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'Служба MCP',
    'mcp_service_desc'  => 'Открывает данные магазина (товары, заказы, остатки, статистика продаж) для MCP-клиентов, таких как Claude и Cursor. По умолчанию только чтение, требуется токен администратора.',
    'enable_mcp'        => 'Включить конечную точку MCP',
    'enable_mcp_write'  => 'Разрешить операции записи',
    'write_hint'        => 'Выключено: внешний ИИ может только запрашивать. Включено: доступны инструменты записи (создание/изменение товаров, изменение статуса заказов, отгрузка).',
    'endpoint_url'      => 'URL конечной точки',
    'auth_header'       => 'Метод аутентификации',
    'token_hint'        => 'Выполните POST на :url с учётной записью администратора, чтобы получить токен, и передавайте его как Bearer.',
    'usage_title'       => 'Использование',
    'usage_cursor'      => 'Cursor: добавьте следующую конфигурацию в ~/.cursor/mcp.json (или Настройки → MCP → Добавить сервер):',
    'usage_claude_code' => 'Claude Code: выполните следующую команду:',
];
