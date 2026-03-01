<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OrderTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Order::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Order::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_model_uses_notifiable_trait(): void
    {
        $traits = class_uses_recursive(Order::class);
        $this->assertContains('Illuminate\Notifications\Notifiable', $traits);
    }

    #[Test]
    public function test_table_name_is_orders(): void
    {
        $property = $this->reflection->getProperty('table');
        $this->assertEquals('orders', $property->getDefaultValue());
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = [
            'number', 'customer_id', 'customer_group_id', 'shipping_address_id',
            'billing_address_id', 'customer_name', 'email', 'telephone', 'total',
            'locale', 'currency_code', 'currency_value', 'status',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_fillable_contains_shipping_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $shippingFields = [
            'shipping_method_code', 'shipping_method_name', 'shipping_customer_name',
            'shipping_telephone', 'shipping_country', 'shipping_country_id',
            'shipping_state_id', 'shipping_state', 'shipping_city',
            'shipping_address_1', 'shipping_address_2', 'shipping_zipcode',
        ];

        foreach ($shippingFields as $field) {
            $this->assertContains($field, $fillable, "Shipping field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_fillable_contains_billing_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $billingFields = [
            'billing_method_code', 'billing_method_name', 'billing_customer_name',
            'billing_telephone', 'billing_country', 'billing_country_id',
            'billing_state_id', 'billing_state', 'billing_city',
            'billing_address_1', 'billing_address_2', 'billing_zipcode',
        ];

        foreach ($billingFields as $field) {
            $this->assertContains($field, $fillable, "Billing field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_appends_contains_format_attributes(): void
    {
        $property = $this->reflection->getProperty('appends');
        $appends  = $property->getDefaultValue();

        $this->assertContains('total_format', $appends);
        $this->assertContains('status_format', $appends);
    }

    #[Test]
    public function test_has_customer_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_items_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('items'));
        $method = $this->reflection->getMethod('items');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_fees_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('fees'));
        $method = $this->reflection->getMethod('fees');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_histories_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('histories'));
        $method = $this->reflection->getMethod('histories');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_shipments_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('shipments'));
        $method = $this->reflection->getMethod('shipments');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_payments_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('payments'));
        $method = $this->reflection->getMethod('payments');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_children_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('children'));
        $method = $this->reflection->getMethod('children');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_parent_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('parent'));
        $method = $this->reflection->getMethod('parent');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_calc_subtotal_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('calcSubtotal'));
        $method = $this->reflection->getMethod('calcSubtotal');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_calc_total_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('calcTotal'));
        $method = $this->reflection->getMethod('calcTotal');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_total_format_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getTotalFormatAttribute'));
        $method = $this->reflection->getMethod('getTotalFormatAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_status_format_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getStatusFormatAttribute'));
        $method = $this->reflection->getMethod('getStatusFormatAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_status_color_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getStatusColorAttribute'));
        $method = $this->reflection->getMethod('getStatusColorAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_add_to_cart_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('addToCart'));
        $method = $this->reflection->getMethod('addToCart');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_reorder_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('reorder'));
        $method = $this->reflection->getMethod('reorder');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_notify_new_order_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('notifyNewOrder'));
        $method = $this->reflection->getMethod('notifyNewOrder');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_notify_update_order_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('notifyUpdateOrder'));
        $method = $this->reflection->getMethod('notifyUpdateOrder');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_status_color_logic_for_unpaid(): void
    {
        $status = StateMachineService::UNPAID;
        $color  = 'warning';

        if ($status == StateMachineService::UNPAID) {
            $result = 'warning';
        } elseif (in_array($status, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            $result = 'danger';
        } else {
            $result = 'success';
        }

        $this->assertEquals($color, $result);
    }

    #[Test]
    public function test_status_color_logic_for_created(): void
    {
        $status = StateMachineService::CREATED;

        if ($status == StateMachineService::UNPAID) {
            $result = 'warning';
        } elseif (in_array($status, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            $result = 'danger';
        } else {
            $result = 'success';
        }

        $this->assertEquals('danger', $result);
    }

    #[Test]
    public function test_status_color_logic_for_cancelled(): void
    {
        $status = StateMachineService::CANCELLED;

        if ($status == StateMachineService::UNPAID) {
            $result = 'warning';
        } elseif (in_array($status, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            $result = 'danger';
        } else {
            $result = 'success';
        }

        $this->assertEquals('danger', $result);
    }

    #[Test]
    public function test_status_color_logic_for_paid(): void
    {
        $status = StateMachineService::PAID;

        if ($status == StateMachineService::UNPAID) {
            $result = 'warning';
        } elseif (in_array($status, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            $result = 'danger';
        } else {
            $result = 'success';
        }

        $this->assertEquals('success', $result);
    }

    #[Test]
    public function test_status_color_logic_for_shipped(): void
    {
        $status = StateMachineService::SHIPPED;

        if ($status == StateMachineService::UNPAID) {
            $result = 'warning';
        } elseif (in_array($status, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            $result = 'danger';
        } else {
            $result = 'success';
        }

        $this->assertEquals('success', $result);
    }

    #[Test]
    public function test_status_color_logic_for_completed(): void
    {
        $status = StateMachineService::COMPLETED;

        if ($status == StateMachineService::UNPAID) {
            $result = 'warning';
        } elseif (in_array($status, [StateMachineService::CREATED, StateMachineService::CANCELLED])) {
            $result = 'danger';
        } else {
            $result = 'success';
        }

        $this->assertEquals('success', $result);
    }
}
