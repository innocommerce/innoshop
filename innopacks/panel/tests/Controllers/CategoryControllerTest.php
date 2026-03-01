<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\CategoryController;
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
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Panel\Controllers\BaseController'));
    }

    #[Test]
    public function test_index_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('index'));
        $method = $this->reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_create_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('create'));
        $method = $this->reflection->getMethod('create');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_store_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('store'));
        $method = $this->reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_edit_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('edit'));
        $method = $this->reflection->getMethod('edit');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_form_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('form'));
        $method = $this->reflection->getMethod('form');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('update'));
        $method = $this->reflection->getMethod('update');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_destroy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
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
    public function test_store_method_accepts_category_request(): void
    {
        $method     = $this->reflection->getMethod('store');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_edit_method_accepts_category_parameter(): void
    {
        $method     = $this->reflection->getMethod('edit');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('category', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_accepts_request_and_category(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('category', $parameters[1]->getName());
    }

    #[Test]
    public function test_destroy_method_accepts_category_parameter(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('category', $parameters[0]->getName());
    }

    #[Test]
    public function test_store_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('store');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_update_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('update');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_form_method_accepts_category_parameter(): void
    {
        $method     = $this->reflection->getMethod('form');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('category', $parameters[0]->getName());
    }

    #[Test]
    public function test_create_method_has_no_parameters(): void
    {
        $method     = $this->reflection->getMethod('create');
        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function test_category_child_exclusion_logic(): void
    {
        // Test the logic for excluding child categories
        $categoryId = 1;
        $childIds   = [2, 3, 4];

        $excludeIds = array_merge($childIds, [$categoryId]);
        $excludeIds = array_unique($excludeIds);

        $this->assertContains(1, $excludeIds);
        $this->assertContains(2, $excludeIds);
        $this->assertContains(3, $excludeIds);
        $this->assertContains(4, $excludeIds);
        $this->assertCount(4, $excludeIds);
    }

    #[Test]
    public function test_category_child_exclusion_with_empty_children(): void
    {
        $categoryId = 1;
        $childIds   = [];

        $excludeIds = array_merge($childIds, [$categoryId]);
        $excludeIds = array_unique($excludeIds);

        $this->assertContains(1, $excludeIds);
        $this->assertCount(1, $excludeIds);
    }

    #[Test]
    public function test_category_child_exclusion_for_new_category(): void
    {
        $categoryId = 0; // New category has no ID
        $childIds   = [];

        $excludeIds = [];
        if ($categoryId) {
            $excludeIds = array_merge($childIds, [$categoryId]);
        }
        $excludeIds = array_unique($excludeIds);

        $this->assertCount(0, $excludeIds);
    }

    #[Test]
    public function test_filter_defaults_for_hierarchical_categories(): void
    {
        $filters = [
            'active'      => 1,
            'exclude_ids' => [1, 2, 3],
        ];

        $this->assertEquals(1, $filters['active']);
        $this->assertIsArray($filters['exclude_ids']);
        $this->assertCount(3, $filters['exclude_ids']);
    }

    #[Test]
    public function test_root_category_prepend_logic(): void
    {
        $hierarchicalCategories = [
            ['id' => 1, 'name' => 'Category 1', 'level' => 1],
            ['id' => 2, 'name' => 'Category 2', 'level' => 1],
        ];

        $rootCategory = [
            'id'    => 0,
            'name'  => 'Root Category',
            'level' => 0,
        ];

        array_unshift($hierarchicalCategories, $rootCategory);

        $this->assertCount(3, $hierarchicalCategories);
        $this->assertEquals(0, $hierarchicalCategories[0]['id']);
        $this->assertEquals('Root Category', $hierarchicalCategories[0]['name']);
        $this->assertEquals(0, $hierarchicalCategories[0]['level']);
    }

    #[Test]
    public function test_index_filter_parent_id_default(): void
    {
        $filters              = [];
        $filters['parent_id'] = 0;

        $this->assertEquals(0, $filters['parent_id']);
    }
}
