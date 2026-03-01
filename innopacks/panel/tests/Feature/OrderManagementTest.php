<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use InnoShop\Panel\Controllers\OrderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Feature tests for Order Management functionality.
 * Tests verify the OrderController has all required methods for order management.
 */
class OrderManagementTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(OrderController::class);
    }

    #[Test]
    public function test_order_controller_exists(): void
    {
        $this->assertTrue(class_exists(OrderController::class));
    }

    #[Test]
    public function test_can_list_orders_via_index_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('index'));
        $method = $this->reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_view_order_detail_via_show_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('show'));
        $method = $this->reflection->getMethod('show');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_edit_order_via_edit_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('edit'));
        $method = $this->reflection->getMethod('edit');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_change_order_status(): void
    {
        $this->assertTrue($this->reflection->hasMethod('changeStatus'));
        $method = $this->reflection->getMethod('changeStatus');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_delete_order_via_destroy_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_print_order_via_printing_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('printing'));
        $method = $this->reflection->getMethod('printing');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_export_orders_via_export_batch_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('exportBatch'));
        $method = $this->reflection->getMethod('exportBatch');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_can_view_order_form(): void
    {
        $this->assertTrue($this->reflection->hasMethod('form'));
        $method = $this->reflection->getMethod('form');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_change_status_accepts_request_and_order(): void
    {
        $method     = $this->reflection->getMethod('changeStatus');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('order', $parameters[1]->getName());
    }

    #[Test]
    public function test_destroy_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }
}
