<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\Feature;

use InnoShop\RestAPI\PanelApiControllers\AuthController;
use InnoShop\RestAPI\PanelApiControllers\CustomerController;
use InnoShop\RestAPI\PanelApiControllers\DashboardController;
use InnoShop\RestAPI\PanelApiControllers\OrderController;
use InnoShop\RestAPI\PanelApiControllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Admin Management Controllers.
 * Tests controller class existence and structure without database dependencies.
 */
class AdminManagementTest extends TestCase
{
    #[Test]
    public function test_auth_controller_exists(): void
    {
        $this->assertTrue(class_exists(AuthController::class));
    }

    #[Test]
    public function test_customer_controller_exists(): void
    {
        $this->assertTrue(class_exists(CustomerController::class));
    }

    #[Test]
    public function test_dashboard_controller_exists(): void
    {
        $this->assertTrue(class_exists(DashboardController::class));
    }

    #[Test]
    public function test_order_controller_exists(): void
    {
        $this->assertTrue(class_exists(OrderController::class));
    }

    #[Test]
    public function test_product_controller_exists(): void
    {
        $this->assertTrue(class_exists(ProductController::class));
    }

    #[Test]
    public function test_auth_controller_can_be_instantiated(): void
    {
        $controller = new AuthController;
        $this->assertInstanceOf(AuthController::class, $controller);
    }

    #[Test]
    public function test_customer_controller_can_be_instantiated(): void
    {
        $controller = new CustomerController;
        $this->assertInstanceOf(CustomerController::class, $controller);
    }

    #[Test]
    public function test_dashboard_controller_can_be_instantiated(): void
    {
        $controller = new DashboardController;
        $this->assertInstanceOf(DashboardController::class, $controller);
    }

    #[Test]
    public function test_order_controller_can_be_instantiated(): void
    {
        $controller = new OrderController;
        $this->assertInstanceOf(OrderController::class, $controller);
    }

    #[Test]
    public function test_product_controller_can_be_instantiated(): void
    {
        $controller = new ProductController;
        $this->assertInstanceOf(ProductController::class, $controller);
    }

    #[Test]
    public function test_all_panel_controllers_extend_base_controller(): void
    {
        $controllers = [
            AuthController::class,
            CustomerController::class,
            DashboardController::class,
            OrderController::class,
            ProductController::class,
        ];

        foreach ($controllers as $controllerClass) {
            $reflection  = new ReflectionClass($controllerClass);
            $parentClass = $reflection->getParentClass();

            $this->assertNotFalse($parentClass, "$controllerClass should have a parent class");
            $this->assertEquals(
                'InnoShop\RestAPI\PanelApiControllers\BaseController',
                $parentClass->getName(),
                "$controllerClass should extend BaseController"
            );
        }
    }

    #[Test]
    public function test_auth_controller_has_login_method(): void
    {
        $this->assertTrue(method_exists(AuthController::class, 'login'));
    }

    #[Test]
    public function test_auth_controller_has_admin_method(): void
    {
        $this->assertTrue(method_exists(AuthController::class, 'admin'));
    }

    #[Test]
    public function test_customer_controller_has_crud_methods(): void
    {
        $methods = ['index', 'names', 'store', 'update', 'destroy', 'autocomplete'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(CustomerController::class, $method),
                "CustomerController should have $method method"
            );
        }
    }

    #[Test]
    public function test_product_controller_has_required_methods(): void
    {
        $methods = ['index', 'names', 'autocomplete', 'skuAutocomplete', 'import', 'update', 'patch'];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(ProductController::class, $method),
                "ProductController should have $method method"
            );
        }
    }

    #[Test]
    public function test_order_controller_has_update_note_method(): void
    {
        $this->assertTrue(method_exists(OrderController::class, 'updateNote'));
    }

    #[Test]
    public function test_dashboard_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(DashboardController::class, 'index'));
    }

    #[Test]
    public function test_all_panel_controllers_in_correct_namespace(): void
    {
        $controllers = [
            AuthController::class,
            CustomerController::class,
            DashboardController::class,
            OrderController::class,
            ProductController::class,
        ];

        foreach ($controllers as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);
            $this->assertEquals(
                'InnoShop\RestAPI\PanelApiControllers',
                $reflection->getNamespaceName(),
                "$controllerClass should be in PanelApiControllers namespace"
            );
        }
    }
}
