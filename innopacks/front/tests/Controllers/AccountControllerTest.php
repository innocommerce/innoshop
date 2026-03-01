<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\Account\AccountController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
    public function test_has_index_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    #[Test]
    public function test_index_method_is_public(): void
    {
        $reflection = new ReflectionClass(AccountController::class);
        $method     = $reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(AccountController::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }
}
