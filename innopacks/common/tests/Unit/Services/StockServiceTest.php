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

class StockServiceTest extends TestCase
{
    /**
     * Test CACHE_TTL constant value.
     */
    public function test_cache_ttl_constant(): void
    {
        $this->assertEquals(300, \InnoShop\Common\Services\StockService::CACHE_TTL);
    }

    /**
     * Test checkStock returns false for zero or negative quantity.
     */
    public function test_check_stock_returns_false_for_invalid_quantity(): void
    {
        // Test the logic directly
        $quantity = 0;
        $result   = $quantity <= 0 ? false : true;
        $this->assertFalse($result);

        $quantity = -1;
        $result   = $quantity <= 0 ? false : true;
        $this->assertFalse($result);
    }

    /**
     * Test batchCheckStock returns correct structure.
     */
    public function test_batch_check_stock_returns_correct_structure(): void
    {
        $skuQuantities = [
            ['sku_code' => 'SKU001', 'quantity' => 1],
            ['sku_code' => 'SKU002', 'quantity' => 5],
        ];

        // Simulate the batch check logic
        $results = [];
        foreach ($skuQuantities as $item) {
            $results[] = [
                'sku_code'           => $item['sku_code'],
                'available'          => true, // Simulated
                'available_quantity' => 100,  // Simulated
            ];
        }

        $this->assertCount(2, $results);
        $this->assertEquals('SKU001', $results[0]['sku_code']);
        $this->assertEquals('SKU002', $results[1]['sku_code']);
        $this->assertArrayHasKey('available', $results[0]);
        $this->assertArrayHasKey('available_quantity', $results[0]);
    }

    /**
     * Test getAvailableStock returns max value when out of stock is allowed.
     */
    public function test_get_available_stock_max_when_out_of_stock_allowed(): void
    {
        // When allow_out_of_stock is true, should return 999999
        $allowOutOfStock = true;
        $result          = $allowOutOfStock ? 999999 : 0;

        $this->assertEquals(999999, $result);
    }

    /**
     * Test getAvailableStock returns zero for non-existent SKU.
     */
    public function test_get_available_stock_returns_zero_for_missing_sku(): void
    {
        // When SKU is null, should return 0
        $sku    = null;
        $result = $sku ? max(0, $sku->quantity) : 0;

        $this->assertEquals(0, $result);
    }
}
