<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\ProductController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProductControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ProductController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(ProductController::class));
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
    public function test_copy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('copy'));
        $method = $this->reflection->getMethod('copy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_bulk_update_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('bulkUpdate'));
        $method = $this->reflection->getMethod('bulkUpdate');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_bulk_destroy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('bulkDestroy'));
        $method = $this->reflection->getMethod('bulkDestroy');
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
    public function test_store_method_accepts_product_request(): void
    {
        $method     = $this->reflection->getMethod('store');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_accepts_request_and_product(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('product', $parameters[1]->getName());
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
    public function test_prepare_product_options_data_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('prepareProductOptionsData'));
        $method = $this->reflection->getMethod('prepareProductOptionsData');
        $this->assertTrue($method->isPrivate());
    }
}
