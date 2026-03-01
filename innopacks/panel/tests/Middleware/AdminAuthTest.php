<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Middleware;

use InnoShop\Panel\Middleware\AdminAuthenticate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for AdminAuthenticate middleware.
 * Verifies the middleware structure and method signatures.
 */
class AdminAuthTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(AdminAuthenticate::class);
    }

    #[Test]
    public function test_middleware_exists(): void
    {
        $this->assertTrue(class_exists(AdminAuthenticate::class));
    }

    #[Test]
    public function test_middleware_extends_laravel_authenticate(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('Illuminate\Auth\Middleware\Authenticate'));
    }

    #[Test]
    public function test_handle_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('handle'));
        $method = $this->reflection->getMethod('handle');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_redirect_to_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('redirectTo'));
        $method = $this->reflection->getMethod('redirectTo');
        $this->assertTrue($method->isProtected());
    }

    #[Test]
    public function test_handle_method_accepts_request_next_and_guards(): void
    {
        $method     = $this->reflection->getMethod('handle');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(2, count($parameters));
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
    public function test_redirect_to_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('redirectTo');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }
}
