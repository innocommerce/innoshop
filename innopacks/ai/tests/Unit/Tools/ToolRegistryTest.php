<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Tests\Unit\Tools;

use InnoShop\AI\Services\ToolRegistry;
use InnoShop\AI\Tools\BaseTool;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FakeProductTool extends BaseTool
{
    public function name(): string
    {
        return 'product_list';
    }

    public function description(): string
    {
        return 'List products';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => ['limit' => ['type' => 'integer']],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products.index';
    }

    public function execute(array $arguments): mixed
    {
        return ['items' => []];
    }
}

class FakePublicTool extends BaseTool
{
    public function name(): string
    {
        return 'ping';
    }

    public function description(): string
    {
        return 'Ping';
    }

    public function execute(array $arguments): mixed
    {
        return 'pong';
    }
}

class ToolRegistryTest extends TestCase
{
    private ToolRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ToolRegistry;
    }

    #[Test]
    public function test_register_and_get_tool(): void
    {
        $tool = new FakeProductTool;
        $this->registry->register($tool);

        $this->assertTrue($this->registry->has('product_list'));
        $this->assertSame($tool, $this->registry->get('product_list'));
    }

    #[Test]
    public function test_get_unknown_tool_returns_null(): void
    {
        $this->assertNull($this->registry->get('not_exists'));
        $this->assertFalse($this->registry->has('not_exists'));
    }

    #[Test]
    public function test_duplicate_registration_throws(): void
    {
        $this->registry->register(new FakeProductTool);

        $this->expectException(LogicException::class);
        $this->registry->register(new FakeProductTool);
    }

    #[Test]
    public function test_all_returns_registered_tools(): void
    {
        $this->registry->register(new FakeProductTool);
        $this->registry->register(new FakePublicTool);

        $all = $this->registry->all();
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('product_list', $all);
        $this->assertArrayHasKey('ping', $all);
    }

    #[Test]
    public function test_permitted_filters_by_permission(): void
    {
        $this->registry->register(new FakeProductTool);
        $this->registry->register(new FakePublicTool);

        $denyAll = $this->registry->permitted(fn () => false);
        $this->assertArrayNotHasKey('product_list', $denyAll);
        $this->assertArrayHasKey('ping', $denyAll);

        $allowAll = $this->registry->permitted(fn () => true);
        $this->assertCount(2, $allowAll);
    }

    #[Test]
    public function test_schemas_returns_serializable_descriptors(): void
    {
        $this->registry->register(new FakeProductTool);
        $this->registry->register(new FakePublicTool);

        $schemas = $this->registry->schemas();
        $this->assertCount(2, $schemas);
        $this->assertSame('product_list', $schemas[0]['name']);
        $this->assertSame('List products', $schemas[0]['description']);
        $this->assertSame('object', $schemas[0]['inputSchema']['type']);
    }

    #[Test]
    public function test_schemas_respects_permission_filter(): void
    {
        $this->registry->register(new FakeProductTool);
        $this->registry->register(new FakePublicTool);

        $schemas = $this->registry->schemas(fn (string $permission) => $permission !== 'products.index');
        $this->assertCount(1, $schemas);
        $this->assertSame('ping', $schemas[0]['name']);
    }

    #[Test]
    public function test_execute_delegates_to_tool(): void
    {
        $tool = new FakePublicTool;
        $this->assertSame('pong', $tool->execute([]));
    }
}
