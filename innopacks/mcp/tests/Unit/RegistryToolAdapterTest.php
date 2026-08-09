<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp\Tests\Unit;

use InnoShop\Aicore\Contracts\ToolInterface;
use InnoShop\Mcp\Tools\RegistryToolAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FakeEchoTool implements ToolInterface
{
    public function name(): string
    {
        return 'echo_tool';
    }

    public function description(): string
    {
        return 'Echo back the input';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => ['message' => ['type' => 'string']],
        ];
    }

    public function requiredPermission(): ?string
    {
        return null;
    }

    public function isWrite(): bool
    {
        return false;
    }

    public function execute(array $arguments): mixed
    {
        return $arguments;
    }
}

class RegistryToolAdapterTest extends TestCase
{
    #[Test]
    public function test_adapter_exposes_tool_metadata(): void
    {
        $adapter = new RegistryToolAdapter(new FakeEchoTool);

        $this->assertSame('echo_tool', $adapter->name());
        $this->assertSame('Echo back the input', $adapter->description());
    }

    #[Test]
    public function test_to_array_uses_tool_json_schema(): void
    {
        $adapter = new RegistryToolAdapter(new FakeEchoTool);
        $array   = $adapter->toArray();

        $this->assertSame('echo_tool', $array['name']);
        $this->assertSame('Echo back the input', $array['description']);
        $this->assertSame('object', $array['inputSchema']['type']);
        $this->assertArrayHasKey('message', $array['inputSchema']['properties']);
    }

    #[Test]
    public function test_to_array_falls_back_to_empty_object_schema(): void
    {
        $tool = new class extends FakeEchoTool
        {
            public function inputSchema(): array
            {
                return [];
            }
        };

        $array = (new RegistryToolAdapter($tool))->toArray();
        $this->assertSame('object', $array['inputSchema']['type']);
        $this->assertInstanceOf(\stdClass::class, $array['inputSchema']['properties']);
    }
}
