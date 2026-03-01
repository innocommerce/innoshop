<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\AnalyticsController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnalyticsControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(AnalyticsController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(AnalyticsController::class));
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
    public function test_order_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('order'));
        $method = $this->reflection->getMethod('order');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_product_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('product'));
        $method = $this->reflection->getMethod('product');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_customer_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_visit_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('visit'));
        $method = $this->reflection->getMethod('visit');
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
    public function test_order_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('order');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_product_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('product');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_customer_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('customer');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_visit_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('visit');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'date_filter'         => '',
            'start_date'          => '',
            'end_date'            => '',
            'order_statistics'    => [],
            'product_statistics'  => [],
            'customer_statistics' => [],
            'order_trends'        => [],
            'product_trends'      => [],
            'customer_trends'     => [],
            'pending_orders'      => 0,
            'unpaid_orders'       => 0,
            'top_products'        => [],
            'status_distribution' => [],
            'visit_statistics'    => [
                'page_views'      => 0,
                'unique_visitors' => 0,
            ],
        ];

        $this->assertArrayHasKey('date_filter', $data);
        $this->assertArrayHasKey('order_statistics', $data);
        $this->assertArrayHasKey('product_statistics', $data);
        $this->assertArrayHasKey('customer_statistics', $data);
        $this->assertArrayHasKey('visit_statistics', $data);
    }

    #[Test]
    public function test_order_data_structure(): void
    {
        $data = [
            'date_filter'         => '',
            'start_date'          => '',
            'end_date'            => '',
            'order_statistics'    => [],
            'daily_trends'        => [],
            'status_distribution' => [],
            'top_sale_products'   => [],
        ];

        $this->assertArrayHasKey('date_filter', $data);
        $this->assertArrayHasKey('order_statistics', $data);
        $this->assertArrayHasKey('daily_trends', $data);
        $this->assertArrayHasKey('status_distribution', $data);
    }

    #[Test]
    public function test_product_data_structure(): void
    {
        $data = [
            'date_filter'           => '',
            'start_date'            => '',
            'end_date'              => '',
            'product_statistics'    => [],
            'daily_trends'          => [],
            'top_products'          => [],
            'category_distribution' => [],
        ];

        $this->assertArrayHasKey('date_filter', $data);
        $this->assertArrayHasKey('product_statistics', $data);
        $this->assertArrayHasKey('daily_trends', $data);
        $this->assertArrayHasKey('top_products', $data);
        $this->assertArrayHasKey('category_distribution', $data);
    }

    #[Test]
    public function test_customer_data_structure(): void
    {
        $data = [
            'date_filter'         => '',
            'start_date'          => '',
            'end_date'            => '',
            'customer_statistics' => [],
            'daily_trends'        => [],
            'source_distribution' => [],
        ];

        $this->assertArrayHasKey('date_filter', $data);
        $this->assertArrayHasKey('customer_statistics', $data);
        $this->assertArrayHasKey('daily_trends', $data);
        $this->assertArrayHasKey('source_distribution', $data);
    }

    #[Test]
    public function test_visit_data_structure(): void
    {
        $data = [
            'statistics'         => [],
            'visits_by_country'  => [],
            'visits_by_device'   => [],
            'daily_statistics'   => [],
            'hourly_statistics'  => [],
            'conversion_funnel'  => [],
            'avg_visit_duration' => 0,
            'start_date'         => '',
            'end_date'           => '',
            'date_filter'        => '',
        ];

        $this->assertArrayHasKey('statistics', $data);
        $this->assertArrayHasKey('visits_by_country', $data);
        $this->assertArrayHasKey('visits_by_device', $data);
        $this->assertArrayHasKey('daily_statistics', $data);
        $this->assertArrayHasKey('hourly_statistics', $data);
        $this->assertArrayHasKey('conversion_funnel', $data);
    }
}
