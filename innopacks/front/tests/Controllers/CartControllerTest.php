<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\CartController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CartControllerTest extends TestCase
{
    private CartController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CartController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(CartController::class, $this->controller);
    }

    #[Test]
    public function test_has_index_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    #[Test]
    public function test_has_mini_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'mini'));
    }

    #[Test]
    public function test_has_store_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }

    #[Test]
    public function test_has_update_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'update'));
    }

    #[Test]
    public function test_has_select_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'select'));
    }

    #[Test]
    public function test_has_unselect_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'unselect'));
    }

    #[Test]
    public function test_has_destroy_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'destroy'));
    }

    #[Test]
    public function test_index_method_is_public(): void
    {
        $reflection = new ReflectionClass(CartController::class);
        $method     = $reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_store_method_is_public(): void
    {
        $reflection = new ReflectionClass(CartController::class);
        $method     = $reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_method_is_public(): void
    {
        $reflection = new ReflectionClass(CartController::class);
        $method     = $reflection->getMethod('update');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_destroy_method_is_public(): void
    {
        $reflection = new ReflectionClass(CartController::class);
        $method     = $reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(CartController::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }
}
