<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\FrontApiControllers;

use InnoShop\RestAPI\FrontApiControllers\CheckoutController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Front CheckoutController.
 * Tests method existence and basic structure without database dependencies.
 */
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
    public function test_confirm_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'confirm'));
    }

    #[Test]
    public function test_confirm_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('confirm');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_billing_methods_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'billingMethods'));
    }

    #[Test]
    public function test_billing_methods_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('billingMethods');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_quick_confirm_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'quickConfirm'));
    }

    #[Test]
    public function test_quick_confirm_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('quickConfirm');

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
    public function test_update_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('update');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_confirm_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('confirm');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_billing_methods_has_no_parameters(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('billingMethods');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function test_quick_confirm_method_has_request_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('quickConfirm');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }
}
