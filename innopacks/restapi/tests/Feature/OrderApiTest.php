<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Tests\Feature;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Shipment;
use Tests\TestCase;

/**
 * Order lookups by number on the front API.
 *
 * Order numbers are not guaranteed to be numeric — imported orders or custom
 * number generators produce values like "ORD-2024-A1" — so the endpoints must
 * accept a string and answer 404 for an unknown number instead of failing with
 * a TypeError (HTTP 500).
 *
 * The shipment traces endpoint used by the storefront is customer scoped: a
 * customer may only read the traces of a shipment that belongs to their own
 * order.
 */
class OrderApiTest extends TestCase
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

        // The API route files are only loaded when the application is installed
        // (RestapiServiceProvider::boot() returns early when `installed()` is
        // false), which is never the case in the test environment. Register the
        // front API routes by hand so the endpoints can be exercised.
        Route::prefix('api')
            ->middleware([SubstituteBindings::class])
            ->name('api.')
            ->group(function () {
                require __DIR__.'/../../routes/front-api.php';
            });
    }

    public function test_order_by_number_with_letters_returns_the_order(): void
    {
        $customer = $this->createCustomer('letters@example.com');
        $order    = $this->createOrder($customer, 'ORD-ABC123');

        $response = $this->withCustomerToken($customer)->getJson('/api/orders/ORD-ABC123');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.number', 'ORD-ABC123');
    }

    public function test_unknown_order_number_returns_404(): void
    {
        $customer = $this->createCustomer('unknown@example.com');

        $response = $this->withCustomerToken($customer)->getJson('/api/orders/ORD-NOT-EXIST');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_customer_can_read_traces_of_own_order_shipment(): void
    {
        $customer = $this->createCustomer('traces@example.com');
        $order    = $this->createOrder($customer, 'ORD-TRACE01');
        $shipment = $this->createShipment($order);

        listen_hook_filter('service.shipping_trace.driver', fn () => FakeShippingTraceDriver::class, 10);

        $response = $this->withCustomerToken($customer)
            ->getJson("/api/orders/{$order->number}/shipments/{$shipment->id}/traces");

        $response->assertOk();
        $response->assertJsonPath('data.traces.0.station', 'Chengdu');
    }

    public function test_customer_cannot_read_traces_of_another_customers_order_shipment(): void
    {
        $victim   = $this->createCustomer('victim@example.com');
        $order    = $this->createOrder($victim, 'ORD-VICTIM1');
        $shipment = $this->createShipment($order);

        $attacker = $this->createCustomer('attacker@example.com');

        $response = $this->withCustomerToken($attacker)
            ->getJson("/api/orders/{$order->number}/shipments/{$shipment->id}/traces");

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }

    public function test_shipment_of_another_order_is_not_found(): void
    {
        $customer = $this->createCustomer('other-order@example.com');
        $order    = $this->createOrder($customer, 'ORD-MINE0001');

        $otherOrder = $this->createOrder($customer, 'ORD-MINE0002');
        $shipment   = $this->createShipment($otherOrder);

        $response = $this->withCustomerToken($customer)
            ->getJson("/api/orders/{$order->number}/shipments/{$shipment->id}/traces");

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    private function withCustomerToken(Customer $customer): static
    {
        $token = $customer->createToken('test', ['customer'])->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer '.$token);
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
            'status'                 => 'shipped',
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

    private function createShipment(Order $order): Shipment
    {
        return Shipment::query()->create([
            'order_id'        => $order->id,
            'express_code'    => 'sf',
            'express_company' => 'SF Express',
            'express_number'  => 'SF1234567890',
        ]);
    }
}

/**
 * Minimal shipping trace driver, the real one is provided by a shipping plugin.
 */
class FakeShippingTraceDriver
{
    /**
     * @param  string  $code
     * @param  string  $number
     * @return array
     */
    public function getTraces(string $code, string $number): array
    {
        return [
            [
                'time'    => '2026-01-01 10:00:00',
                'station' => 'Chengdu',
            ],
        ];
    }
}
