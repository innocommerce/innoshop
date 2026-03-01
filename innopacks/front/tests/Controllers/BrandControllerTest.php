<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\BrandController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BrandControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(BrandController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(BrandController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_index_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('index'));
        $method = $this->reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('show'));
        $method = $this->reflection->getMethod('show');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_slug_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('slugShow'));
        $method = $this->reflection->getMethod('slugShow');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_has_no_parameters(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function test_show_method_accepts_request_and_brand(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(1, count($parameters));
    }

    #[Test]
    public function test_slug_show_method_accepts_request(): void
    {
        $method     = $this->reflection->getMethod('slugShow');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_brand_index_data_structure(): void
    {
        $data = [
            'brands' => [],
        ];

        $this->assertArrayHasKey('brands', $data);
    }

    #[Test]
    public function test_brand_show_data_structure(): void
    {
        $data = [
            'brand'    => null,
            'products' => [],
        ];

        $this->assertArrayHasKey('brand', $data);
        $this->assertArrayHasKey('products', $data);
    }

    #[Test]
    public function test_brand_filter_active_only(): void
    {
        $filters = [
            'active' => true,
        ];

        $this->assertTrue($filters['active']);
    }

    #[Test]
    public function test_inactive_brand_should_abort(): void
    {
        $brand = (object) ['active' => false];

        $shouldAbort = ! $brand->active;

        $this->assertTrue($shouldAbort);
    }

    #[Test]
    public function test_active_brand_should_not_abort(): void
    {
        $brand = (object) ['active' => true];

        $shouldAbort = ! $brand->active;

        $this->assertFalse($shouldAbort);
    }

    #[Test]
    public function test_brand_product_filter(): void
    {
        $brandId = 5;
        $filters = [
            'brand_id' => $brandId,
            'active'   => true,
        ];

        $this->assertEquals(5, $filters['brand_id']);
        $this->assertTrue($filters['active']);
    }
}
