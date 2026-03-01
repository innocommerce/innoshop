<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\FrontApiControllers;

use InnoShop\RestAPI\FrontApiControllers\AccountController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Front AccountController.
 * Tests method existence and basic structure without database dependencies.
 */
class AccountControllerTest extends TestCase
{
    private AccountController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AccountController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(AccountController::class, $this->controller);
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
    public function test_me_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'me'));
    }

    #[Test]
    public function test_me_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('me');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_profile_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'updateProfile'));
    }

    #[Test]
    public function test_update_profile_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateProfile');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_password_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'updatePassword'));
    }

    #[Test]
    public function test_update_password_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updatePassword');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_set_password_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'setPassword'));
    }

    #[Test]
    public function test_set_password_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('setPassword');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_me_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('me');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_profile_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateProfile');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_password_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updatePassword');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_set_password_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('setPassword');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_me_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('me');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }

    #[Test]
    public function test_update_profile_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateProfile');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }
}
