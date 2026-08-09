<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'mcp_service'       => 'Service MCP',
    'mcp_service_desc'  => 'Expose les données de la boutique (produits, commandes, stock, statistiques de vente) à des clients MCP comme Claude et Cursor. Lecture seule par défaut, jeton d\'administrateur requis.',
    'enable_mcp'        => 'Activer le point de terminaison MCP',
    'enable_mcp_write'  => 'Autoriser les opérations d\'écriture',
    'write_hint'        => 'Lorsqu\'elle est désactivée, l\'IA externe ne peut que consulter. Activée : les outils d\'écriture (création/modification de produits, changement de statut de commande, expédition) sont exposés.',
    'endpoint_url'      => 'URL du point de terminaison',
    'auth_header'       => 'Méthode d\'authentification',
    'token_hint'        => 'POSTez :url avec un compte administrateur pour obtenir un Token, transmis en Bearer.',
    'usage_title'       => 'Utilisation',
    'usage_cursor'      => 'Cursor : ajoutez la configuration suivante à ~/.cursor/mcp.json (ou Paramètres → MCP → Ajouter un serveur) :',
    'usage_claude_code' => 'Claude Code : exécutez la commande suivante :',
];
