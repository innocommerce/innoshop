<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'Servizio MCP',
    'mcp_service_desc'  => 'Espone i dati del negozio (prodotti, ordini, scorte, statistiche di vendita) a clienti MCP come Claude e Cursor. Sola lettura per impostazione predefinita, richiede un token di amministratore.',
    'enable_mcp'        => 'Abilita endpoint MCP',
    'enable_mcp_write'  => 'Consenti operazioni di scrittura',
    'write_hint'        => 'Disattivato: l\'IA esterna può solo consultare. Attivato: vengono esposti strumenti di scrittura (creare/modificare prodotti, cambiare stato ordini, spedizioni).',
    'endpoint_url'      => 'URL endpoint',
    'auth_header'       => 'Metodo di autenticazione',
    'token_hint'        => 'Esegui POST su :url con un account amministratore per ottenere un Token e invialo come Bearer.',
    'usage_title'       => 'Utilizzo',
    'usage_cursor'      => 'Cursor: aggiungi la seguente configurazione a ~/.cursor/mcp.json (o Impostazioni → MCP → Aggiungi server):',
    'usage_claude_code' => 'Claude Code: esegui il seguente comando:',
];
