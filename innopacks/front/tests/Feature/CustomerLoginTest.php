<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Front\Controllers\Account\AccountController;
use InnoShop\Front\Controllers\Account\LoginController;
use InnoShop\Front\Controllers\Account\LogoutController;
use InnoShop\Front\Controllers\Account\RegisterController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CustomerLoginTest extends TestCase
{
    #[Test]
    public function test_login_controller_exists(): void
    {
        $controller = new LoginController;
        $this->assertInstanceOf(LoginController::class, $controller);
    }

    #[Test]
    public function test_register_controller_exists(): void
    {
        $controller = new RegisterController;
        $this->assertInstanceOf(RegisterController::class, $controller);
    }

    #[Test]
    public function test_account_controller_exists(): void
    {
        $controller = new AccountController;
        $this->assertInstanceOf(AccountController::class, $controller);
    }

    #[Test]
    public function test_logout_controller_exists(): void
    {
        $this->assertTrue(class_exists(LogoutController::class));
    }

    #[Test]
    public function test_login_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(LoginController::class, 'index'));
    }

    #[Test]
    public function test_login_controller_has_store_method(): void
    {
        $this->assertTrue(method_exists(LoginController::class, 'store'));
    }

    #[Test]
    public function test_register_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(RegisterController::class, 'index'));
    }

    #[Test]
    public function test_register_controller_has_store_method(): void
    {
        $this->assertTrue(method_exists(RegisterController::class, 'store'));
    }

    #[Test]
    public function test_account_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(AccountController::class, 'index'));
    }

    #[Test]
    public function test_login_controller_uses_send_sms_code_trait(): void
    {
        $reflection = new ReflectionClass(LoginController::class);
        $traits     = $reflection->getTraitNames();
        $this->assertContains('InnoShop\Front\Controllers\Account\SendSmsCodeTrait', $traits);
    }
}
