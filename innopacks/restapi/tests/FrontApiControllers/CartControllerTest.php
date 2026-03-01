<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\FrontApiControllers;

use InnoShop\RestAPI\FrontApiControllers\CartController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Front CartController.
 * Tests method existence and basic structure without database dependencies.
 */
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
    public function test_controller_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass($this->controller);
        $parentClass = $reflection->getParentClass();

        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\RestAPI\FrontApiControllers\BaseController', $parentClass->getName());
    }

    #[Test]
    public function test_index_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    #[Test]
    public function test_index_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('index');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_store_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }

    #[Test]
    public function test_store_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('store');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'update'));
    }

    #[Test]
    public function test_update_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('update');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_select_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'select'));
    }

    #[Test]
    public function test_select_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('select');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_unselect_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'unselect'));
    }

    #[Test]
    public function test_unselect_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('unselect');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_select_all_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'selectAll'));
    }

    #[Test]
    public function test_select_all_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('selectAll');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_unselect_all_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'unselectAll'));
    }

    #[Test]
    public function test_unselect_all_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('unselectAll');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_destroy_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'destroy'));
    }

    #[Test]
    public function test_destroy_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('destroy');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_has_no_parameters(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('index');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function test_store_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('store');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('update');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('cart', $parameters[1]->getName());
    }

    #[Test]
    public function test_destroy_method_has_cart_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('destroy');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('cart', $parameters[0]->getName());
    }
}
