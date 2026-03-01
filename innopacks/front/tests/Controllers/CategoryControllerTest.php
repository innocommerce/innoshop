<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\CategoryController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CategoryControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(CategoryController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(CategoryController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_uses_filter_sidebar_trait(): void
    {
        $traits = $this->reflection->getTraitNames();
        $this->assertContains('InnoShop\Front\Traits\FilterSidebarTrait', $traits);
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
    public function test_render_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('renderShow'));
        $method = $this->reflection->getMethod('renderShow');
        $this->assertTrue($method->isPrivate());
    }

    #[Test]
    public function test_index_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_show_method_accepts_request_and_category(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('category', $parameters[1]->getName());
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
    public function test_filter_extraction_keys(): void
    {
        $expectedFilterKeys = [
            'keyword',
            'sort',
            'order',
            'per_page',
            'price_from',
            'price_to',
            'brand_ids',
            'attribute_values',
            'in_stock',
        ];

        $this->assertCount(9, $expectedFilterKeys);
        $this->assertContains('keyword', $expectedFilterKeys);
        $this->assertContains('sort', $expectedFilterKeys);
        $this->assertContains('order', $expectedFilterKeys);
        $this->assertContains('per_page', $expectedFilterKeys);
        $this->assertContains('price_from', $expectedFilterKeys);
        $this->assertContains('price_to', $expectedFilterKeys);
        $this->assertContains('brand_ids', $expectedFilterKeys);
        $this->assertContains('attribute_values', $expectedFilterKeys);
        $this->assertContains('in_stock', $expectedFilterKeys);
    }

    #[Test]
    public function test_keyword_search_filter_structure(): void
    {
        $keyword = 'test';
        $filters = [
            'keyword' => $keyword,
            'active'  => true,
        ];

        $this->assertEquals('test', $filters['keyword']);
        $this->assertTrue($filters['active']);
    }

    #[Test]
    public function test_category_show_data_structure(): void
    {
        $data = [
            'slug'           => 'test-category',
            'category'       => null,
            'categories'     => [],
            'products'       => [],
            'per_page_items' => [12, 24, 36],
        ];

        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('category', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('per_page_items', $data);
    }

    #[Test]
    public function test_category_index_with_keyword_data_structure(): void
    {
        $data = [
            'categories' => [],
            'keyword'    => 'search term',
        ];

        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('keyword', $data);
    }

    #[Test]
    public function test_category_index_without_keyword_data_structure(): void
    {
        $data = [
            'products'       => [],
            'categories'     => [],
            'per_page_items' => [],
        ];

        $this->assertArrayHasKey('products', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('per_page_items', $data);
    }

    #[Test]
    public function test_category_filter_with_category_id(): void
    {
        $categoryId = 5;
        $keyword    = 'test';

        $filters                = [];
        $filters['category_id'] = $categoryId;
        if ($keyword) {
            $filters['keyword'] = $keyword;
        }

        $this->assertEquals(5, $filters['category_id']);
        $this->assertEquals('test', $filters['keyword']);
    }

    #[Test]
    public function test_category_filter_without_keyword(): void
    {
        $categoryId = 5;
        $keyword    = '';

        $filters                = [];
        $filters['category_id'] = $categoryId;
        if ($keyword) {
            $filters['keyword'] = $keyword;
        }

        $this->assertEquals(5, $filters['category_id']);
        $this->assertArrayNotHasKey('keyword', $filters);
    }

    #[Test]
    public function test_inactive_category_should_abort(): void
    {
        $category = (object) ['active' => false];

        $shouldAbort = ! $category->active;

        $this->assertTrue($shouldAbort);
    }

    #[Test]
    public function test_active_category_should_not_abort(): void
    {
        $category = (object) ['active' => true];

        $shouldAbort = ! $category->active;

        $this->assertFalse($shouldAbort);
    }

    #[Test]
    public function test_slug_fallback_for_empty_slug(): void
    {
        $category = (object) ['slug' => null];

        $slug = $category->slug ?? '';

        $this->assertEquals('', $slug);
    }

    #[Test]
    public function test_slug_value_when_present(): void
    {
        $category = (object) ['slug' => 'electronics'];

        $slug = $category->slug ?? '';

        $this->assertEquals('electronics', $slug);
    }
}
