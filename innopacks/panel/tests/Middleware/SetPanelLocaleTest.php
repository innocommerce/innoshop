<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Middleware;

use InnoShop\Panel\Middleware\SetPanelLocale;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for SetPanelLocale middleware.
 * Verifies the middleware structure and method signatures.
 */
class SetPanelLocaleTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(SetPanelLocale::class);
    }

    #[Test]
    public function test_middleware_exists(): void
    {
        $this->assertTrue(class_exists(SetPanelLocale::class));
    }

    #[Test]
    public function test_handle_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('handle'));
        $method = $this->reflection->getMethod('handle');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_handle_method_accepts_request_and_next(): void
    {
        $method     = $this->reflection->getMethod('handle');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('next', $parameters[1]->getName());
    }

    #[Test]
    public function test_handle_method_returns_mixed(): void
    {
        $method     = $this->reflection->getMethod('handle');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }

    #[Test]
    public function test_request_parameter_type_is_request(): void
    {
        $method       = $this->reflection->getMethod('handle');
        $parameters   = $method->getParameters();
        $requestParam = $parameters[0];
        $type         = $requestParam->getType();
        $this->assertNotNull($type);
        $this->assertEquals('Illuminate\Http\Request', $type->getName());
    }

    #[Test]
    public function test_next_parameter_type_is_closure(): void
    {
        $method     = $this->reflection->getMethod('handle');
        $parameters = $method->getParameters();
        $nextParam  = $parameters[1];
        $type       = $nextParam->getType();
        $this->assertNotNull($type);
        $this->assertEquals('Closure', $type->getName());
    }
}
