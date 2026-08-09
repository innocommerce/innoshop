<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'Servicio MCP',
    'mcp_service_desc'  => 'Expone los datos de la tienda (productos, pedidos, stock, estadísticas de ventas) a clientes MCP como Claude y Cursor. Solo lectura por defecto, requiere token de administrador.',
    'enable_mcp'        => 'Activar punto de conexión MCP',
    'enable_mcp_write'  => 'Permitir operaciones de escritura',
    'write_hint'        => 'Desactivado: la IA externa solo puede consultar. Activado: se exponen herramientas de escritura (crear/modificar productos, cambiar estado de pedidos, envíos).',
    'endpoint_url'      => 'URL del punto de conexión',
    'auth_header'       => 'Método de autenticación',
    'token_hint'        => 'Haga POST a :url con una cuenta de administrador para obtener un Token y envíelo como Bearer.',
    'usage_title'       => 'Uso',
    'usage_cursor'      => 'Cursor: añada la siguiente configuración a ~/.cursor/mcp.json (o Ajustes → MCP → Añadir servidor):',
    'usage_claude_code' => 'Claude Code: ejecute el siguiente comando:',
];
