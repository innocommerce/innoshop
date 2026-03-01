<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Services;

use InnoShop\DevTools\Services\MarketplaceService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MarketplaceService.
 * Tests public methods: upload()
 */
class MarketplaceServiceTest extends TestCase
{
    #[Test]
    public function test_service_class_exists(): void
    {
        $this->assertTrue(class_exists(MarketplaceService::class));
    }

    #[Test]
    public function test_upload_method_exists(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'upload'));
    }

    #[Test]
    public function test_upload_returns_array(): void
    {
        $reflection = new \ReflectionMethod(MarketplaceService::class, 'upload');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_upload_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionMethod(MarketplaceService::class, 'upload');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('zipPath', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertEquals('string', $parameters[1]->getType()->getName());
        $this->assertEquals('metadata', $parameters[2]->getName());
        $this->assertTrue($parameters[2]->isDefaultValueAvailable());
    }

    #[Test]
    public function test_service_has_constructor(): void
    {
        $reflection  = new \ReflectionClass(MarketplaceService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
    }

    #[Test]
    public function test_service_has_only_one_public_method_besides_constructor(): void
    {
        $reflection    = new \ReflectionClass(MarketplaceService::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Filter out inherited methods and constructor
        $ownPublicMethods = array_filter($publicMethods, function ($method) {
            return $method->getDeclaringClass()->getName() === MarketplaceService::class
                && $method->getName() !== '__construct';
        });

        $methodNames = array_map(fn ($m) => $m->getName(), $ownPublicMethods);

        $this->assertContains('upload', $methodNames);
        $this->assertCount(1, $ownPublicMethods);
    }

    #[Test]
    public function test_upload_method_is_public(): void
    {
        $reflection = new \ReflectionMethod(MarketplaceService::class, 'upload');
        $this->assertTrue($reflection->isPublic());
    }
}
