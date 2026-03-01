<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\CheckoutController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CheckoutControllerTest extends TestCase
{
    private CheckoutController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CheckoutController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(CheckoutController::class, $this->controller);
    }

    #[Test]
    public function test_has_index_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    #[Test]
    public function test_has_update_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'update'));
    }

    #[Test]
    public function test_has_confirm_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'confirm'));
    }

    #[Test]
    public function test_index_method_is_public(): void
    {
        $reflection = new ReflectionClass(CheckoutController::class);
        $method     = $reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_method_is_public(): void
    {
        $reflection = new ReflectionClass(CheckoutController::class);
        $method     = $reflection->getMethod('update');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_confirm_method_is_public(): void
    {
        $reflection = new ReflectionClass(CheckoutController::class);
        $method     = $reflection->getMethod('confirm');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(CheckoutController::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }
}
