<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'Serviço MCP',
    'mcp_service_desc'  => 'Expõe dados da loja (produtos, pedidos, estoque, estatísticas de vendas) a clientes MCP como Claude e Cursor. Somente leitura por padrão, requer token de administrador.',
    'enable_mcp'        => 'Ativar endpoint MCP',
    'enable_mcp_write'  => 'Permitir operações de gravação',
    'write_hint'        => 'Desativado: a IA externa só pode consultar. Ativado: ferramentas de escrita (criar/alterar produtos, mudar status de pedidos, envios) são expostas.',
    'endpoint_url'      => 'URL do endpoint',
    'auth_header'       => 'Método de autenticação',
    'token_hint'        => 'Faça POST em :url com uma conta de administrador para obter um Token e envie como Bearer.',
    'usage_title'       => 'Uso',
    'usage_cursor'      => 'Cursor: adicione a seguinte configuração a ~/.cursor/mcp.json (ou Configurações → MCP → Adicionar servidor):',
    'usage_claude_code' => 'Claude Code: execute o seguinte comando:',
];
