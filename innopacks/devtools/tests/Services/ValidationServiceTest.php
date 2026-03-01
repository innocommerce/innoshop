<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Services;

use InnoShop\DevTools\Services\ValidationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ValidationService.
 * Tests public methods: validatePlugin(), validateTheme()
 */
class ValidationServiceTest extends TestCase
{
    private ValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ValidationService;
    }

    #[Test]
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ValidationService::class, $this->service);
    }

    #[Test]
    public function test_validate_plugin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'validatePlugin'));
    }

    #[Test]
    public function test_validate_theme_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'validateTheme'));
    }

    #[Test]
    public function test_validate_plugin_returns_array(): void
    {
        $reflection = new \ReflectionMethod(ValidationService::class, 'validatePlugin');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_validate_theme_returns_array(): void
    {
        $reflection = new \ReflectionMethod(ValidationService::class, 'validateTheme');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_validate_plugin_accepts_string_parameter(): void
    {
        $reflection = new \ReflectionMethod(ValidationService::class, 'validatePlugin');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('pluginPath', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    #[Test]
    public function test_validate_theme_accepts_string_parameter(): void
    {
        $reflection = new \ReflectionMethod(ValidationService::class, 'validateTheme');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('themePath', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    #[Test]
    public function test_service_has_only_two_public_methods(): void
    {
        $reflection    = new \ReflectionClass(ValidationService::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Filter out inherited methods
        $ownPublicMethods = array_filter($publicMethods, function ($method) {
            return $method->getDeclaringClass()->getName() === ValidationService::class;
        });

        $methodNames = array_map(fn ($m) => $m->getName(), $ownPublicMethods);

        $this->assertContains('validatePlugin', $methodNames);
        $this->assertContains('validateTheme', $methodNames);
        $this->assertCount(2, $ownPublicMethods);
    }
}
