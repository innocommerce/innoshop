<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use InnoShop\Panel\Controllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Feature tests for Product Management functionality.
 * Tests verify the ProductController has all required methods for product management.
 */
class ProductManagementTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ProductController::class);
    }

    #[Test]
    public function test_product_controller_exists(): void
    {
        $this->assertTrue(class_exists(ProductController::class));
    }

    #[Test]
    public function test_can_list_products_via_index_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('index'));
        $method = $this->reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_create_product_via_create_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('create'));
        $method = $this->reflection->getMethod('create');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_store_product_via_store_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('store'));
        $method = $this->reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_edit_product_via_edit_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('edit'));
        $method = $this->reflection->getMethod('edit');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_update_product_via_update_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('update'));
        $method = $this->reflection->getMethod('update');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_delete_product_via_destroy_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_copy_product_via_copy_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('copy'));
        $method = $this->reflection->getMethod('copy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_bulk_update_products(): void
    {
        $this->assertTrue($this->reflection->hasMethod('bulkUpdate'));
        $method = $this->reflection->getMethod('bulkUpdate');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_bulk_destroy_products(): void
    {
        $this->assertTrue($this->reflection->hasMethod('bulkDestroy'));
        $method = $this->reflection->getMethod('bulkDestroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_view_product_form(): void
    {
        $this->assertTrue($this->reflection->hasMethod('form'));
        $method = $this->reflection->getMethod('form');
        $this->assertTrue($method->isPublic());
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
    public function test_destroy_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_copy_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('copy');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }
}
