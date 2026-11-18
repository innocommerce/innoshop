<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Services\StateMachineService;
use Tests\TestCase;

/**
 * Integration coverage for the default (plugin-independent) order state machine.
 * COD-specific transitions are tested in the Cod plugin itself.
 */
class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_to_cancelled_restores_stock(): void
    {
        [$order, $sku] = $this->setupOrder(initialStock: 10, orderQty: 4);

        // Standard online-payment flow: unpaid -> paid (deducts), paid -> cancelled (must restore)
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
        $this->assertSame(6, (int) $sku->fresh()->quantity);

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::CANCELLED);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, (int) $sku->fresh()->quantity, 'paid -> cancelled must restore stock');
    }

    /**
     * @return array{0: Order, 1: Sku}
     */
    private function setupOrder(int $initialStock, int $orderQty): array
    {
        $customer = Customer::query()->create([
            'email'             => 'sm_'.$orderQty.'_'.uniqid().'@example.com',
            'name'              => 'State Machine Tester',
            'customer_group_id' => 1,
            'password'          => 'x',
            'active'            => true,
        ]);

        $sku = Sku::query()->create([
            'product_id' => 1,
            'code'       => 'SM-SKU-'.uniqid(),
            'price'      => 10.0,
            'quantity'   => $initialStock,
            'is_default' => true,
            'position'   => 1,
        ]);

        $order = Order::query()->create([
            'customer_id'            => $customer->id,
            'customer_group_id'      => 1,
            'number'                 => 'SM-'.uniqid(),
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => $orderQty * 10.0,
            'status'                 => StateMachineService::UNPAID,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => $customer->name,
            'email'                  => $customer->email,
            'calling_code'           => '1',
            'telephone'              => '1234567890',
            'locale'                 => 'en',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'PHPUnit',
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat',
            'billing_method_code'    => 'bank_transfer',
            'billing_method_name'    => 'Bank Transfer',
            'shipping_customer_name' => $customer->name,
            'shipping_calling_code'  => '1',
            'shipping_telephone'     => '1234567890',
            'shipping_country'       => 'US',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'NY',
            'shipping_city'          => 'NY',
            'shipping_address_1'     => '1 St',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '10001',
            'billing_customer_name'  => $customer->name,
            'billing_calling_code'   => '1',
            'billing_telephone'      => '1234567890',
            'billing_country'        => 'US',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'NY',
            'billing_city'           => 'NY',
            'billing_address_1'      => '1 St',
            'billing_address_2'      => '',
            'billing_zipcode'        => '10001',
        ]);

        Order\Item::query()->create([
            'order_id'      => $order->id,
            'order_number'  => $order->number,
            'product_id'    => $sku->product_id,
            'product_sku'   => $sku->code,
            'variant_label' => '',
            'name'          => 'Test SKU',
            'image'         => '',
            'quantity'      => $orderQty,
            'price'         => 10.0,
            'total'         => $orderQty * 10.0,
            'item_type'     => 'product',
            'item_id'       => $sku->product_id,
        ]);

        return [$order->fresh(), $sku];
    }
}
