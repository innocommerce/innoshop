<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\Account\LoginController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoginControllerTest extends TestCase
{
    private LoginController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new LoginController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(LoginController::class, $this->controller);
    }

    #[Test]
    public function test_has_index_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    #[Test]
    public function test_has_store_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }

    #[Test]
    public function test_has_send_sms_code_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'sendSmsCode'));
    }

    #[Test]
    public function test_index_method_is_public(): void
    {
        $reflection = new ReflectionClass(LoginController::class);
        $method     = $reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_store_method_is_public(): void
    {
        $reflection = new ReflectionClass(LoginController::class);
        $method     = $reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_send_sms_code_method_is_public(): void
    {
        $reflection = new ReflectionClass(LoginController::class);
        $method     = $reflection->getMethod('sendSmsCode');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(LoginController::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_uses_send_sms_code_trait(): void
    {
        $reflection = new ReflectionClass(LoginController::class);
        $traits     = $reflection->getTraitNames();
        $this->assertContains('InnoShop\Front\Controllers\Account\SendSmsCodeTrait', $traits);
    }
}
