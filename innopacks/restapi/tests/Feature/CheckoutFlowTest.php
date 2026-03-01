<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\Feature;

use InnoShop\RestAPI\FrontApiControllers\CartController;
use InnoShop\RestAPI\FrontApiControllers\CheckoutController;
use InnoShop\RestAPI\FrontApiControllers\OrderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Checkout Flow Controllers.
 * Tests controller class existence and structure without database dependencies.
 */
class CheckoutFlowTest extends TestCase
{
    #[Test]
    public function test_cart_controller_exists(): void
    {
        $this->assertTrue(class_exists(CartController::class));
    }

    #[Test]
    public function test_checkout_controller_exists(): void
    {
        $this->assertTrue(class_exists(CheckoutController::class));
    }

    #[Test]
    public function test_order_controller_exists(): void
    {
        $this->assertTrue(class_exists(OrderController::class));
    }

    #[Test]
    public function test_cart_controller_can_be_instantiated(): void
    {
        $controller = new CartController;
        $this->assertInstanceOf(CartController::class, $controller);
    }

    #[Test]
    public function test_checkout_controller_can_be_instantiated(): void
    {
        $controller = new CheckoutController;
        $this->assertInstanceOf(CheckoutController::class, $controller);
    }

    #[Test]
    public function test_order_controller_can_be_instantiated(): void
    {
        $controller = new OrderController;
        $this->assertInstanceOf(OrderController::class, $controller);
    }

    #[Test]
    public function test_all_checkout_controllers_extend_base_controller(): void
    {
        $controllers = [
            CartController::class,
            CheckoutController::class,
            OrderController::class,
        ];

        foreach ($controllers as $controllerClass) {
            $reflection  = new ReflectionClass($controllerClass);
            $parentClass = $reflection->getParentClass();

            $this->assertNotFalse($parentClass, "$controllerClass should have a parent class");
            $this->assertEquals(
                'InnoShop\RestAPI\FrontApiControllers\BaseController',
                $parentClass->getName(),
                "$controllerClass should extend BaseController"
            );
        }
    }

    #[Test]
    public function test_cart_controller_has_required_methods(): void
    {
        $methods = ['index', 'store', 'update', 'select', 'unselect', 'selectAll', 'unselectAll', 'destroy'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(CartController::class, $method),
                "CartController should have $method method"
            );
        }
    }

    #[Test]
    public function test_checkout_controller_has_required_methods(): void
    {
        $methods = ['index', 'update', 'confirm', 'billingMethods', 'quickConfirm'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(CheckoutController::class, $method),
                "CheckoutController should have $method method"
            );
        }
    }

    #[Test]
    public function test_order_controller_has_required_methods(): void
    {
        $methods = ['index', 'show', 'numberShow', 'pay', 'cancel', 'complete', 'reorder'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(OrderController::class, $method),
                "OrderController should have $method method"
            );
        }
    }

    #[Test]
    public function test_cart_controller_index_is_public(): void
    {
        $reflection = new ReflectionClass(CartController::class);
        $method     = $reflection->getMethod('index');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_checkout_controller_confirm_is_public(): void
    {
        $reflection = new ReflectionClass(CheckoutController::class);
        $method     = $reflection->getMethod('confirm');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_order_controller_show_is_public(): void
    {
        $reflection = new ReflectionClass(OrderController::class);
        $method     = $reflection->getMethod('show');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_all_checkout_controllers_in_correct_namespace(): void
    {
        $controllers = [
            CartController::class,
            CheckoutController::class,
            OrderController::class,
        ];

        foreach ($controllers as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);
            $this->assertEquals(
                'InnoShop\RestAPI\FrontApiControllers',
                $reflection->getNamespaceName(),
                "$controllerClass should be in FrontApiControllers namespace"
            );
        }
    }

    #[Test]
    public function test_checkout_flow_controllers_have_mixed_return_types(): void
    {
        $controllerMethods = [
            [CartController::class, 'index'],
            [CheckoutController::class, 'index'],
            [OrderController::class, 'index'],
        ];

        foreach ($controllerMethods as [$controllerClass, $methodName]) {
            $reflection = new ReflectionClass($controllerClass);
            $method     = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();

            $this->assertNotNull($returnType, "$controllerClass::$methodName should have a return type");
            $this->assertEquals('mixed', $returnType->getName(), "$controllerClass::$methodName should return mixed");
        }
    }
}
