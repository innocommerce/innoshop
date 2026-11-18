<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use Tests\TestCase;

/**
 * Order numbers are not guaranteed to be numeric — imported orders or custom
 * number generators produce values like "ORD-2024-A1". Every order lookup by
 * number must therefore accept a string and answer 404 for an unknown number
 * instead of failing with a TypeError (HTTP 500).
 *
 * Routes are addressed by URL path (not the route() helper) because the front
 * route prefix depends on whether hide_url_locale() is true at app boot time.
 */
class OrderNumberLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.providers.customer', [
            'driver' => 'eloquent',
            'model'  => Customer::class,
        ]);

        Config::set('auth.guards.customer', [
            'driver'   => 'session',
            'provider' => 'customer',
        ]);
    }

    public function test_account_order_detail_with_letters_in_number_is_not_a_server_error(): void
    {
        $customer = $this->createCustomer('letters@example.com');
        $order    = $this->createOrder($customer, 'ORD-ABC123');

        $this->actingAs($customer, 'customer');

        $response = $this->get($this->orderUrl('front.account.orders.number_show', $order->number));

        $response->assertOk();
        $response->assertSee($order->number);
    }

    public function test_guest_order_detail_with_letters_in_number_is_not_a_server_error(): void
    {
        $customer = $this->createCustomer('guest-letters@example.com');
        $order    = $this->createOrder($customer, 'ORD-XYZ789');

        $this->actingAs($customer, 'customer');

        $response = $this->get($this->orderUrl('front.orders.number_show', $order->number));

        $response->assertOk();
        $response->assertSee($order->number);
    }

    public function test_recart_with_letters_in_number_is_not_a_server_error(): void
    {
        $customer = $this->createCustomer('recart@example.com');
        $order    = $this->createOrder($customer, 'ORD-RECART1');

        $this->actingAs($customer, 'customer');

        $response = $this->get($this->orderUrl('front.account.orders.recart', $order->number));

        $response->assertOk();
        $response->assertJsonPath('success', true);
    }

    public function test_unknown_order_number_returns_404(): void
    {
        $customer = $this->createCustomer('unknown@example.com');

        $this->actingAs($customer, 'customer');

        $this->get($this->orderUrl('front.account.orders.number_show', 'ORD-NOT-EXIST'))
            ->assertStatus(404);
    }

    /**
     * Resolve the front endpoint of an order route for a given order number.
     *
     * The route name is probed from the router because the front routes are
     * registered once per locale with a locale-prefixed name (e.g.
     * "en.front.account.orders.number_show") when the URL carries the locale.
     */
    private function orderUrl(string $routeName, string $number): string
    {
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName() ?? '';
            if ($name === $routeName || str_ends_with($name, '.'.$routeName)) {
                return '/'.ltrim(str_replace('{number}', $number, $route->uri()), '/');
            }
        }

        return '/account/orders/'.$number;
    }

    private function createCustomer(string $email): Customer
    {
        $customer           = new Customer;
        $customer->name     = explode('@', $email)[0];
        $customer->email    = $email;
        $customer->password = Hash::make('password');
        $customer->active   = true;
        $customer->save();

        return $customer;
    }

    private function createOrder(Customer $customer, string $number): Order
    {
        return Order::query()->create([
            'customer_id'            => $customer->id,
            'customer_group_id'      => 1,
            'number'                 => $number,
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => 101.66,
            'status'                 => 'paid',
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => $customer->name,
            'email'                  => $customer->email,
            'calling_code'           => '1',
            'telephone'              => '1234567890',
            'locale'                 => 'en',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'Test Agent',
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat Rate',
            'billing_method_code'    => 'cod',
            'billing_method_name'    => 'Cash On Delivery',
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
    }
}
