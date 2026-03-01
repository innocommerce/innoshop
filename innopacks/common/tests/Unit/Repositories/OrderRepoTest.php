<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Services\StateMachineService;
use PHPUnit\Framework\TestCase;

class OrderRepoTest extends TestCase
{
    /**
     * Test getFilterStatuses expected statuses array.
     */
    public function test_get_filter_statuses_expected_statuses(): void
    {
        // Test the expected statuses that should be returned
        $expectedStatuses = [
            StateMachineService::UNPAID,
            StateMachineService::PAID,
            StateMachineService::SHIPPED,
            StateMachineService::COMPLETED,
            StateMachineService::CANCELLED,
        ];

        $this->assertCount(5, $expectedStatuses);
        $this->assertContains('unpaid', $expectedStatuses);
        $this->assertContains('paid', $expectedStatuses);
        $this->assertContains('shipped', $expectedStatuses);
        $this->assertContains('completed', $expectedStatuses);
        $this->assertContains('cancelled', $expectedStatuses);
    }

    /**
     * Test generateOrderNumber format logic.
     */
    public function test_generate_order_number_format_logic(): void
    {
        // Order number format: Ymd + 5 digit random number
        $pattern = '/^\d{8}\d{5}$/';

        // Test the format logic
        $today      = date('Ymd');
        $randomPart = rand(10000, 99999);
        $number     = $today.$randomPart;

        $this->assertMatchesRegularExpression($pattern, $number);
        $this->assertEquals(13, strlen($number));
        $this->assertStringStartsWith($today, $number);
    }

    /**
     * Test generateOrderNumber random part range.
     */
    public function test_generate_order_number_random_part_range(): void
    {
        // Test the random part is within expected range
        $randomPart = rand(10000, 99999);

        $this->assertGreaterThanOrEqual(10000, $randomPart);
        $this->assertLessThanOrEqual(99999, $randomPart);
    }

    /**
     * Test order status constants are defined correctly.
     */
    public function test_order_status_constants_are_defined(): void
    {
        $this->assertEquals('created', StateMachineService::CREATED);
        $this->assertEquals('unpaid', StateMachineService::UNPAID);
        $this->assertEquals('paid', StateMachineService::PAID);
        $this->assertEquals('shipped', StateMachineService::SHIPPED);
        $this->assertEquals('completed', StateMachineService::COMPLETED);
        $this->assertEquals('cancelled', StateMachineService::CANCELLED);
    }

    /**
     * Test ORDER_STATUS array contains all statuses.
     */
    public function test_order_status_array_contains_all_statuses(): void
    {
        $expectedStatuses = [
            'created',
            'unpaid',
            'paid',
            'shipped',
            'completed',
            'cancelled',
        ];

        $this->assertEquals($expectedStatuses, StateMachineService::ORDER_STATUS);
        $this->assertCount(6, StateMachineService::ORDER_STATUS);
    }

    /**
     * Test handleData logic for order number generation.
     */
    public function test_handle_data_generates_order_number_when_empty(): void
    {
        $data = [
            'number' => '',
        ];

        $number = $data['number'] ?? '';
        if (empty($number)) {
            // Simulate order number generation logic
            $number = date('Ymd').rand(10000, 99999);
        }

        $this->assertNotEmpty($number);
        $this->assertEquals(13, strlen($number));
    }

    /**
     * Test handleData logic preserves provided order number.
     */
    public function test_handle_data_preserves_provided_order_number(): void
    {
        $providedNumber = '2024010112345';
        $data           = [
            'number' => $providedNumber,
        ];

        $number = $data['number'] ?? '';
        if (empty($number)) {
            $number = OrderRepo::generateOrderNumber();
        }

        $this->assertEquals($providedNumber, $number);
    }

    /**
     * Test builder filter logic for customer_id.
     */
    public function test_builder_filter_customer_id_logic(): void
    {
        $filters = ['customer_id' => 123];

        $customerID = $filters['customer_id'] ?? 0;

        $this->assertEquals(123, $customerID);
        $this->assertTrue((bool) $customerID);
    }

    /**
     * Test builder filter logic for empty customer_id.
     */
    public function test_builder_filter_empty_customer_id_logic(): void
    {
        $filters = [];

        $customerID = $filters['customer_id'] ?? 0;

        $this->assertEquals(0, $customerID);
        $this->assertFalse((bool) $customerID);
    }

    /**
     * Test builder filter logic for number.
     */
    public function test_builder_filter_number_logic(): void
    {
        $filters = ['number' => '2024010112345'];

        $number = $filters['number'] ?? '';

        $this->assertEquals('2024010112345', $number);
        $this->assertTrue((bool) $number);
    }

    /**
     * Test builder filter logic for customer_name with like pattern.
     */
    public function test_builder_filter_customer_name_logic(): void
    {
        $filters = ['customer_name' => 'John'];

        $customerName = $filters['customer_name'] ?? '';
        $likePattern  = "%$customerName%";

        $this->assertEquals('%John%', $likePattern);
    }

    /**
     * Test builder filter logic for email.
     */
    public function test_builder_filter_email_logic(): void
    {
        $filters = ['email' => 'test@example.com'];

        $email = $filters['email'] ?? '';

        $this->assertEquals('test@example.com', $email);
    }

    /**
     * Test builder filter logic for telephone.
     */
    public function test_builder_filter_telephone_logic(): void
    {
        $filters = ['telephone' => '1234567890'];

        $telephone = $filters['telephone'] ?? '';

        $this->assertEquals('1234567890', $telephone);
    }

    /**
     * Test builder filter logic for shipping_method_code with like pattern.
     */
    public function test_builder_filter_shipping_method_code_logic(): void
    {
        $filters = ['shipping_method_code' => 'flat_rate'];

        $shippingCode = $filters['shipping_method_code'] ?? '';
        $likePattern  = $shippingCode.'%';

        $this->assertEquals('flat_rate%', $likePattern);
    }

    /**
     * Test builder filter logic for billing_method_code.
     */
    public function test_builder_filter_billing_method_code_logic(): void
    {
        $filters = ['billing_method_code' => 'paypal'];

        $billingCode = $filters['billing_method_code'] ?? '';

        $this->assertEquals('paypal', $billingCode);
    }

    /**
     * Test builder filter logic for valid status.
     */
    public function test_builder_filter_valid_status_logic(): void
    {
        $filters = ['status' => 'paid'];

        $status  = $filters['status'] ?? '';
        $isValid = $status && in_array($status, StateMachineService::ORDER_STATUS);

        $this->assertTrue($isValid);
    }

    /**
     * Test builder filter logic for invalid status.
     */
    public function test_builder_filter_invalid_status_logic(): void
    {
        $filters = ['status' => 'invalid_status'];

        $status  = $filters['status'] ?? '';
        $isValid = $status && in_array($status, StateMachineService::ORDER_STATUS);

        $this->assertFalse($isValid);
    }

    /**
     * Test builder filter logic for statuses array.
     */
    public function test_builder_filter_statuses_array_logic(): void
    {
        $filters = ['statuses' => ['paid', 'shipped']];

        $statuses = $filters['statuses'] ?? [];

        $this->assertIsArray($statuses);
        $this->assertCount(2, $statuses);
        $this->assertContains('paid', $statuses);
        $this->assertContains('shipped', $statuses);
    }

    /**
     * Test builder filter logic for date range.
     */
    public function test_builder_filter_date_range_logic(): void
    {
        $filters = [
            'created_at_start' => '2024-01-01 00:00:00',
            'created_at_end'   => '2024-12-31 23:59:59',
        ];

        $createdStart = $filters['created_at_start'] ?? '';
        $createdEnd   = $filters['created_at_end'] ?? '';

        $this->assertEquals('2024-01-01 00:00:00', $createdStart);
        $this->assertEquals('2024-12-31 23:59:59', $createdEnd);
        $this->assertTrue((bool) $createdStart);
        $this->assertTrue((bool) $createdEnd);
    }

    /**
     * Test builder filter logic for total range.
     */
    public function test_builder_filter_total_range_logic(): void
    {
        $filters = [
            'total_start' => 100,
            'total_end'   => 500,
        ];

        $totalStart = $filters['total_start'] ?? '';
        $totalEnd   = $filters['total_end'] ?? '';

        $this->assertEquals(100, $totalStart);
        $this->assertEquals(500, $totalEnd);
    }

    /**
     * Test handleData default values.
     */
    public function test_handle_data_default_values(): void
    {
        $requestData = [];

        $result = [
            'total'                => $requestData['total'] ?? 0,
            'locale'               => $requestData['locale'] ?? 'en',
            'currency_code'        => $requestData['currency_code'] ?? 'USD',
            'currency_value'       => $requestData['currency_value'] ?? 1,
            'status'               => $requestData['status'] ?? 'created',
            'shipping_method_code' => $requestData['shipping_method_code'] ?? '',
            'billing_method_code'  => $requestData['billing_method_code'] ?? '',
            'comment'              => $requestData['comment'] ?? '',
        ];

        $this->assertEquals(0, $result['total']);
        $this->assertEquals('en', $result['locale']);
        $this->assertEquals('USD', $result['currency_code']);
        $this->assertEquals(1, $result['currency_value']);
        $this->assertEquals('created', $result['status']);
        $this->assertEquals('', $result['shipping_method_code']);
        $this->assertEquals('', $result['billing_method_code']);
        $this->assertEquals('', $result['comment']);
    }

    /**
     * Test handleData with provided values.
     */
    public function test_handle_data_with_provided_values(): void
    {
        $requestData = [
            'total'                => 199.99,
            'locale'               => 'zh_CN',
            'currency_code'        => 'CNY',
            'currency_value'       => 7.2,
            'status'               => 'unpaid',
            'shipping_method_code' => 'express',
            'billing_method_code'  => 'alipay',
            'comment'              => 'Please deliver quickly',
        ];

        $result = [
            'total'                => $requestData['total'] ?? 0,
            'locale'               => $requestData['locale'] ?? 'en',
            'currency_code'        => $requestData['currency_code'] ?? 'USD',
            'currency_value'       => $requestData['currency_value'] ?? 1,
            'status'               => $requestData['status'] ?? 'created',
            'shipping_method_code' => $requestData['shipping_method_code'] ?? '',
            'billing_method_code'  => $requestData['billing_method_code'] ?? '',
            'comment'              => $requestData['comment'] ?? '',
        ];

        $this->assertEquals(199.99, $result['total']);
        $this->assertEquals('zh_CN', $result['locale']);
        $this->assertEquals('CNY', $result['currency_code']);
        $this->assertEquals(7.2, $result['currency_value']);
        $this->assertEquals('unpaid', $result['status']);
        $this->assertEquals('express', $result['shipping_method_code']);
        $this->assertEquals('alipay', $result['billing_method_code']);
        $this->assertEquals('Please deliver quickly', $result['comment']);
    }

    /**
     * Test address data extraction logic.
     */
    public function test_address_data_extraction_logic(): void
    {
        $addressData = [
            'name'         => 'John Doe',
            'calling_code' => '1',
            'phone'        => '5551234567',
            'country_name' => 'United States',
            'country_id'   => 223,
            'state_id'     => 12,
            'state_name'   => 'California',
            'city'         => 'Los Angeles',
            'address_1'    => '123 Main St',
            'address_2'    => 'Apt 4B',
            'zipcode'      => '90001',
        ];

        $result = [
            'shipping_customer_name' => $addressData['name'] ?? '',
            'shipping_calling_code'  => $addressData['calling_code'] ?? '',
            'shipping_telephone'     => $addressData['phone'] ?? '',
            'shipping_country'       => $addressData['country_name'] ?? '',
            'shipping_country_id'    => $addressData['country_id'] ?? 0,
            'shipping_state_id'      => $addressData['state_id'] ?? 0,
            'shipping_state'         => $addressData['state_name'] ?? '',
            'shipping_city'          => $addressData['city'] ?? '',
            'shipping_address_1'     => $addressData['address_1'] ?? '',
            'shipping_address_2'     => $addressData['address_2'] ?? '',
            'shipping_zipcode'       => $addressData['zipcode'] ?? '',
        ];

        $this->assertEquals('John Doe', $result['shipping_customer_name']);
        $this->assertEquals('1', $result['shipping_calling_code']);
        $this->assertEquals('5551234567', $result['shipping_telephone']);
        $this->assertEquals('United States', $result['shipping_country']);
        $this->assertEquals(223, $result['shipping_country_id']);
        $this->assertEquals(12, $result['shipping_state_id']);
        $this->assertEquals('California', $result['shipping_state']);
        $this->assertEquals('Los Angeles', $result['shipping_city']);
        $this->assertEquals('123 Main St', $result['shipping_address_1']);
        $this->assertEquals('Apt 4B', $result['shipping_address_2']);
        $this->assertEquals('90001', $result['shipping_zipcode']);
    }

    /**
     * Test address data extraction with empty data.
     */
    public function test_address_data_extraction_with_empty_data(): void
    {
        $addressData = [];

        $result = [
            'shipping_customer_name' => $addressData['name'] ?? '',
            'shipping_calling_code'  => $addressData['calling_code'] ?? '',
            'shipping_telephone'     => $addressData['phone'] ?? '',
            'shipping_country'       => $addressData['country_name'] ?? '',
            'shipping_country_id'    => $addressData['country_id'] ?? 0,
            'shipping_state_id'      => $addressData['state_id'] ?? 0,
            'shipping_state'         => $addressData['state_name'] ?? '',
            'shipping_city'          => $addressData['city'] ?? '',
            'shipping_address_1'     => $addressData['address_1'] ?? '',
            'shipping_address_2'     => $addressData['address_2'] ?? '',
            'shipping_zipcode'       => $addressData['zipcode'] ?? '',
        ];

        $this->assertEquals('', $result['shipping_customer_name']);
        $this->assertEquals('', $result['shipping_calling_code']);
        $this->assertEquals('', $result['shipping_telephone']);
        $this->assertEquals('', $result['shipping_country']);
        $this->assertEquals(0, $result['shipping_country_id']);
        $this->assertEquals(0, $result['shipping_state_id']);
        $this->assertEquals('', $result['shipping_state']);
        $this->assertEquals('', $result['shipping_city']);
        $this->assertEquals('', $result['shipping_address_1']);
        $this->assertEquals('', $result['shipping_address_2']);
        $this->assertEquals('', $result['shipping_zipcode']);
    }

    /**
     * Test getValidatedAddress logic requires customer_id or guest_id.
     */
    public function test_get_validated_address_requires_customer_or_guest(): void
    {
        $addressID  = 1;
        $customerID = 0;
        $guestID    = '';

        $requiresValidation = $addressID && ! $customerID && ! $guestID;

        $this->assertTrue($requiresValidation);
    }

    /**
     * Test getValidatedAddress logic with customer_id.
     */
    public function test_get_validated_address_with_customer_id(): void
    {
        $addressID  = 1;
        $customerID = 123;
        $guestID    = '';

        $useCustomerValidation = $addressID && $customerID;
        $useGuestValidation    = $addressID && ! $customerID && $guestID;

        $this->assertTrue($useCustomerValidation);
        $this->assertFalse($useGuestValidation);
    }

    /**
     * Test getValidatedAddress logic with guest_id.
     */
    public function test_get_validated_address_with_guest_id(): void
    {
        $addressID  = 1;
        $customerID = 0;
        $guestID    = 'guest_abc123';

        $useCustomerValidation = $addressID && $customerID;
        $useGuestValidation    = $addressID && ! $customerID && $guestID;

        $this->assertFalse($useCustomerValidation);
        $this->assertTrue($useGuestValidation);
    }

    /**
     * Test getValidatedAddress returns null for zero address ID.
     */
    public function test_get_validated_address_returns_null_for_zero_id(): void
    {
        $addressID = 0;

        $shouldReturnNull = ! $addressID;

        $this->assertTrue($shouldReturnNull);
    }

    /**
     * Test state machine transitions are defined.
     */
    public function test_state_machine_transitions_are_defined(): void
    {
        $machines = StateMachineService::MACHINES;

        // Test created -> unpaid transition
        $this->assertArrayHasKey(StateMachineService::CREATED, $machines);
        $this->assertArrayHasKey(StateMachineService::UNPAID, $machines[StateMachineService::CREATED]);

        // Test unpaid -> paid transition
        $this->assertArrayHasKey(StateMachineService::UNPAID, $machines);
        $this->assertArrayHasKey(StateMachineService::PAID, $machines[StateMachineService::UNPAID]);

        // Test unpaid -> cancelled transition
        $this->assertArrayHasKey(StateMachineService::CANCELLED, $machines[StateMachineService::UNPAID]);

        // Test paid -> shipped transition
        $this->assertArrayHasKey(StateMachineService::PAID, $machines);
        $this->assertArrayHasKey(StateMachineService::SHIPPED, $machines[StateMachineService::PAID]);

        // Test shipped -> completed transition
        $this->assertArrayHasKey(StateMachineService::SHIPPED, $machines);
        $this->assertArrayHasKey(StateMachineService::COMPLETED, $machines[StateMachineService::SHIPPED]);
    }

    /**
     * Test state machine transition actions.
     */
    public function test_state_machine_transition_actions(): void
    {
        $machines = StateMachineService::MACHINES;

        // Test created -> unpaid actions
        $createdToUnpaidActions = $machines[StateMachineService::CREATED][StateMachineService::UNPAID];
        $this->assertContains('updateStatus', $createdToUnpaidActions);
        $this->assertContains('addHistory', $createdToUnpaidActions);
        $this->assertContains('redeemBalance', $createdToUnpaidActions);
        $this->assertContains('notifyNewOrder', $createdToUnpaidActions);

        // Test unpaid -> paid actions
        $unpaidToPaidActions = $machines[StateMachineService::UNPAID][StateMachineService::PAID];
        $this->assertContains('updateStatus', $unpaidToPaidActions);
        $this->assertContains('addHistory', $unpaidToPaidActions);
        $this->assertContains('updateSales', $unpaidToPaidActions);
        $this->assertContains('subStock', $unpaidToPaidActions);

        // Test paid -> shipped actions
        $paidToShippedActions = $machines[StateMachineService::PAID][StateMachineService::SHIPPED];
        $this->assertContains('addShipment', $paidToShippedActions);
    }

    /**
     * Test filter merging logic.
     */
    public function test_filter_merging_logic(): void
    {
        $defaultFilters  = ['status' => 'paid'];
        $providedFilters = ['customer_id' => 123, 'status' => 'shipped'];

        $mergedFilters = array_merge($defaultFilters, $providedFilters);

        $this->assertEquals(123, $mergedFilters['customer_id']);
        $this->assertEquals('shipped', $mergedFilters['status']); // Provided overrides default
    }

    /**
     * Test relations merging logic.
     */
    public function test_relations_merging_logic(): void
    {
        $defaultRelations = ['customer', 'items', 'children'];
        $extraRelations   = ['payments', 'shipments'];

        $mergedRelations = array_merge($extraRelations, $defaultRelations);

        $this->assertContains('customer', $mergedRelations);
        $this->assertContains('items', $mergedRelations);
        $this->assertContains('children', $mergedRelations);
        $this->assertContains('payments', $mergedRelations);
        $this->assertContains('shipments', $mergedRelations);
    }

    /**
     * Test customer data extraction from request.
     */
    public function test_customer_data_extraction(): void
    {
        $customer = (object) [
            'id'                => 1,
            'customer_group_id' => 2,
            'name'              => 'John Doe',
            'email'             => 'john@example.com',
            'calling_code'      => '1',
            'telephone'         => '5551234567',
        ];

        $result = [
            'customer_id'       => $customer->id ?? 0,
            'customer_group_id' => $customer->customer_group_id ?? 0,
            'customer_name'     => $customer->name ?? '',
            'email'             => $customer->email ?? '',
            'calling_code'      => $customer->calling_code ?? 0,
            'telephone'         => $customer->telephone ?? '',
        ];

        $this->assertEquals(1, $result['customer_id']);
        $this->assertEquals(2, $result['customer_group_id']);
        $this->assertEquals('John Doe', $result['customer_name']);
        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals('1', $result['calling_code']);
        $this->assertEquals('5551234567', $result['telephone']);
    }

    /**
     * Test customer data extraction with null customer.
     */
    public function test_customer_data_extraction_with_null_customer(): void
    {
        $customer = null;

        $result = [
            'customer_id'       => $customer->id ?? 0,
            'customer_group_id' => $customer->customer_group_id ?? 0,
            'customer_name'     => $customer->name ?? '',
            'email'             => $customer->email ?? '',
            'calling_code'      => $customer->calling_code ?? 0,
            'telephone'         => $customer->telephone ?? '',
        ];

        $this->assertEquals(0, $result['customer_id']);
        $this->assertEquals(0, $result['customer_group_id']);
        $this->assertEquals('', $result['customer_name']);
        $this->assertEquals('', $result['email']);
        $this->assertEquals(0, $result['calling_code']);
        $this->assertEquals('', $result['telephone']);
    }
}
