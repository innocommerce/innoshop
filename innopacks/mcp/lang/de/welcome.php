<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'MCP-Dienst',
    'subtitle'          => ':name stellt externe KI-Agenten über das MCP-Protokoll Abfrage- und Verwaltungsfunktionen der Shop-Daten zur Verfügung.',
    'active'            => 'Aktiv',
    'tools_available'   => 'Tools verfügbar',
    'nav_overview'      => 'Übersicht',
    'nav_architecture'  => 'Architektur',
    'nav_connect'       => 'Verbindungsleitfaden',
    'nav_auth'          => 'Authentifizierung',
    'nav_tools'         => 'Alle Tools',
    'overview_title'    => 'Übersicht',
    'overview_desc'     => 'Dieser Endpunkt implementiert das <strong>Model Context Protocol</strong> (Streamable HTTP, JSON-RPC 2.0). KI-Tools wie Claude Code, Cursor oder Cline können nach Administrator-Authentifizierung sicher auf Shop-Daten zugreifen. Alle Tools erben das Berechtigungssystem des Panels und sind standardmäßig schreibgeschützt.',
    'write_mode_on'     => 'Schreibzugriff: aktiviert (externe KI kann Daten erstellen/ändern)',
    'write_mode_off'    => 'Schreibzugriff: deaktiviert (aktuell schreibgeschützt)',
    'endpoint_label'    => 'Endpunkt:',
    'arch_desc'         => 'MCP ist die Protokoll-Adaptionsschicht von innopacks/mcp; alle Tools sind in innopacks/ai definiert und gepflegt. Beide sind über den ToolInterface-Vertrag entkoppelt.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => 'KI-Tools<br>(Abfrage/Operation)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'Protokoll-Adaptionsschicht<br>(JSON-RPC + Authentifizierung)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'Hinweis',
    'arch_note'         => 'Für KI-Dialoge direkt im Panel installieren Sie das <strong>ShopBot-Plugin</strong> (KI-Assistent). Es basiert auf demselben Tool-System von innopacks/ai, interagiert aber über eine Web-UI ohne MCP-Protokoll.',
    'connect_title'     => 'Verbindungsleitfaden',
    'your_token'        => 'Ihr Administrator-Token',
    'no_token'          => 'Auf dieser Seite wurde kein API-Token erkannt. Melden Sie sich im Panel an und rufen Sie diese Seite über System → KI → MCP auf, um das automatisch ausgefüllte Token zu erhalten.',
    'auth_title'        => 'Authentifizierung',
    'auth_desc'         => 'Holen Sie sich einen Bearer Token mit Ihrem Administratorkonto:',
    'auth_token_hint'   => 'Nach der Beschaffung kann der Token verwaltet werden unter',
    'system_settings'   => 'System → Werkzeuge → KI',
    'auth_mcp_card'     => 'die Karte „MCP-Dienst“.',
    'tools_title'       => 'Alle Tools',
    'tools_write_hint'  => 'Mit „Schreiben“ markierte Tools sind derzeit nicht aufrufbar. Aktivieren Sie „Schreiboperationen erlauben“ unter System → Werkzeuge → KI, um sie zu nutzen.',
    'tool_write_badge'  => 'Schreiben',
    'tools_plugin_hint' => 'Plugins können über den ai.tools-Hook benutzerdefinierte Tools registrieren; diese erscheinen automatisch in dieser Liste und über MCP:',
    'cat_product'       => 'Produkte',
    'cat_order'         => 'Bestellungen / Retouren',
    'cat_customer'      => 'Kunden',
    'cat_catalog'       => 'Kategorien / Marken',
    'cat_content'       => 'Inhalt (CMS)',
    'cat_shipping'      => 'Versand',
    'cat_analytics'     => 'Statistiken / Berichte',
    'cat_config'        => 'Konfiguration / System',
    'cat_other'         => 'Sonstiges',
];
