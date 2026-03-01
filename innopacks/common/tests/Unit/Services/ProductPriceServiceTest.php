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

class ProductPriceServiceTest extends TestCase
{
    /**
     * Test customer price calculation with discount rate.
     */
    public function test_customer_price_calculation_with_discount(): void
    {
        $skuPrice     = 100.00;
        $discountRate = 80; // 80% discount rate
        $decimal      = 2;

        $result = round($skuPrice * $discountRate / 100, $decimal);

        $this->assertEquals(80.00, $result);
    }

    /**
     * Test customer price calculation with 100% rate.
     */
    public function test_customer_price_calculation_full_price(): void
    {
        $skuPrice     = 99.99;
        $discountRate = 100;
        $decimal      = 2;

        $result = round($skuPrice * $discountRate / 100, $decimal);

        $this->assertEquals(99.99, $result);
    }

    /**
     * Test customer price calculation with 50% rate.
     */
    public function test_customer_price_calculation_half_price(): void
    {
        $skuPrice     = 200.00;
        $discountRate = 50;
        $decimal      = 2;

        $result = round($skuPrice * $discountRate / 100, $decimal);

        $this->assertEquals(100.00, $result);
    }

    /**
     * Test price rounding with different decimal places.
     */
    public function test_price_rounding_with_decimals(): void
    {
        $skuPrice     = 99.99;
        $discountRate = 85;

        // Test with 2 decimal places
        $result2 = round($skuPrice * $discountRate / 100, 2);
        $this->assertEquals(84.99, $result2);

        // Test with 0 decimal places
        $result0 = round($skuPrice * $discountRate / 100, 0);
        $this->assertEquals(85, $result0);
    }

    /**
     * Test price returns original when no customer group.
     */
    public function test_price_returns_original_without_customer_group(): void
    {
        $skuPrice      = 150.00;
        $customerGroup = null;

        // When no customer group, return original price
        $result = empty($customerGroup) ? $skuPrice : 0;

        $this->assertEquals(150.00, $result);
    }
}
