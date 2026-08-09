<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'Servizio MCP',
    'subtitle'          => ':name espone query e operazioni sui dati del negozio a agenti IA esterni tramite il protocollo MCP.',
    'active'            => 'Attivo',
    'tools_available'   => 'strumenti disponibili',
    'nav_overview'      => 'Panoramica',
    'nav_architecture'  => 'Architettura',
    'nav_connect'       => 'Guida alla connessione',
    'nav_auth'          => 'Autenticazione',
    'nav_tools'         => 'Tutti gli strumenti',
    'overview_title'    => 'Panoramica',
    'overview_desc'     => 'Questo endpoint implementa il <strong>Model Context Protocol</strong> (Streamable HTTP, JSON-RPC 2.0). Strumenti IA come Claude Code, Cursor o Cline possono consultare in modo sicuro i dati del negozio dopo l\'autenticazione di un amministratore. Tutti gli strumenti ereditano il sistema di permessi del pannello e sono di sola lettura per impostazione predefinita.',
    'write_mode_on'     => 'Scrittura: abilitata (l\'IA esterna può creare/modificare dati)',
    'write_mode_off'    => 'Scrittura: disabilitata (sola lettura)',
    'endpoint_label'    => 'Endpoint:',
    'arch_desc'         => 'MCP è il livello di adattamento del protocollo fornito da innopacks/mcp; tutti gli strumenti sono definiti e mantenuti in innopacks/ai. I due sono disaccoppiati tramite il contratto ToolInterface.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => 'strumenti IA<br>(query/operazione)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'Livello di adattamento<br>(JSON-RPC + autenticazione)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'Suggerimento',
    'arch_note'         => 'Per usare l\'IA direttamente nel pannello, installa il <strong>plugin ShopBot</strong> (assistente IA). Usa lo stesso sistema di strumenti di innopacks/ai ma interagisce tramite interfaccia web, senza protocollo MCP.',
    'connect_title'     => 'Guida alla connessione',
    'your_token'        => 'Il tuo token di amministratore',
    'no_token'          => 'Nessun token API rilevato in questa pagina. Accedi al pannello e apri questa pagina tramite Sistema → IA → MCP per ottenere il token precompilato.',
    'auth_title'        => 'Autenticazione',
    'auth_desc'         => 'Ottieni un Bearer Token con il tuo account amministratore:',
    'auth_token_hint'   => 'Dopo l\'ottenimento, puoi gestirlo in',
    'system_settings'   => 'Sistema → Strumenti → IA',
    'auth_mcp_card'     => 'la scheda «Servizio MCP».',
    'tools_title'       => 'Tutti gli strumenti',
    'tools_write_hint'  => 'Gli strumenti marcati come «scrittura» non sono chiamabili al momento. Abilita «Consenti operazioni di scrittura» in Sistema → Strumenti → IA per attivarli.',
    'tool_write_badge'  => 'scrittura',
    'tools_plugin_hint' => 'I plugin possono registrare strumenti personalizzati tramite l\'hook ai.tools; appaiono automaticamente in questa lista e su MCP:',
    'cat_product'       => 'Prodotti',
    'cat_order'         => 'Ordini / Resi',
    'cat_customer'      => 'Clienti',
    'cat_catalog'       => 'Categorie / Brand',
    'cat_content'       => 'Contenuti (CMS)',
    'cat_shipping'      => 'Spedizioni',
    'cat_analytics'     => 'Statistiche / Report',
    'cat_config'        => 'Configurazione / Sistema',
    'cat_other'         => 'Altro',
];
