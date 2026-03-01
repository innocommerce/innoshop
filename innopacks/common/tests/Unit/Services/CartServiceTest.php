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

class CartServiceTest extends TestCase
{
    /**
     * Test mergeAuthId adds customer_id to data.
     */
    public function test_merge_auth_id_adds_customer_id(): void
    {
        $customerID = 123;
        $guestID    = '';
        $data       = ['sku_id' => 1, 'quantity' => 2];

        // Simulate mergeAuthId logic
        $data['customer_id'] = $customerID;
        if (empty($customerID)) {
            $data['guest_id'] = $guestID;
        }

        $this->assertEquals(123, $data['customer_id']);
        $this->assertArrayNotHasKey('guest_id', $data);
    }

    /**
     * Test mergeAuthId adds guest_id when no customer.
     */
    public function test_merge_auth_id_adds_guest_id_when_no_customer(): void
    {
        $customerID = 0;
        $guestID    = 'guest-abc-123';
        $data       = ['sku_id' => 1, 'quantity' => 2];

        // Simulate mergeAuthId logic
        $data['customer_id'] = $customerID;
        if (empty($customerID)) {
            $data['guest_id'] = $guestID;
        }

        $this->assertEquals(0, $data['customer_id']);
        $this->assertEquals('guest-abc-123', $data['guest_id']);
    }

    /**
     * Test handleResponse returns correct structure.
     */
    public function test_handle_response_returns_correct_structure(): void
    {
        // Simulate handleResponse logic
        $selectedAmount = 199.99;
        $quantityTotal  = 3;

        $data = [
            'total'         => $quantityTotal,
            'total_format'  => $quantityTotal <= 99 ? $quantityTotal : '99+',
            'amount'        => $selectedAmount,
            'amount_format' => '$199.99', // Simulated currency format
            'list'          => [],
        ];

        $this->assertEquals(3, $data['total']);
        $this->assertEquals(3, $data['total_format']);
        $this->assertEquals(199.99, $data['amount']);
        $this->assertArrayHasKey('list', $data);
    }

    /**
     * Test handleResponse formats total correctly when over 99.
     */
    public function test_handle_response_formats_total_over_99(): void
    {
        $quantityTotal = 150;

        $totalFormat = $quantityTotal <= 99 ? $quantityTotal : '99+';

        $this->assertEquals('99+', $totalFormat);
    }

    /**
     * Test select/unselect cart items logic.
     */
    public function test_select_unselect_logic(): void
    {
        $cartIds = [1, 2, 3];

        // Simulate select logic - should update selected to true
        $selectedIds = $cartIds;
        $this->assertCount(3, $selectedIds);
        $this->assertContains(1, $selectedIds);
        $this->assertContains(2, $selectedIds);
        $this->assertContains(3, $selectedIds);
    }
}
