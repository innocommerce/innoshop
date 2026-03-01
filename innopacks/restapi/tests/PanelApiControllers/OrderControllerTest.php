<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\PanelApiControllers;

use InnoShop\RestAPI\PanelApiControllers\OrderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Panel OrderController.
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
        $this->assertEquals('InnoShop\RestAPI\PanelApiControllers\BaseController', $parentClass->getName());
    }

    #[Test]
    public function test_update_note_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'updateNote'));
    }

    #[Test]
    public function test_update_note_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateNote');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_note_method_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateNote');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('order', $parameters[0]->getName());
        $this->assertEquals('request', $parameters[1]->getName());
    }

    #[Test]
    public function test_update_note_method_returns_mixed(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateNote');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('mixed', $returnType->getName());
    }

    #[Test]
    public function test_update_note_order_parameter_type(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateNote');
        $parameters = $method->getParameters();

        $orderParam = $parameters[0];
        $type       = $orderParam->getType();

        $this->assertNotNull($type);
        $this->assertEquals('InnoShop\Common\Models\Order', $type->getName());
    }

    #[Test]
    public function test_update_note_request_parameter_type(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('updateNote');
        $parameters = $method->getParameters();

        $requestParam = $parameters[1];
        $type         = $requestParam->getType();

        $this->assertNotNull($type);
        $this->assertEquals('Illuminate\Http\Request', $type->getName());
    }
}
