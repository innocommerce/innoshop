<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Middleware;

use InnoShop\Front\Middleware\CustomerAuthentication;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CustomerAuthTest extends TestCase
{
    #[Test]
    public function test_middleware_exists(): void
    {
        $this->assertTrue(class_exists(CustomerAuthentication::class));
    }

    #[Test]
    public function test_has_handle_method(): void
    {
        $this->assertTrue(method_exists(CustomerAuthentication::class, 'handle'));
    }

    #[Test]
    public function test_has_redirect_to_method(): void
    {
        $reflection = new ReflectionClass(CustomerAuthentication::class);
        $this->assertTrue($reflection->hasMethod('redirectTo'));
    }

    #[Test]
    public function test_handle_method_is_public(): void
    {
        $reflection = new ReflectionClass(CustomerAuthentication::class);
        $method     = $reflection->getMethod('handle');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_redirect_to_method_is_protected(): void
    {
        $reflection = new ReflectionClass(CustomerAuthentication::class);
        $method     = $reflection->getMethod('redirectTo');
        $this->assertTrue($method->isProtected());
    }

    #[Test]
    public function test_extends_authenticate_middleware(): void
    {
        $reflection  = new ReflectionClass(CustomerAuthentication::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('Illuminate\Auth\Middleware\Authenticate', $parentClass->getName());
    }
}
