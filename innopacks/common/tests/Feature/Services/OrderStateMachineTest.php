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
use Illuminate\Support\Facades\Cache;
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

        Cache::put("sku:{$sku->code}", $sku->fresh(), 300);
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::CANCELLED);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, (int) $sku->fresh()->quantity, 'paid -> cancelled must restore stock');
        $this->assertFalse(Cache::has("sku:{$sku->code}"));
    }

    public function test_paid_order_can_be_refunded(): void
    {
        [$order] = $this->setupOrder(initialStock: 10, orderQty: 1);

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
        StateMachineService::getInstance($order->fresh())->changeStatus(StateMachineService::REFUNDED);

        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame(
            1,
            Order\History::query()->where('order_id', $order->id)->where('status', StateMachineService::REFUNDED)->count(),
            'the refund must be recorded in the order history'
        );
    }

    public function test_refunded_status_has_a_readable_name(): void
    {
        $statuses = collect(StateMachineService::getAllStatuses())->keyBy('status');

        $this->assertTrue($statuses->has(StateMachineService::REFUNDED));
        $this->assertNotSame(
            'panel/order.refunded',
            $statuses->get(StateMachineService::REFUNDED)['name'],
            'a missing translation must not leak the raw key into the panel'
        );
    }

    public function test_insufficient_stock_rolls_back_the_paid_transition(): void
    {
        [$order, $sku] = $this->setupOrder(initialStock: 1, orderQty: 2);

        try {
            StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);
            $this->fail('Payment succeeded with insufficient stock.');
        } catch (\Exception $e) {
            $this->assertSame(trans('front/common.stock_not_enough'), $e->getMessage());
        }

        $this->assertSame(StateMachineService::UNPAID, $order->fresh()->status);
        $this->assertSame(1, (int) $sku->fresh()->quantity);
        $this->assertSame(0, Order\History::query()->where('order_id', $order->id)->where('status', StateMachineService::PAID)->count());
    }

    public function test_two_orders_cannot_consume_the_same_last_unit(): void
    {
        [$firstOrder, $sku] = $this->setupOrder(initialStock: 1, orderQty: 1);
        [$secondOrder]      = $this->setupOrder(initialStock: 1, orderQty: 1, sku: $sku);

        StateMachineService::getInstance($firstOrder)->changeStatus(StateMachineService::PAID);

        $this->expectException(\Exception::class);
        try {
            StateMachineService::getInstance($secondOrder)->changeStatus(StateMachineService::PAID);
        } finally {
            $this->assertSame(StateMachineService::UNPAID, $secondOrder->fresh()->status);
            $this->assertSame(0, (int) $sku->fresh()->quantity);
        }
    }

    public function test_stale_duplicate_paid_transitions_deduct_stock_once(): void
    {
        [$order, $sku] = $this->setupOrder(initialStock: 5, orderQty: 1);
        $first         = StateMachineService::getInstance($order->fresh());
        $duplicate     = StateMachineService::getInstance($order->fresh());

        $first->changeStatus(StateMachineService::PAID);
        $duplicate->changeStatus(StateMachineService::PAID);

        $this->assertSame(4, (int) $sku->fresh()->quantity);
        $this->assertSame(1, Order\History::query()->where('order_id', $order->id)->where('status', StateMachineService::PAID)->count());
    }

    public function test_stale_duplicate_cancellations_restore_stock_once(): void
    {
        [$order, $sku] = $this->setupOrder(initialStock: 5, orderQty: 2);
        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);

        $first     = StateMachineService::getInstance($order->fresh());
        $duplicate = StateMachineService::getInstance($order->fresh());
        $first->changeStatus(StateMachineService::CANCELLED);
        $duplicate->changeStatus(StateMachineService::CANCELLED);

        $this->assertSame(5, (int) $sku->fresh()->quantity);
        $this->assertSame(1, Order\History::query()->where('order_id', $order->id)->where('status', StateMachineService::CANCELLED)->count());
    }

    public function test_configured_backorders_can_still_deduct_below_zero(): void
    {
        config()->set('inno.system.allow_out_of_stock', true);
        [$order, $sku] = $this->setupOrder(initialStock: 0, orderQty: 1);

        StateMachineService::getInstance($order)->changeStatus(StateMachineService::PAID);

        $this->assertSame(-1, (int) $sku->fresh()->quantity);
        $this->assertSame(StateMachineService::PAID, $order->fresh()->status);
    }

    /**
     * @return array{0: Order, 1: Sku}
     */
    private function setupOrder(int $initialStock, int $orderQty, ?Sku $sku = null): array
    {
        $customer = Customer::query()->create([
            'email'             => 'sm_'.$orderQty.'_'.uniqid().'@example.com',
            'name'              => 'State Machine Tester',
            'customer_group_id' => 1,
            'password'          => 'x',
            'active'            => true,
        ]);

        $sku ??= Sku::query()->create([
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
