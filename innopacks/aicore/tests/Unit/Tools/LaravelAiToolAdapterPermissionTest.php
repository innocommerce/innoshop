<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Unit\Tools;

use InnoShop\Aicore\Tools\BaseTool;
use InnoShop\Aicore\Tools\LaravelAiToolAdapter;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionedWriteTool extends BaseTool
{
    protected bool $write = true;

    public function __construct(private readonly string $canaryPath) {}

    public function name(): string
    {
        return 'canary_setting_update';
    }

    public function description(): string
    {
        return 'Canary write tool';
    }

    public function requiredPermission(): ?string
    {
        return 'settings_update';
    }

    public function execute(array $arguments): mixed
    {
        file_put_contents($this->canaryPath, json_encode($arguments));

        return ['status' => 'updated'];
    }
}

class LaravelAiToolAdapterPermissionTest extends TestCase
{
    #[Test]
    public function test_handle_refuses_permissioned_tool_without_admin(): void
    {
        $canary = tempnam(sys_get_temp_dir(), 'innopoc_');
        unlink($canary);

        $adapter = new LaravelAiToolAdapter(new PermissionedWriteTool($canary));
        $result  = (string) $adapter->handle(new Request(['meta_title' => 'CANARY']));

        $this->assertStringContainsString('unauthorized', $result);
        $this->assertFileDoesNotExist($canary);
    }
}
