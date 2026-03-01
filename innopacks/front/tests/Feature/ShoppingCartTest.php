<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Front\Controllers\CartController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ShoppingCartTest extends TestCase
{
    #[Test]
    public function test_cart_controller_exists(): void
    {
        $controller = new CartController;
        $this->assertInstanceOf(CartController::class, $controller);
    }

    #[Test]
    public function test_cart_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'index'));
    }

    #[Test]
    public function test_cart_controller_has_store_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'store'));
    }

    #[Test]
    public function test_cart_controller_has_update_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'update'));
    }

    #[Test]
    public function test_cart_controller_has_destroy_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'destroy'));
    }

    #[Test]
    public function test_cart_controller_has_mini_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'mini'));
    }

    #[Test]
    public function test_cart_controller_has_select_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'select'));
    }

    #[Test]
    public function test_cart_controller_has_unselect_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'unselect'));
    }

    #[Test]
    public function test_cart_controller_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(CartController::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }
}
