<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'MCP-Dienst',
    'mcp_service_desc'  => 'Stellt Shop-Daten (Produkte, Bestellungen, Bestand, Verkaufsstatistik) über das MCP-Protokoll für Clients wie Claude und Cursor bereit. Standardmäßig schreibgeschützt, Administrator-Token erforderlich.',
    'enable_mcp'        => 'MCP-Endpunkt aktivieren',
    'enable_mcp_write'  => 'Schreiboperationen erlauben',
    'write_hint'        => 'Aus: externe KI kann nur abfragen. Ein: Schreib-Tools (Produkte erstellen/ändern, Bestellstatus ändern, Versand) werden freigegeben.',
    'endpoint_url'      => 'Endpunkt-URL',
    'auth_header'       => 'Authentifizierungsmethode',
    'token_hint'        => 'POSTen Sie :url mit einem Administratorkonto, um ein Token zu erhalten, und übertragen Sie es als Bearer.',
    'usage_title'       => 'Verwendung',
    'usage_cursor'      => 'Cursor: Fügen Sie die folgende Konfiguration zu ~/.cursor/mcp.json hinzu (oder Einstellungen → MCP → Server hinzufügen):',
    'usage_claude_code' => 'Claude Code: Führen Sie den folgenden Befehl aus:',
];
