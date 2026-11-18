<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\StateMachineService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The order state machine had no "refunded" state, so an order whose money
 * was given back through the after-sale flow stayed "paid" forever.
 */
class OrderRefundedStateTest extends TestCase
{
    #[Test]
    public function test_refunded_status_constant_exists(): void
    {
        $this->assertTrue(defined(StateMachineService::class.'::REFUNDED'));
        $this->assertSame('refunded', StateMachineService::REFUNDED);
    }

    #[Test]
    public function test_refunded_is_part_of_the_order_statuses(): void
    {
        $this->assertContains(StateMachineService::REFUNDED, StateMachineService::ORDER_STATUS);
    }

    #[Test]
    public function test_paid_shipped_and_completed_can_transition_to_refunded(): void
    {
        $machines = StateMachineService::MACHINES;

        $this->assertArrayHasKey(StateMachineService::REFUNDED, $machines[StateMachineService::PAID]);
        $this->assertArrayHasKey(StateMachineService::REFUNDED, $machines[StateMachineService::SHIPPED]);
        $this->assertArrayHasKey(StateMachineService::REFUNDED, $machines[StateMachineService::COMPLETED]);
    }

    #[Test]
    public function test_unpaid_and_cancelled_orders_cannot_be_refunded(): void
    {
        $machines = StateMachineService::MACHINES;

        $this->assertArrayNotHasKey(StateMachineService::REFUNDED, $machines[StateMachineService::UNPAID]);
        $this->assertArrayNotHasKey(StateMachineService::REFUNDED, $machines[StateMachineService::CANCELLED] ?? []);
        $this->assertArrayNotHasKey(StateMachineService::REFUNDED, $machines[StateMachineService::CREATED] ?? []);
    }

    #[Test]
    public function test_refund_transition_uses_the_standard_action_helpers(): void
    {
        $machines = StateMachineService::MACHINES;

        foreach ([StateMachineService::PAID, StateMachineService::SHIPPED, StateMachineService::COMPLETED] as $status) {
            $functions = $machines[$status][StateMachineService::REFUNDED];

            $this->assertContains('updateStatus', $functions);
            $this->assertContains('addHistory', $functions);
            $this->assertContains('notifyUpdateOrder', $functions);
        }
    }

    #[Test]
    public function test_refunded_status_has_no_further_transitions(): void
    {
        $machines = StateMachineService::MACHINES;

        $this->assertArrayNotHasKey(StateMachineService::REFUNDED, $machines);
    }
}
