<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\MCP\Server;

use InnoShop\AI\Contracts\ToolInterface;
use InnoShop\AI\Services\ToolRegistry;
use InnoShop\MCP\Tools\RegistryToolAdapter;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Contracts\Transport;

#[Name('InnoShop')]
#[Version('1.0.0')]
#[Instructions('InnoShop MCP server. Exposes store management tools registered in innopacks/ai. All tools are read-only adapters over existing repositories.')]
class InnoShopMcpServer extends Server
{
    protected array $capabilities = [
        self::CAPABILITY_TOOLS => [
            'listChanged' => false,
        ],
    ];

    public function __construct(Transport $transport, ToolRegistry $registry)
    {
        parent::__construct($transport);

        $this->tools = array_map(
            fn (ToolInterface $tool) => new RegistryToolAdapter($tool),
            array_values($registry->all())
        );
    }
}
