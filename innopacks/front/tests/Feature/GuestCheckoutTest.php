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
use InnoShop\Front\Controllers\CheckoutController;
use InnoShop\Front\Controllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GuestCheckoutTest extends TestCase
{
    #[Test]
    public function test_cart_controller_exists(): void
    {
        $controller = new CartController;
        $this->assertInstanceOf(CartController::class, $controller);
    }

    #[Test]
    public function test_checkout_controller_exists(): void
    {
        $controller = new CheckoutController;
        $this->assertInstanceOf(CheckoutController::class, $controller);
    }

    #[Test]
    public function test_product_controller_exists(): void
    {
        $controller = new ProductController;
        $this->assertInstanceOf(ProductController::class, $controller);
    }

    #[Test]
    public function test_cart_controller_has_store_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'store'));
    }

    #[Test]
    public function test_cart_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(CartController::class, 'index'));
    }

    #[Test]
    public function test_checkout_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(CheckoutController::class, 'index'));
    }

    #[Test]
    public function test_checkout_controller_has_confirm_method(): void
    {
        $this->assertTrue(method_exists(CheckoutController::class, 'confirm'));
    }

    #[Test]
    public function test_product_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(ProductController::class, 'index'));
    }

    #[Test]
    public function test_product_controller_has_show_method(): void
    {
        $this->assertTrue(method_exists(ProductController::class, 'show'));
    }
}
