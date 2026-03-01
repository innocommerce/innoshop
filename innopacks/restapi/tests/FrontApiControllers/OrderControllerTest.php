<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\FrontApiControllers;

use InnoShop\RestAPI\FrontApiControllers\OrderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Front OrderController.
 * Tests method existence and basic structure without database dependencies.
 */
class OrderControllerTest extends TestCase
{
    private OrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new OrderController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(OrderController::class, $this->controller);
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
    public function test_show_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'show'));
    }

    #[Test]
    public function test_show_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('show');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_number_show_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'numberShow'));
    }

    #[Test]
    public function test_number_show_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('numberShow');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_pay_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'pay'));
    }

    #[Test]
    public function test_pay_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('pay');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_cancel_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'cancel'));
    }

    #[Test]
    public function test_cancel_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('cancel');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_complete_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'complete'));
    }

    #[Test]
    public function test_complete_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('complete');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_reorder_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'reorder'));
    }

    #[Test]
    public function test_reorder_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('reorder');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('index');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_show_method_has_order_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('show');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('order', $parameters[0]->getName());
    }

    #[Test]
    public function test_number_show_method_has_number_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('numberShow');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('number', $parameters[0]->getName());
    }

    #[Test]
    public function test_pay_method_has_number_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('pay');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('number', $parameters[0]->getName());
    }

    #[Test]
    public function test_cancel_method_has_number_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('cancel');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('number', $parameters[0]->getName());
    }

    #[Test]
    public function test_complete_method_has_number_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('complete');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('number', $parameters[0]->getName());
    }

    #[Test]
    public function test_reorder_method_has_number_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('reorder');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('number', $parameters[0]->getName());
    }
}
