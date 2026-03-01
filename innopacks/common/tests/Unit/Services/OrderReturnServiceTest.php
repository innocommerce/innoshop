<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\ReturnStateService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OrderReturnServiceTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ReturnStateService::class);
    }

    #[Test]
    public function test_service_has_all_required_status_constants(): void
    {
        $this->assertTrue($this->reflection->hasConstant('CREATED'));
        $this->assertTrue($this->reflection->hasConstant('PENDING'));
        $this->assertTrue($this->reflection->hasConstant('REFUNDED'));
        $this->assertTrue($this->reflection->hasConstant('RETURNED'));
        $this->assertTrue($this->reflection->hasConstant('CANCELLED'));

        $this->assertEquals('created', ReturnStateService::CREATED);
        $this->assertEquals('pending', ReturnStateService::PENDING);
        $this->assertEquals('refunded', ReturnStateService::REFUNDED);
        $this->assertEquals('returned', ReturnStateService::RETURNED);
        $this->assertEquals('cancelled', ReturnStateService::CANCELLED);
    }

    #[Test]
    public function test_order_status_array_contains_all_statuses(): void
    {
        $expectedStatuses = [
            ReturnStateService::CREATED,
            ReturnStateService::PENDING,
            ReturnStateService::REFUNDED,
            ReturnStateService::RETURNED,
            ReturnStateService::CANCELLED,
        ];

        $this->assertEquals($expectedStatuses, ReturnStateService::ORDER_STATUS);
    }

    #[Test]
    public function test_machines_constant_defines_valid_transitions(): void
    {
        $machines = ReturnStateService::MACHINES;

        // CREATED can only go to PENDING
        $this->assertArrayHasKey(ReturnStateService::CREATED, $machines);
        $this->assertArrayHasKey(ReturnStateService::PENDING, $machines[ReturnStateService::CREATED]);

        // PENDING can go to REFUNDED or CANCELLED
        $this->assertArrayHasKey(ReturnStateService::PENDING, $machines);
        $this->assertArrayHasKey(ReturnStateService::REFUNDED, $machines[ReturnStateService::PENDING]);
        $this->assertArrayHasKey(ReturnStateService::CANCELLED, $machines[ReturnStateService::PENDING]);

        // REFUNDED can go to RETURNED
        $this->assertArrayHasKey(ReturnStateService::REFUNDED, $machines);
        $this->assertArrayHasKey(ReturnStateService::RETURNED, $machines[ReturnStateService::REFUNDED]);
    }

    #[Test]
    public function test_created_to_pending_triggers_correct_functions(): void
    {
        $machines  = ReturnStateService::MACHINES;
        $functions = $machines[ReturnStateService::CREATED][ReturnStateService::PENDING];

        $this->assertContains('updateStatus', $functions);
        $this->assertContains('addHistory', $functions);
        $this->assertContains('notifyNewOrder', $functions);
    }

    #[Test]
    public function test_pending_to_refunded_triggers_correct_functions(): void
    {
        $machines  = ReturnStateService::MACHINES;
        $functions = $machines[ReturnStateService::PENDING][ReturnStateService::REFUNDED];

        $this->assertContains('updateStatus', $functions);
        $this->assertContains('addHistory', $functions);
        $this->assertContains('notifyUpdateOrder', $functions);
    }

    #[Test]
    public function test_pending_to_cancelled_triggers_correct_functions(): void
    {
        $machines  = ReturnStateService::MACHINES;
        $functions = $machines[ReturnStateService::PENDING][ReturnStateService::CANCELLED];

        $this->assertContains('updateStatus', $functions);
        $this->assertContains('addHistory', $functions);
        $this->assertContains('notifyUpdateOrder', $functions);
    }

    #[Test]
    public function test_refunded_to_returned_triggers_correct_functions(): void
    {
        $machines  = ReturnStateService::MACHINES;
        $functions = $machines[ReturnStateService::REFUNDED][ReturnStateService::RETURNED];

        $this->assertContains('updateStatus', $functions);
        $this->assertContains('addHistory', $functions);
        $this->assertContains('notifyUpdateOrder', $functions);
    }

    #[Test]
    public function test_get_valid_statuses_returns_correct_statuses(): void
    {
        $validStatuses = ReturnStateService::getValidStatuses();

        $this->assertCount(2, $validStatuses);
        $this->assertContains(ReturnStateService::REFUNDED, $validStatuses);
        $this->assertContains(ReturnStateService::RETURNED, $validStatuses);

        // Should not contain these
        $this->assertNotContains(ReturnStateService::CREATED, $validStatuses);
        $this->assertNotContains(ReturnStateService::PENDING, $validStatuses);
        $this->assertNotContains(ReturnStateService::CANCELLED, $validStatuses);
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
    }

    #[Test]
    public function test_returned_status_has_no_further_transitions(): void
    {
        $machines = ReturnStateService::MACHINES;

        $this->assertArrayNotHasKey(ReturnStateService::RETURNED, $machines);
    }

    #[Test]
    public function test_cancelled_status_has_no_further_transitions(): void
    {
        $machines = ReturnStateService::MACHINES;

        $this->assertArrayNotHasKey(ReturnStateService::CANCELLED, $machines);
    }

    #[Test]
    public function test_return_flow_follows_correct_sequence(): void
    {
        $machines = ReturnStateService::MACHINES;

        // Verify the complete return flow: CREATED -> PENDING -> REFUNDED -> RETURNED
        $this->assertArrayHasKey(ReturnStateService::PENDING, $machines[ReturnStateService::CREATED]);
        $this->assertArrayHasKey(ReturnStateService::REFUNDED, $machines[ReturnStateService::PENDING]);
        $this->assertArrayHasKey(ReturnStateService::RETURNED, $machines[ReturnStateService::REFUNDED]);
    }

    #[Test]
    public function test_cannot_skip_pending_status(): void
    {
        $machines = ReturnStateService::MACHINES;

        // CREATED should not be able to go directly to REFUNDED or RETURNED
        $this->assertArrayNotHasKey(ReturnStateService::REFUNDED, $machines[ReturnStateService::CREATED] ?? []);
        $this->assertArrayNotHasKey(ReturnStateService::RETURNED, $machines[ReturnStateService::CREATED] ?? []);
    }
}
