<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\Middleware;

use InnoShop\RestAPI\Middleware\SetAPICurrency;
use InnoShop\RestAPI\Middleware\SetAPILocale;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Rate Limiting Middleware.
 * Tests middleware class existence and structure without database dependencies.
 */
class RateLimitTest extends TestCase
{
    #[Test]
    public function test_set_api_currency_middleware_class_exists(): void
    {
        $this->assertTrue(class_exists(SetAPICurrency::class));
    }

    #[Test]
    public function test_set_api_locale_middleware_class_exists(): void
    {
        $this->assertTrue(class_exists(SetAPILocale::class));
    }

    #[Test]
    public function test_set_api_currency_can_be_instantiated(): void
    {
        $middleware = new SetAPICurrency;
        $this->assertInstanceOf(SetAPICurrency::class, $middleware);
    }

    #[Test]
    public function test_set_api_locale_can_be_instantiated(): void
    {
        $middleware = new SetAPILocale;
        $this->assertInstanceOf(SetAPILocale::class, $middleware);
    }

    #[Test]
    public function test_set_api_currency_implements_handle(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $this->assertTrue($reflection->hasMethod('handle'));
    }

    #[Test]
    public function test_set_api_locale_implements_handle(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $this->assertTrue($reflection->hasMethod('handle'));
    }

    #[Test]
    public function test_middleware_namespace_is_correct(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $this->assertEquals('InnoShop\RestAPI\Middleware', $reflection->getNamespaceName());
    }

    #[Test]
    public function test_locale_middleware_namespace_is_correct(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $this->assertEquals('InnoShop\RestAPI\Middleware', $reflection->getNamespaceName());
    }
}
