<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\Feature;

use InnoShop\RestAPI\FrontApiControllers\AccountController;
use InnoShop\RestAPI\FrontApiControllers\AuthController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Customer Authentication Controllers.
 * Tests controller class existence and structure without database dependencies.
 */
class CustomerAuthenticationTest extends TestCase
{
    #[Test]
    public function test_auth_controller_exists(): void
    {
        $this->assertTrue(class_exists(AuthController::class));
    }

    #[Test]
    public function test_account_controller_exists(): void
    {
        $this->assertTrue(class_exists(AccountController::class));
    }

    #[Test]
    public function test_auth_controller_can_be_instantiated(): void
    {
        $controller = new AuthController;
        $this->assertInstanceOf(AuthController::class, $controller);
    }

    #[Test]
    public function test_account_controller_can_be_instantiated(): void
    {
        $controller = new AccountController;
        $this->assertInstanceOf(AccountController::class, $controller);
    }

    #[Test]
    public function test_auth_controller_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(AuthController::class);
        $parentClass = $reflection->getParentClass();

        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\RestAPI\FrontApiControllers\BaseController', $parentClass->getName());
    }

    #[Test]
    public function test_account_controller_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(AccountController::class);
        $parentClass = $reflection->getParentClass();

        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\RestAPI\FrontApiControllers\BaseController', $parentClass->getName());
    }

    #[Test]
    public function test_auth_controller_has_register_method(): void
    {
        $this->assertTrue(method_exists(AuthController::class, 'register'));
    }

    #[Test]
    public function test_auth_controller_has_login_method(): void
    {
        $this->assertTrue(method_exists(AuthController::class, 'login'));
    }

    #[Test]
    public function test_auth_controller_has_send_sms_code_method(): void
    {
        $this->assertTrue(method_exists(AuthController::class, 'sendSmsCode'));
    }

    #[Test]
    public function test_account_controller_has_me_method(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'me'));
    }

    #[Test]
    public function test_account_controller_has_update_profile_method(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'updateProfile'));
    }

    #[Test]
    public function test_account_controller_has_update_password_method(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'updatePassword'));
    }

    #[Test]
    public function test_account_controller_has_set_password_method(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'setPassword'));
    }

    #[Test]
    public function test_register_method_is_public(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        $method     = $reflection->getMethod('register');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_login_method_is_public(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        $method     = $reflection->getMethod('login');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_me_method_is_public(): void
    {
        $reflection = new ReflectionClass(AccountController::class);
        $method     = $reflection->getMethod('me');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_auth_controller_in_correct_namespace(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        $this->assertEquals('InnoShop\RestAPI\FrontApiControllers', $reflection->getNamespaceName());
    }

    #[Test]
    public function test_account_controller_in_correct_namespace(): void
    {
        $reflection = new ReflectionClass(AccountController::class);
        $this->assertEquals('InnoShop\RestAPI\FrontApiControllers', $reflection->getNamespaceName());
    }
}
