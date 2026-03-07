<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Item;
use Tests\TestCase;

class OrderReturnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register customer guard for testing
        Config::set('auth.providers.customer', [
            'driver' => 'eloquent',
            'model'  => Customer::class,
        ]);

        Config::set('auth.guards.customer', [
            'driver'   => 'session',
            'provider' => 'customer',
        ]);

        // Set hide_url_locale to true so routes don't have locale prefix
        Config::set('inno.system.hide_url_locale', true);

        // Ensure only one active locale exists (required for hide_url_locale to work)
        Locale::query()->update(['active' => false]);
        Locale::query()->where('code', 'en')->update(['active' => true]);
    }

    public function test_customer_cannot_return_more_than_ordered_quantity()
    {
        // 1. Create Customer
        $customer           = new Customer;
        $customer->name     = 'John Doe';
        $customer->email    = 'john.doe@example.com';
        $customer->password = Hash::make('password');
        $customer->active   = true;
        $customer->save();

        // 2. Create Order
        $order = Order::create([
            'customer_id'            => $customer->id,
            'customer_group_id'      => 1,
            'number'                 => 'ORD-001',
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => 100.0,
            'status'                 => 'completed',
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'John Doe',
            'email'                  => 'john.doe@example.com',
            'calling_code'           => '1',
            'telephone'              => '1234567890',
            'locale'                 => 'en',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'Test Agent',
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat Rate',
            'billing_method_code'    => 'cod',
            'billing_method_name'    => 'Cash On Delivery',
            'shipping_customer_name' => 'John Doe',
            'shipping_calling_code'  => '1',
            'shipping_telephone'     => '1234567890',
            'shipping_country'       => 'US',
            'shipping_country_id'    => 1,
            'shipping_state_id'      => 1,
            'shipping_state'         => 'New York',
            'shipping_city'          => 'New York',
            'shipping_address_1'     => '123 St',
            'shipping_address_2'     => '',
            'shipping_zipcode'       => '10001',
            'billing_customer_name'  => 'John Doe',
            'billing_calling_code'   => '1',
            'billing_telephone'      => '1234567890',
            'billing_country'        => 'US',
            'billing_country_id'     => 1,
            'billing_state_id'       => 1,
            'billing_state'          => 'New York',
            'billing_city'           => 'New York',
            'billing_address_1'      => '123 St',
            'billing_address_2'      => '',
            'billing_zipcode'        => '10001',
        ]);

        // 3. Create Order Item
        $item = Item::create([
            'order_id'      => $order->id,
            'order_number'  => $order->number,
            'product_id'    => 1,
            'product_sku'   => 'SKU-001',
            'variant_label' => '',
            'name'          => 'Test Product',
            'image'         => '',
            'quantity'      => 5,
            'price'         => 20.0,
            'total'         => 100.0,
            'item_type'     => 'product',
            'item_id'       => 1,
        ]);

        // Login as customer
        $this->actingAs($customer, 'customer');

        // 4. Return 3 (Valid)
        $response = $this->post(route('front.account.order_returns.store'), [
            'order_item_id' => $item->id,
            'quantity'      => 3,
            'opened'        => 1,
            'comment'       => 'Defective',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_returns', [
            'order_item_id' => $item->id,
            'quantity'      => 3,
        ]);

        // 5. Return 3 again (Invalid: 3+3 = 6 > 5)
        $response = $this->post(route('front.account.order_returns.store'), [
            'order_item_id' => $item->id,
            'quantity'      => 3,
            'opened'        => 1,
            'comment'       => 'Defective again',
        ]);

        $response->assertSessionHasErrors(['errors']);

        // 6. Return 2 (Valid: 3+2 = 5)
        $response = $this->post(route('front.account.order_returns.store'), [
            'order_item_id' => $item->id,
            'quantity'      => 2,
            'opened'        => 1,
            'comment'       => 'Returning the rest',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_returns', [
            'order_item_id' => $item->id,
            'quantity'      => 2,
            'comment'       => 'Returning the rest',
        ]);
    }
}
