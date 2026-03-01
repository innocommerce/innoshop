<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests\PanelApiControllers;

use InnoShop\RestAPI\PanelApiControllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for Panel ProductController.
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
        $this->assertEquals('InnoShop\RestAPI\PanelApiControllers\BaseController', $parentClass->getName());
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
    public function test_names_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'names'));
    }

    #[Test]
    public function test_names_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('names');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_autocomplete_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'autocomplete'));
    }

    #[Test]
    public function test_autocomplete_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('autocomplete');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_sku_autocomplete_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'skuAutocomplete'));
    }

    #[Test]
    public function test_sku_autocomplete_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('skuAutocomplete');

        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_import_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'import'));
    }

    #[Test]
    public function test_import_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('import');

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
    public function test_patch_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'patch'));
    }

    #[Test]
    public function test_patch_method_is_public(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('patch');

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
    public function test_update_method_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('update');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('spuCode', $parameters[1]->getName());
    }

    #[Test]
    public function test_patch_method_has_correct_parameters(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $method     = $reflection->getMethod('patch');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('spuCode', $parameters[1]->getName());
    }
}
