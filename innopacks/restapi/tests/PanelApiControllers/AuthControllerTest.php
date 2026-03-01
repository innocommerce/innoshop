<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\PanelApiControllers;

use InnoShop\RestAPI\PanelApiControllers\AuthController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Panel AuthController.
 * Tests method existence and basic structure without database dependencies.
 */
class AuthControllerTest extends TestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(AuthController::class, $this->controller);
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass($this->controller);
        $parentClass = $reflection->getParentClass();

        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\RestAPI\PanelApiControllers\BaseController', $parentClass->getName());
    }

    #[Test]
    public function test_login_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'login'));
    }

    #[Test]
    public function test_login_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('login');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_login_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('login');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_admin_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'admin'));
    }

    #[Test]
    public function test_admin_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('admin');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_admin_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('admin');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_login_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('login');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }

    #[Test]
    public function test_admin_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('admin');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }
}
