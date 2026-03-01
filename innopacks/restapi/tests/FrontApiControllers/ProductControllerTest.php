<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\FrontApiControllers;

use InnoShop\RestAPI\FrontApiControllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Front ProductController.
 * Tests method existence and basic structure without database dependencies.
 */
class ProductControllerTest extends TestCase
{
    private ProductController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ProductController;
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertInstanceOf(ProductController::class, $this->controller);
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
    public function test_filters_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'filters'));
    }

    #[Test]
    public function test_filters_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('filters');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_product_reviews_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'productReviews'));
    }

    #[Test]
    public function test_product_reviews_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('productReviews');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_sku_list_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'skuList'));
    }

    #[Test]
    public function test_sku_list_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('skuList');

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
    public function test_show_method_has_product_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('show');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('product', $parameters[0]->getName());
    }

    #[Test]
    public function test_product_reviews_method_has_product_parameter(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('productReviews');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('product', $parameters[0]->getName());
    }
}
