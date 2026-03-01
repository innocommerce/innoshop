<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Services;

use InnoShop\DevTools\Services\PackageService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PackageService.
 * Tests public methods: createPackage(), cleanup()
 */
class PackageServiceTest extends TestCase
{
    private PackageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PackageService;
    }

    #[Test]
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PackageService::class, $this->service);
    }

    #[Test]
    public function test_create_package_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'createPackage'));
    }

    #[Test]
    public function test_cleanup_method_exists(): void
    {
        $this->assertTrue(method_exists($this->service, 'cleanup'));
    }

    #[Test]
    public function test_create_package_returns_string(): void
    {
        $reflection = new \ReflectionMethod(PackageService::class, 'createPackage');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    #[Test]
    public function test_cleanup_returns_void(): void
    {
        $reflection = new \ReflectionMethod(PackageService::class, 'cleanup');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    #[Test]
    public function test_create_package_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(PackageService::class, 'createPackage');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('sourcePath', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
        $this->assertEquals('plugin', $parameters[1]->getDefaultValue());
        $this->assertEquals('metadata', $parameters[2]->getName());
        $this->assertTrue($parameters[2]->isDefaultValueAvailable());
    }

    #[Test]
    public function test_cleanup_accepts_string_parameter(): void
    {
        $reflection = new \ReflectionMethod(PackageService::class, 'cleanup');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('zipPath', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    #[Test]
    public function test_service_has_only_two_public_methods(): void
    {
        $reflection    = new \ReflectionClass(PackageService::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Filter out inherited methods and constructor
        $ownPublicMethods = array_filter($publicMethods, function ($method) {
            return $method->getDeclaringClass()->getName() === PackageService::class
                && $method->getName() !== '__construct';
        });

        $methodNames = array_map(fn ($m) => $m->getName(), $ownPublicMethods);

        $this->assertContains('createPackage', $methodNames);
        $this->assertContains('cleanup', $methodNames);
        $this->assertCount(2, $ownPublicMethods);
    }
}
