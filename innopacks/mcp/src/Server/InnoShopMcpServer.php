<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Server;

use InnoShop\Aicore\Contracts\ToolInterface;
use InnoShop\Aicore\Services\ToolRegistry;
use InnoShop\Mcp\McpAccess;
use InnoShop\Mcp\ShopIdentity;
use InnoShop\Mcp\Tools\RegistryToolAdapter;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Contracts\Transport;
use Laravel\Mcp\Server\ServerContext;

#[Name('InnoShop')]
#[Version('1.0.0')]
#[Instructions('InnoShop MCP server. Exposes store management tools registered in innopacks/ai. Read-only by default; write tools appear only when the merchant enabled them in the panel.')]
class InnoShopMcpServer extends Server
{
    protected array $capabilities = [
        self::CAPABILITY_TOOLS => [
            'listChanged' => false,
        ],
    ];

    public int $defaultPaginationLength = 100;

    public int $maxPaginationLength = 200;

    public function __construct(Transport $transport, ToolRegistry $registry, private readonly ShopIdentity $shopIdentity)
    {
        parent::__construct($transport);

        $this->tools = array_map(
            fn (ToolInterface $tool) => new RegistryToolAdapter($tool),
            array_values(McpAccess::filterTools($registry->all()))
        );
    }

    public function createContext(): ServerContext
    {
        $context = parent::createContext();

        $context->instructions .= "\n\nServer: {$this->shopIdentity->host()}";

        return $context;
    }
}
