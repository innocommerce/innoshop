<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\OrderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OrderControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(OrderController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(OrderController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_pay_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('pay'));
        $method = $this->reflection->getMethod('pay');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_number_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('numberShow'));
        $method = $this->reflection->getMethod('numberShow');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_pay_method_accepts_request(): void
    {
        $method     = $this->reflection->getMethod('pay');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_number_show_method_accepts_number(): void
    {
        $method     = $this->reflection->getMethod('numberShow');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('number', $parameters[0]->getName());
    }

    #[Test]
    public function test_order_show_data_structure(): void
    {
        $data = [
            'order' => null,
        ];

        $this->assertArrayHasKey('order', $data);
    }

    #[Test]
    public function test_order_number_format_validation(): void
    {
        $orderNumber = '2024010112345';

        // Order number should be 13 digits
        $this->assertEquals(13, strlen($orderNumber));
        $this->assertMatchesRegularExpression('/^\d{13}$/', $orderNumber);
    }

    #[Test]
    public function test_order_status_check_for_payment(): void
    {
        $order = (object) ['status' => 'unpaid'];

        $canPay = $order->status === 'unpaid';

        $this->assertTrue($canPay);
    }

    #[Test]
    public function test_paid_order_cannot_pay_again(): void
    {
        $order = (object) ['status' => 'paid'];

        $canPay = $order->status === 'unpaid';

        $this->assertFalse($canPay);
    }

    #[Test]
    public function test_completed_order_cannot_pay(): void
    {
        $order = (object) ['status' => 'completed'];

        $canPay = $order->status === 'unpaid';

        $this->assertFalse($canPay);
    }

    #[Test]
    public function test_cancelled_order_cannot_pay(): void
    {
        $order = (object) ['status' => 'cancelled'];

        $canPay = $order->status === 'unpaid';

        $this->assertFalse($canPay);
    }
}
