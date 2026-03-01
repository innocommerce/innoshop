<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Services;

use InnoShop\DevTools\Services\ScaffoldService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ScaffoldService.
 * Tests public methods: generatePlugin(), generateTheme(), generateControllerForPlugin(),
 * generateModelForPlugin(), generateServiceForPlugin(), generateRepositoryForPlugin(),
 * generateMigrationForPlugin()
 */
class ScaffoldServiceTest extends TestCase
{
    private ScaffoldService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScaffoldService;
    }

    #[Test]
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ScaffoldService::class, $this->service);
    }

    #[Test]
    public function test_generate_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generatePlugin'));
    }

    #[Test]
    public function test_generate_theme_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generateTheme'));
    }

    #[Test]
    public function test_generate_controller_for_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generateControllerForPlugin'));
    }

    #[Test]
    public function test_generate_model_for_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generateModelForPlugin'));
    }

    #[Test]
    public function test_generate_service_for_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generateServiceForPlugin'));
    }

    #[Test]
    public function test_generate_repository_for_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generateRepositoryForPlugin'));
    }

    #[Test]
    public function test_generate_migration_for_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'generateMigrationForPlugin'));
    }

    #[Test]
    public function test_generate_plugin_returns_bool(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generatePlugin');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    #[Test]
    public function test_generate_theme_returns_bool(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateTheme');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    #[Test]
    public function test_generate_plugin_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generatePlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('options', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
    }

    #[Test]
    public function test_generate_theme_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateTheme');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('name', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('options', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
    }

    #[Test]
    public function test_generate_controller_for_plugin_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateControllerForPlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('pluginPath', $parameters[0]->getName());
        $this->assertEquals('pluginName', $parameters[1]->getName());
        $this->assertEquals('controllerName', $parameters[2]->getName());
    }

    #[Test]
    public function test_generate_model_for_plugin_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateModelForPlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('pluginPath', $parameters[0]->getName());
        $this->assertEquals('pluginName', $parameters[1]->getName());
        $this->assertEquals('modelName', $parameters[2]->getName());
    }

    #[Test]
    public function test_generate_service_for_plugin_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateServiceForPlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('pluginPath', $parameters[0]->getName());
        $this->assertEquals('pluginName', $parameters[1]->getName());
        $this->assertEquals('serviceName', $parameters[2]->getName());
    }

    #[Test]
    public function test_generate_repository_for_plugin_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateRepositoryForPlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(4, $parameters);
        $this->assertEquals('pluginPath', $parameters[0]->getName());
        $this->assertEquals('pluginName', $parameters[1]->getName());
        $this->assertEquals('repositoryName', $parameters[2]->getName());
        $this->assertEquals('modelName', $parameters[3]->getName());
    }

    #[Test]
    public function test_generate_migration_for_plugin_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(ScaffoldService::class, 'generateMigrationForPlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(4, $parameters);
        $this->assertEquals('pluginPath', $parameters[0]->getName());
        $this->assertEquals('pluginName', $parameters[1]->getName());
        $this->assertEquals('migrationName', $parameters[2]->getName());
        $this->assertEquals('tableName', $parameters[3]->getName());
    }
}
