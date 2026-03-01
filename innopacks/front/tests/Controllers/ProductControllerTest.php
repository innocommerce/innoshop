<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
    public function test_has_index_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
    }

    #[Test]
    public function test_has_show_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'show'));
    }

    #[Test]
    public function test_has_slug_show_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'slugShow'));
    }

    #[Test]
    public function test_has_reviews_method(): void
    {
        $this->assertTrue(method_exists($this->controller, 'reviews'));
    }

    #[Test]
    public function test_index_method_is_public(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $method     = $reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_show_method_is_public(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $method     = $reflection->getMethod('show');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_slug_show_method_is_public(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $method     = $reflection->getMethod('slugShow');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_reviews_method_is_public(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $method     = $reflection->getMethod('reviews');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_render_show_method_is_private(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $method     = $reflection->getMethod('renderShow');
        $this->assertTrue($method->isPrivate());
    }

    #[Test]
    public function test_extends_base_controller(): void
    {
        $reflection  = new ReflectionClass(ProductController::class);
        $parentClass = $reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_uses_filter_sidebar_trait(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $traits     = $reflection->getTraitNames();
        $this->assertContains('InnoShop\Front\Traits\FilterSidebarTrait', $traits);
    }
}
