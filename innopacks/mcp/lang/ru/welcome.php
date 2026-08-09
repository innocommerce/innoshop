<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'Служба MCP',
    'subtitle'          => ':name открывает запросы и операции с данными магазина для внешних ИИ-агентов через протокол MCP.',
    'active'            => 'Активна',
    'tools_available'   => 'инструментов доступно',
    'nav_overview'      => 'Обзор',
    'nav_architecture'  => 'Архитектура',
    'nav_connect'       => 'Руководство по подключению',
    'nav_auth'          => 'Аутентификация',
    'nav_tools'         => 'Все инструменты',
    'overview_title'    => 'Обзор',
    'overview_desc'     => 'Эта конечная точка реализует <strong>Model Context Protocol</strong> (Streamable HTTP, JSON-RPC 2.0). ИИ-инструменты, такие как Claude Code, Cursor или Cline, могут безопасно запрашивать данные магазина после аутентификации администратора. Все инструменты наследуют систему прав панели и по умолчанию доступны только для чтения.',
    'write_mode_on'     => 'Запись: включена (внешний ИИ может создавать/изменять данные)',
    'write_mode_off'    => 'Запись: выключена (сейчас только чтение)',
    'endpoint_label'    => 'Конечная точка:',
    'arch_desc'         => 'MCP — это слой адаптации протокола от innopacks/mcp; все инструменты определяются и поддерживаются в innopacks/ai. Они слабо связаны через контракт ToolInterface.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => 'ИИ-инструментов<br>(запрос/операция)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'Слой адаптации протокола<br>(JSON-RPC + аутентификация)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'Подсказка',
    'arch_note'         => 'Чтобы использовать ИИ прямо в панели, установите <strong>плагин ShopBot</strong> (ИИ-ассистент). Он основан на той же системе инструментов innopacks/ai, но взаимодействует через веб-интерфейс без протокола MCP.',
    'connect_title'     => 'Руководство по подключению',
    'your_token'        => 'Ваш токен администратора',
    'no_token'          => 'На этой странице не обнаружен API-токен. Войдите в панель и перейдите на эту страницу через Система → ИИ → MCP, чтобы получить автозаполненный токен.',
    'auth_title'        => 'Аутентификация',
    'auth_desc'         => 'Получите Bearer Token с помощью учётной записи администратора:',
    'auth_token_hint'   => 'После получения токена им можно управлять в',
    'system_settings'   => 'Система → Инструменты → ИИ',
    'auth_mcp_card'     => 'карточке «Служба MCP».',
    'tools_title'       => 'Все инструменты',
    'tools_write_hint'  => 'Инструменты с пометкой «запись» сейчас недоступны для вызова. Включите «Разрешить операции записи» в разделе Система → Инструменты → ИИ.',
    'tool_write_badge'  => 'запись',
    'tools_plugin_hint' => 'Плагины могут регистрировать собственные инструменты через хук ai.tools; они автоматически появляются в этом списке и в MCP:',
    'cat_product'       => 'Товары',
    'cat_order'         => 'Заказы / Возвраты',
    'cat_customer'      => 'Клиенты',
    'cat_catalog'       => 'Категории / Бренды',
    'cat_content'       => 'Контент (CMS)',
    'cat_shipping'      => 'Доставка',
    'cat_analytics'     => 'Статистика / Отчёты',
    'cat_config'        => 'Настройки / Система',
    'cat_other'         => 'Другое',
];
