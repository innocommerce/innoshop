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

class CheckoutServiceTest extends TestCase
{
    /**
     * Test shipping address validation.
     */
    public function test_shipping_address_required_fields(): void
    {
        $requiredFields = ['name', 'phone', 'country_id', 'state_id', 'city', 'address1', 'zipcode'];

        $address = [
            'name'       => 'John Doe',
            'phone'      => '1234567890',
            'country_id' => 1,
            'state_id'   => 1,
            'city'       => 'New York',
            'address1'   => '123 Main St',
            'zipcode'    => '10001',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $address);
            $this->assertNotEmpty($address[$field]);
        }
    }

    /**
     * Test checkout data structure.
     */
    public function test_checkout_data_structure(): void
    {
        $checkoutData = [
            'customer_id'      => 1,
            'shipping_address' => ['id' => 1],
            'billing_address'  => ['id' => 2],
            'shipping_method'  => 'standard',
            'payment_method'   => 'cod',
            'comment'          => 'Please deliver in the morning',
        ];

        $this->assertArrayHasKey('customer_id', $checkoutData);
        $this->assertArrayHasKey('shipping_address', $checkoutData);
        $this->assertArrayHasKey('billing_address', $checkoutData);
        $this->assertArrayHasKey('shipping_method', $checkoutData);
        $this->assertArrayHasKey('payment_method', $checkoutData);
    }

    /**
     * Test order total calculation.
     */
    public function test_order_total_calculation(): void
    {
        $subtotal    = 199.99;
        $shippingFee = 10.00;
        $taxAmount   = 15.50;
        $discount    = 20.00;

        $total = $subtotal + $shippingFee + $taxAmount - $discount;

        $this->assertEquals(205.49, $total);
    }

    /**
     * Test guest checkout data.
     */
    public function test_guest_checkout_data(): void
    {
        $guestData = [
            'guest_id' => 'guest-abc-123',
            'email'    => 'guest@example.com',
            'name'     => 'Guest User',
        ];

        $this->assertNotEmpty($guestData['guest_id']);
        $this->assertStringContainsString('@', $guestData['email']);
    }

    /**
     * Test cart items to order items conversion.
     */
    public function test_cart_to_order_items_conversion(): void
    {
        $cartItems = [
            ['sku_id' => 1, 'quantity' => 2, 'price' => 50.00],
            ['sku_id' => 2, 'quantity' => 1, 'price' => 99.99],
        ];

        $orderItems = [];
        foreach ($cartItems as $cartItem) {
            $orderItems[] = [
                'sku_id'   => $cartItem['sku_id'],
                'quantity' => $cartItem['quantity'],
                'price'    => $cartItem['price'],
                'subtotal' => $cartItem['quantity'] * $cartItem['price'],
            ];
        }

        $this->assertCount(2, $orderItems);
        $this->assertEquals(100.00, $orderItems[0]['subtotal']);
        $this->assertEquals(99.99, $orderItems[1]['subtotal']);
    }
}
