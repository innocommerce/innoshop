<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use Database\Factories\BrandFactory;
use Database\Factories\CustomerFactory;
use Database\Factories\OrderFactory;
use Database\Factories\ProductFactory;
use InnoShop\Aicore\Tools\OrderDetailTool;
use InnoShop\Aicore\Tools\OrderListTool;
use InnoShop\Aicore\Tools\ProductDetailTool;
use InnoShop\Aicore\Tools\ProductListTool;
use InnoShop\Aicore\Tools\SalesStatsTool;
use InnoShop\Aicore\Tools\StockReportTool;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CoreToolsTest extends TestCase
{
    private function createProductWithSku(string $name = 'Test Product', int $quantity = 100): mixed
    {
        $brand   = BrandFactory::new()->create();
        $product = ProductFactory::new()->withBrand($brand)->create(['tax_class_id' => 0]);
        $product->translations()->create([
            'locale' => 'en',
            'name'   => $name,
        ]);
        $product->skus()->create([
            'images'       => [],
            'model'        => 'M1',
            'code'         => 'SKU-'.$product->id,
            'price'        => 99.00,
            'origin_price' => 129.00,
            'quantity'     => $quantity,
            'is_default'   => true,
            'position'     => 0,
        ]);

        return $product->refresh();
    }

    private function createOrder(array $attributes = []): mixed
    {
        $customer = CustomerFactory::new()->create(['customer_group_id' => 0, 'address_id' => 0]);

        return OrderFactory::new()->create(['customer_id' => $customer->id] + $attributes);
    }

    #[Test]
    public function test_product_list_returns_products(): void
    {
        $this->createProductWithSku('Unique MCP Keyword Product');

        $result = (new ProductListTool)->execute(['keyword' => 'Unique MCP Keyword']);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertSame('Unique MCP Keyword Product', $result['items'][0]['name']);
        $this->assertSame(100, $result['items'][0]['stock']);
    }

    #[Test]
    public function test_product_list_active_filter(): void
    {
        $product = $this->createProductWithSku('Inactive Filter Product');
        $product->update(['active' => false]);

        $inactive = (new ProductListTool)->execute(['keyword' => 'Inactive Filter', 'active' => false]);
        $this->assertGreaterThanOrEqual(1, $inactive['total']);

        $active = (new ProductListTool)->execute(['keyword' => 'Inactive Filter', 'active' => true]);
        $this->assertSame(0, $active['total']);
    }

    #[Test]
    public function test_product_detail_returns_skus(): void
    {
        $product = $this->createProductWithSku('Detail Product');

        $result = (new ProductDetailTool)->execute(['id' => $product->id]);

        $this->assertSame('Detail Product', $result['name']);
        $this->assertCount(1, $result['skus']);
        $this->assertSame(100, $result['skus'][0]['quantity']);
    }

    #[Test]
    public function test_product_detail_throws_for_missing_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ProductDetailTool)->execute(['id' => 999999]);
    }

    #[Test]
    public function test_order_list_and_detail(): void
    {
        $order = $this->createOrder();

        $list = (new OrderListTool)->execute(['number' => $order->number]);
        $this->assertGreaterThanOrEqual(1, $list['total']);
        $this->assertSame($order->number, $list['items'][0]['number']);

        $detail = (new OrderDetailTool)->execute(['number' => $order->number]);
        $this->assertSame($order->number, $detail['number']);
        $this->assertSame($order->customer_name, $detail['customer_name']);
        $this->assertSame($order->email, $detail['email']);
    }

    #[Test]
    public function test_order_detail_throws_for_missing_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new OrderDetailTool)->execute(['number' => 'ORD-NOT-EXISTS']);
    }

    #[Test]
    public function test_stock_report_finds_low_stock(): void
    {
        $this->createProductWithSku('Low Stock Product', 3);
        $this->createProductWithSku('High Stock Product', 500);

        $result = (new StockReportTool)->execute(['threshold' => 10]);

        $names = array_column($result['items'], 'product_name');
        $this->assertContains('Low Stock Product', $names);
        $this->assertNotContains('High Stock Product', $names);
    }

    #[Test]
    public function test_sales_stats_aggregates_orders(): void
    {
        $this->createOrder(['total' => 100.00]);
        $this->createOrder(['total' => 300.00]);

        $result = (new SalesStatsTool)->execute([]);

        $this->assertSame(2, $result['order_count']);
        $this->assertSame(400.0, $result['revenue']);
        $this->assertSame(200.0, $result['average_order_value']);
    }

    #[Test]
    public function test_tools_have_permission_and_schema(): void
    {
        $tools = [
            new ProductListTool, new ProductDetailTool,
            new OrderListTool, new OrderDetailTool,
            new StockReportTool, new SalesStatsTool,
        ];

        foreach ($tools as $tool) {
            $this->assertNotEmpty($tool->name());
            $this->assertNotEmpty($tool->description());
            $this->assertNotEmpty($tool->requiredPermission(), $tool->name().' must require a permission');
            $this->assertSame('object', $tool->inputSchema()['type']);
        }
    }
}
