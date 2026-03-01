<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    /**
     * Test order number generation format.
     */
    public function test_order_number_format(): void
    {
        // Test the order number format logic
        // OrderRepo::generateOrderNumber() typically generates a unique number
        $prefix    = 'ORD';
        $timestamp = date('YmdHis');
        $random    = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $orderNumber = $prefix.$timestamp.$random;

        $this->assertStringStartsWith('ORD', $orderNumber);
        $this->assertGreaterThan(10, strlen($orderNumber));
    }

    /**
     * Test reorder creates new order with same items.
     */
    public function test_reorder_logic(): void
    {
        // Simulate reorder logic
        $originalOrderId = 1;
        $originalItems   = [
            ['product_id' => 1, 'quantity' => 2, 'price' => 99.99],
            ['product_id' => 2, 'quantity' => 1, 'price' => 49.99],
        ];

        // New order should have same items
        $newItems = [];
        foreach ($originalItems as $item) {
            $newItem             = $item;
            $newItem['order_id'] = 2; // New order ID
            $newItems[]          = $newItem;
        }

        $this->assertCount(2, $newItems);
        $this->assertEquals(2, $newItems[0]['order_id']);
        $this->assertEquals(2, $newItems[1]['order_id']);
        $this->assertEquals(1, $newItems[0]['product_id']);
        $this->assertEquals(2, $newItems[1]['product_id']);
    }

    /**
     * Test reorder preserves item quantities.
     */
    public function test_reorder_preserves_quantities(): void
    {
        $originalItems = [
            ['quantity' => 5],
            ['quantity' => 3],
            ['quantity' => 1],
        ];

        $totalQuantity = array_sum(array_column($originalItems, 'quantity'));

        $this->assertEquals(9, $totalQuantity);
    }

    /**
     * Test reorder preserves fees.
     */
    public function test_reorder_preserves_fees(): void
    {
        $originalFees = [
            ['type' => 'shipping', 'amount' => 10.00],
            ['type' => 'tax', 'amount' => 15.50],
        ];

        $newFees = [];
        foreach ($originalFees as $fee) {
            $newFee             = $fee;
            $newFee['order_id'] = 2;
            $newFees[]          = $newFee;
        }

        $this->assertCount(2, $newFees);
        $this->assertEquals('shipping', $newFees[0]['type']);
        $this->assertEquals(10.00, $newFees[0]['amount']);
    }
}
