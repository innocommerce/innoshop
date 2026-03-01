<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\FrontApiControllers;

use InnoShop\RestAPI\FrontApiControllers\AuthController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Front AuthController.
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
        $this->assertEquals('InnoShop\RestAPI\FrontApiControllers\BaseController', $parentClass->getName());
    }

    #[Test]
    public function test_register_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'register'));
    }

    #[Test]
    public function test_register_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('register');

        $this->assertTrue($method->isPublic());
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
    public function test_send_sms_code_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'sendSmsCode'));
    }

    #[Test]
    public function test_send_sms_code_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('sendSmsCode');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_register_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('register');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
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
    public function test_send_sms_code_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('sendSmsCode');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_register_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('register');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
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
    public function test_send_sms_code_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('sendSmsCode');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }
}
