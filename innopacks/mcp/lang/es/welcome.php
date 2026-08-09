<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'Servicio MCP',
    'subtitle'          => ':name expone consultas y operaciones de los datos de la tienda a agentes de IA externos mediante el protocolo MCP.',
    'active'            => 'Activo',
    'tools_available'   => 'herramientas disponibles',
    'nav_overview'      => 'Resumen',
    'nav_architecture'  => 'Arquitectura',
    'nav_connect'       => 'Guía de conexión',
    'nav_auth'          => 'Autenticación',
    'nav_tools'         => 'Todas las herramientas',
    'overview_title'    => 'Resumen',
    'overview_desc'     => 'Este punto de conexión implementa el <strong>Model Context Protocol</strong> (Streamable HTTP, JSON-RPC 2.0). Herramientas de IA como Claude Code, Cursor o Cline pueden consultar con seguridad los datos de la tienda tras la autenticación de un administrador. Todas las herramientas heredan el sistema de permisos del panel y son de solo lectura por defecto.',
    'write_mode_on'     => 'Escritura: activada (la IA externa puede crear/modificar datos)',
    'write_mode_off'    => 'Escritura: desactivada (solo lectura)',
    'endpoint_label'    => 'Punto de conexión:',
    'arch_desc'         => 'MCP es la capa de adaptación del protocolo proporcionada por innopacks/mcp; todas las herramientas se definen y mantienen en innopacks/ai. Ambos se desacoplan mediante el contrato ToolInterface.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => 'herramientas IA<br>(consulta/operación)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'Capa de adaptación<br>(JSON-RPC + autenticación)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'Consejo',
    'arch_note'         => 'Para usar IA dentro del panel, instale el <strong>plugin ShopBot</strong> (asistente de IA). Se basa en el mismo sistema de tools de innopacks/ai pero interactúa vía interfaz web, sin protocolo MCP.',
    'connect_title'     => 'Guía de conexión',
    'your_token'        => 'Su token de administrador',
    'no_token'          => 'No se detectó ningún token de API en esta página. Inicie sesión en el panel y acceda a esta página vía Sistema → IA → MCP para obtener el token rellenado automáticamente.',
    'auth_title'        => 'Autenticación',
    'auth_desc'         => 'Obtenga un Bearer Token con su cuenta de administrador:',
    'auth_token_hint'   => 'Tras obtener el token, puede gestionarlo en',
    'system_settings'   => 'Sistema → Herramientas → IA',
    'auth_mcp_card'     => 'la tarjeta «Servicio MCP».',
    'tools_title'       => 'Todas las herramientas',
    'tools_write_hint'  => 'Las herramientas marcadas como «escritura» no son invocables ahora. Active «Permitir operaciones de escritura» en Sistema → Herramientas → IA para habilitarlas.',
    'tool_write_badge'  => 'escritura',
    'tools_plugin_hint' => 'Los plugins pueden registrar herramientas personalizadas mediante el hook ai.tools; aparecen automáticamente en esta lista y sobre MCP:',
    'cat_product'       => 'Productos',
    'cat_order'         => 'Pedidos / Devoluciones',
    'cat_customer'      => 'Clientes',
    'cat_catalog'       => 'Categorías / Marcas',
    'cat_content'       => 'Contenido (CMS)',
    'cat_shipping'      => 'Envíos',
    'cat_analytics'     => 'Estadísticas / Informes',
    'cat_config'        => 'Configuración / Sistema',
    'cat_other'         => 'Otros',
];
