<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\OrderReturn;
use InnoShop\Common\Services\ReturnStateService;
use InnoShop\Common\Services\StateMachineService;
use InnoShop\Common\Tests\TestCase;

/**
 * The after-sale service used to blow up on an unsaved order return (the id
 * was typed as a non-nullable int) and it never moved the related order to
 * the "refunded" state.
 */
class ReturnStateServiceTest extends TestCase
{
    public function test_service_accepts_an_unsaved_order_return(): void
    {
        $orderReturn = new OrderReturn;

        $service = ReturnStateService::getInstance($orderReturn);

        $this->assertInstanceOf(ReturnStateService::class, $service);
        $this->assertSame([], $service->nextBackendStatuses());
    }

    public function test_refund_completion_moves_the_order_to_refunded(): void
    {
        [$order, $orderReturn] = $this->makeOrderReturn(StateMachineService::PAID);

        ReturnStateService::getInstance($orderReturn)->changeStatus(ReturnStateService::REFUNDED);

        $this->assertSame(ReturnStateService::REFUNDED, $orderReturn->fresh()->status);
        $this->assertSame(StateMachineService::REFUNDED, $order->fresh()->status, 'the order must follow the refund');
        $this->assertSame(
            1,
            Order\History::query()->where('order_id', $order->id)->where('status', StateMachineService::REFUNDED)->count(),
            'the order history must record the refund'
        );
    }

    public function test_refund_does_not_touch_an_order_that_cannot_be_refunded(): void
    {
        [$order, $orderReturn] = $this->makeOrderReturn(StateMachineService::CANCELLED);

        ReturnStateService::getInstance($orderReturn)->changeStatus(ReturnStateService::REFUNDED);

        $this->assertSame(ReturnStateService::REFUNDED, $orderReturn->fresh()->status);
        $this->assertSame(StateMachineService::CANCELLED, $order->fresh()->status, 'a cancelled order stays cancelled');
    }

    /**
     * @param  string  $orderStatus
     * @return array{0: Order, 1: OrderReturn}
     */
    private function makeOrderReturn(string $orderStatus): array
    {
        $customer = Customer::query()->create([
            'email'             => 'rma_'.uniqid().'@example.com',
            'name'              => 'After Sale Tester',
            'customer_group_id' => 1,
            'password'          => 'x',
            'active'            => true,
        ]);

        $order = Order::query()->create([
            'customer_id'            => $customer->id,
            'customer_group_id'      => 1,
            'number'                 => 'RMA-'.uniqid(),
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => 100.0,
            'status'                 => $orderStatus,
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

        $orderReturn = OrderReturn::query()->create([
            'customer_id'   => $customer->id,
            'order_id'      => $order->id,
            'order_item_id' => 0,
            'product_id'    => 0,
            'number'        => 'RMA-'.uniqid(),
            'order_number'  => $order->number,
            'product_name'  => 'Test Product',
            'product_sku'   => 'TEST-SKU',
            'opened'        => 1,
            'quantity'      => 1,
            'comment'       => '',
            'status'        => ReturnStateService::PENDING,
        ]);

        return [$order->fresh(), $orderReturn->fresh()];
    }
}
