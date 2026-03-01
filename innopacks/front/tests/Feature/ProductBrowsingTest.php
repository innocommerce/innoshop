<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Front\Controllers\CategoryController;
use InnoShop\Front\Controllers\HomeController;
use InnoShop\Front\Controllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProductBrowsingTest extends TestCase
{
    #[Test]
    public function test_home_controller_exists(): void
    {
        $controller = new HomeController;
        $this->assertInstanceOf(HomeController::class, $controller);
    }

    #[Test]
    public function test_product_controller_exists(): void
    {
        $controller = new ProductController;
        $this->assertInstanceOf(ProductController::class, $controller);
    }

    #[Test]
    public function test_product_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(ProductController::class, 'index'));
    }

    #[Test]
    public function test_product_controller_has_show_method(): void
    {
        $this->assertTrue(method_exists(ProductController::class, 'show'));
    }

    #[Test]
    public function test_product_controller_has_slug_show_method(): void
    {
        $this->assertTrue(method_exists(ProductController::class, 'slugShow'));
    }

    #[Test]
    public function test_product_controller_has_reviews_method(): void
    {
        $this->assertTrue(method_exists(ProductController::class, 'reviews'));
    }

    #[Test]
    public function test_home_controller_has_index_method(): void
    {
        $this->assertTrue(method_exists(HomeController::class, 'index'));
    }

    #[Test]
    public function test_category_controller_exists(): void
    {
        $this->assertTrue(class_exists(CategoryController::class));
    }

    #[Test]
    public function test_product_controller_uses_filter_sidebar_trait(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $traits     = $reflection->getTraitNames();
        $this->assertContains('InnoShop\Front\Traits\FilterSidebarTrait', $traits);
    }
}
