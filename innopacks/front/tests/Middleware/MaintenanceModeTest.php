<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Middleware;

use InnoShop\Front\Middleware\MaintenanceMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MaintenanceModeTest extends TestCase
{
    #[Test]
    public function test_middleware_exists(): void
    {
        $this->assertTrue(class_exists(MaintenanceMode::class));
    }

    #[Test]
    public function test_has_handle_method(): void
    {
        $this->assertTrue(method_exists(MaintenanceMode::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_is_public(): void
    {
        $reflection = new ReflectionClass(MaintenanceMode::class);
        $method     = $reflection->getMethod('handle');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_handle_method_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass(MaintenanceMode::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('next', $parameters[1]->getName());
    }

    #[Test]
    public function test_middleware_can_be_instantiated(): void
    {
        $middleware = new MaintenanceMode;
        $this->assertInstanceOf(MaintenanceMode::class, $middleware);
    }
}
