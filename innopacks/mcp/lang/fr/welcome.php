<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'             => 'Service MCP',
    'subtitle'          => ':name expose des capacités de consultation et d\'opération des données de boutique à des agents IA externes via le protocole MCP.',
    'active'            => 'En cours',
    'tools_available'   => 'outils disponibles',
    'nav_overview'      => 'Aperçu',
    'nav_architecture'  => 'Architecture',
    'nav_connect'       => 'Guide de connexion',
    'nav_auth'          => 'Authentification',
    'nav_tools'         => 'Tous les outils',
    'overview_title'    => 'Aperçu',
    'overview_desc'     => 'Ce point de terminaison implémente le <strong>Model Context Protocol</strong> (Streamable HTTP, JSON-RPC 2.0). Des outils IA comme Claude Code, Cursor ou Cline peuvent consulter en toute sécurité les données de la boutique après authentification d\'un administrateur. Tous les outils héritent du système de permissions du panneau et sont en lecture seule par défaut.',
    'write_mode_on'     => 'Écriture : activée (l\'IA externe peut créer/modifier des données)',
    'write_mode_off'    => 'Écriture : désactivée (lecture seule)',
    'endpoint_label'    => 'Point de terminaison :',
    'arch_desc'         => 'MCP est la couche d\'adaptation du protocole fournie par innopacks/mcp ; tous les outils sont définis et maintenus dans innopacks/ai. Les deux sont découplés via le contrat ToolInterface.',
    'arch_ai'           => 'innopacks/ai',
    'arch_tools'        => 'outils IA<br>(requête/opération)',
    'arch_mcp'          => 'innopacks/mcp',
    'arch_protocol'     => 'Couche d\'adaptation<br>(JSON-RPC + authentification)',
    'arch_bridge'       => 'ToolInterface',
    'arch_note_badge'   => 'Astuce',
    'arch_note'         => 'Pour utiliser l\'IA directement dans le panneau, installez le <strong>plugin ShopBot</strong> (assistant IA). Il repose sur le même système de tools d\'innopacks/ai mais dialogue via une interface Web, sans protocole MCP.',
    'connect_title'     => 'Guide de connexion',
    'your_token'        => 'Votre jeton d\'administrateur',
    'no_token'          => 'Aucun jeton API détecté sur cette page. Connectez-vous au panneau puis accédez à cette page via Système → IA → MCP pour obtenir le jeton prérempli.',
    'auth_title'        => 'Authentification',
    'auth_desc'         => 'Obtenez un Bearer Token avec votre compte administrateur :',
    'auth_token_hint'   => 'Après l\'obtention du jeton, il peut être géré dans',
    'system_settings'   => 'Système → Outils → IA',
    'auth_mcp_card'     => 'la carte « Service MCP ».',
    'tools_title'       => 'Tous les outils',
    'tools_write_hint'  => 'Les outils marqués « écriture » ne sont pas appelables actuellement. Activez « Autoriser les opérations d\'écriture » dans Système → Outils → IA pour les activer.',
    'tool_write_badge'  => 'écriture',
    'tools_plugin_hint' => 'Les plugins peuvent enregistrer des outils personnalisés via le hook ai.tools ; ils apparaissent automatiquement dans cette liste et sur MCP :',
    'cat_product'       => 'Produits',
    'cat_order'         => 'Commandes / Retours',
    'cat_customer'      => 'Clients',
    'cat_catalog'       => 'Catégories / Marques',
    'cat_content'       => 'Contenu (CMS)',
    'cat_shipping'      => 'Expédition',
    'cat_analytics'     => 'Statistiques / Rapports',
    'cat_config'        => 'Configuration / Système',
    'cat_other'         => 'Autres',
];
