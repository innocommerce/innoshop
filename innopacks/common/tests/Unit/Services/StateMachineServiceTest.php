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
use ReflectionClass;

class StateMachineServiceTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(StateMachineService::class);
    }

    #[Test]
    public function test_service_has_all_required_status_constants(): void
    {
        $this->assertTrue($this->reflection->hasConstant('CREATED'));
        $this->assertTrue($this->reflection->hasConstant('UNPAID'));
        $this->assertTrue($this->reflection->hasConstant('PAID'));
        $this->assertTrue($this->reflection->hasConstant('SHIPPED'));
        $this->assertTrue($this->reflection->hasConstant('COMPLETED'));
        $this->assertTrue($this->reflection->hasConstant('CANCELLED'));

        $this->assertEquals('created', StateMachineService::CREATED);
        $this->assertEquals('unpaid', StateMachineService::UNPAID);
        $this->assertEquals('paid', StateMachineService::PAID);
        $this->assertEquals('shipped', StateMachineService::SHIPPED);
        $this->assertEquals('completed', StateMachineService::COMPLETED);
        $this->assertEquals('cancelled', StateMachineService::CANCELLED);
    }

    #[Test]
    public function test_order_status_array_contains_all_statuses(): void
    {
        $expectedStatuses = [
            StateMachineService::CREATED,
            StateMachineService::UNPAID,
            StateMachineService::PAID,
            StateMachineService::SHIPPED,
            StateMachineService::COMPLETED,
            StateMachineService::CANCELLED,
        ];

        $this->assertEquals($expectedStatuses, StateMachineService::ORDER_STATUS);
    }

    #[Test]
    public function test_machines_constant_defines_valid_transitions(): void
    {
        $machines = StateMachineService::MACHINES;

        // CREATED can only go to UNPAID
        $this->assertArrayHasKey(StateMachineService::CREATED, $machines);
        $this->assertArrayHasKey(StateMachineService::UNPAID, $machines[StateMachineService::CREATED]);

        // UNPAID can go to PAID or CANCELLED
        $this->assertArrayHasKey(StateMachineService::UNPAID, $machines);
        $this->assertArrayHasKey(StateMachineService::PAID, $machines[StateMachineService::UNPAID]);
        $this->assertArrayHasKey(StateMachineService::CANCELLED, $machines[StateMachineService::UNPAID]);

        // PAID can go to SHIPPED, COMPLETED, or CANCELLED
        $this->assertArrayHasKey(StateMachineService::PAID, $machines);
        $this->assertArrayHasKey(StateMachineService::SHIPPED, $machines[StateMachineService::PAID]);
        $this->assertArrayHasKey(StateMachineService::COMPLETED, $machines[StateMachineService::PAID]);
        $this->assertArrayHasKey(StateMachineService::CANCELLED, $machines[StateMachineService::PAID]);

        // SHIPPED can only go to COMPLETED
        $this->assertArrayHasKey(StateMachineService::SHIPPED, $machines);
        $this->assertArrayHasKey(StateMachineService::COMPLETED, $machines[StateMachineService::SHIPPED]);
    }

    #[Test]
    public function test_created_to_unpaid_triggers_correct_functions(): void
    {
        $machines  = StateMachineService::MACHINES;
        $functions = $machines[StateMachineService::CREATED][StateMachineService::UNPAID];

        $this->assertContains('updateStatus', $functions);
        $this->assertContains('addHistory', $functions);
        $this->assertContains('redeemBalance', $functions);
        $this->assertContains('notifyNewOrder', $functions);
    }

    #[Test]
    public function test_unpaid_to_paid_triggers_correct_functions(): void
    {
        $machines  = StateMachineService::MACHINES;
        $functions = $machines[StateMachineService::UNPAID][StateMachineService::PAID];

        $this->assertContains('updateStatus', $functions);
        $this->assertContains('addHistory', $functions);
        $this->assertContains('updateSales', $functions);
        $this->assertContains('subStock', $functions);
        $this->assertContains('notifyUpdateOrder', $functions);
    }

    #[Test]
    public function test_unpaid_to_cancelled_triggers_balance_revoke(): void
    {
        $machines  = StateMachineService::MACHINES;
        $functions = $machines[StateMachineService::UNPAID][StateMachineService::CANCELLED];

        $this->assertContains('revokeBalance', $functions);
    }

    #[Test]
    public function test_paid_to_shipped_triggers_shipment_addition(): void
    {
        $machines  = StateMachineService::MACHINES;
        $functions = $machines[StateMachineService::PAID][StateMachineService::SHIPPED];

        $this->assertContains('addShipment', $functions);
    }

    #[Test]
    public function test_get_valid_statuses_returns_correct_statuses(): void
    {
        $validStatuses = StateMachineService::getValidStatuses();

        $this->assertCount(3, $validStatuses);
        $this->assertContains(StateMachineService::PAID, $validStatuses);
        $this->assertContains(StateMachineService::SHIPPED, $validStatuses);
        $this->assertContains(StateMachineService::COMPLETED, $validStatuses);

        // Should not contain these
        $this->assertNotContains(StateMachineService::CREATED, $validStatuses);
        $this->assertNotContains(StateMachineService::UNPAID, $validStatuses);
        $this->assertNotContains(StateMachineService::CANCELLED, $validStatuses);
    }

    #[Test]
    public function test_service_has_get_instance_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getInstance'));
        $method = $this->reflection->getMethod('getInstance');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_service_has_change_status_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('changeStatus'));
        $method = $this->reflection->getMethod('changeStatus');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_service_has_next_backend_statuses_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('nextBackendStatuses'));
        $method = $this->reflection->getMethod('nextBackendStatuses');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_service_has_fluent_setter_methods(): void
    {
        $this->assertTrue($this->reflection->hasMethod('setComment'));
        $this->assertTrue($this->reflection->hasMethod('setNotify'));
        $this->assertTrue($this->reflection->hasMethod('setShipment'));
        $this->assertTrue($this->reflection->hasMethod('setPayment'));
    }

    #[Test]
    public function test_completed_status_has_no_further_transitions(): void
    {
        $machines = StateMachineService::MACHINES;

        $this->assertArrayNotHasKey(StateMachineService::COMPLETED, $machines);
    }

    #[Test]
    public function test_cancelled_status_has_no_further_transitions(): void
    {
        $machines = StateMachineService::MACHINES;

        $this->assertArrayNotHasKey(StateMachineService::CANCELLED, $machines);
    }
}
