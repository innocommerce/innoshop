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
 * Unit tests for API Authentication Middleware.
 * Tests middleware class existence and structure without database dependencies.
 */
class ApiAuthTest extends TestCase
{
    #[Test]
    public function test_set_api_currency_middleware_exists(): void
    {
        $middleware = new SetAPICurrency;
        $this->assertInstanceOf(SetAPICurrency::class, $middleware);
    }

    #[Test]
    public function test_set_api_locale_middleware_exists(): void
    {
        $middleware = new SetAPILocale;
        $this->assertInstanceOf(SetAPILocale::class, $middleware);
    }

    #[Test]
    public function test_set_api_currency_has_handle_method(): void
    {
        $this->assertTrue(method_exists(SetAPICurrency::class, 'handle'));
    }

    #[Test]
    public function test_set_api_locale_has_handle_method(): void
    {
        $this->assertTrue(method_exists(SetAPILocale::class, 'handle'));
    }

    #[Test]
    public function test_set_api_currency_handle_method_is_public(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $method     = $reflection->getMethod('handle');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_set_api_locale_handle_method_is_public(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $method     = $reflection->getMethod('handle');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_set_api_currency_handle_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('next', $parameters[1]->getName());
    }

    #[Test]
    public function test_set_api_locale_handle_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('next', $parameters[1]->getName());
    }

    #[Test]
    public function test_set_api_currency_handle_returns_mixed(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $method     = $reflection->getMethod('handle');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }

    #[Test]
    public function test_set_api_locale_handle_returns_mixed(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $method     = $reflection->getMethod('handle');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }

    #[Test]
    public function test_set_api_currency_request_parameter_type(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $requestParam = $parameters[0];
        $type         = $requestParam->getType();

        $this->assertNotNull($type);
        $this->assertEquals('Illuminate\Http\Request', $type->getName());
    }

    #[Test]
    public function test_set_api_locale_request_parameter_type(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $requestParam = $parameters[0];
        $type         = $requestParam->getType();

        $this->assertNotNull($type);
        $this->assertEquals('Illuminate\Http\Request', $type->getName());
    }

    #[Test]
    public function test_set_api_currency_next_parameter_type(): void
    {
        $reflection = new ReflectionClass(SetAPICurrency::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $nextParam = $parameters[1];
        $type      = $nextParam->getType();

        $this->assertNotNull($type);
        $this->assertEquals('Closure', $type->getName());
    }

    #[Test]
    public function test_set_api_locale_next_parameter_type(): void
    {
        $reflection = new ReflectionClass(SetAPILocale::class);
        $method     = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $nextParam = $parameters[1];
        $type      = $nextParam->getType();

        $this->assertNotNull($type);
        $this->assertEquals('Closure', $type->getName());
    }
}
