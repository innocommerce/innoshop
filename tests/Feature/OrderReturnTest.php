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
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Item;
use Tests\TestCase;

/**
 * Order return (RMA) tests, including the IDOR fix on OrderReturnRepo::handleData.
 *
 * Routes are addressed by URL path (not the route() helper) because the front
 * route prefix depends on whether hide_url_locale() is true at app boot time,
 * which can flip between test methods as the locales table state changes.
 * Using URL strings avoids coupling tests to that boot-time decision.
 */
class OrderReturnTest extends TestCase
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

    public function test_customer_cannot_return_more_than_ordered_quantity()
    {
        $customer = $this->createCustomer('john.doe@example.com', 'John Doe');
        $order    = $this->createOrder($customer, 'ORD-001', 100.0);
        $item     = $this->createItem($order, 'SKU-001', 'Test Product', 5, 20.0);

        $this->actingAs($customer, 'customer');

        $endpoint = $this->accountEndpoint('order_returns.store');

        // 1. Return 3 (Valid)
        $this->post($endpoint, [
            'order_item_id' => $item->id,
            'quantity'      => 3,
            'opened'        => 1,
            'comment'       => 'Defective',
        ])->assertRedirect();

        $this->assertDatabaseHas('order_returns', [
            'order_item_id' => $item->id,
            'quantity'      => 3,
        ]);

        // 2. Return 3 again (Invalid: 3+3 = 6 > 5)
        $this->post($endpoint, [
            'order_item_id' => $item->id,
            'quantity'      => 3,
            'opened'        => 1,
            'comment'       => 'Defective again',
        ])->assertSessionHasErrors(['errors']);

        // 3. Return 2 (Valid: 3+2 = 5)
        $this->post($endpoint, [
            'order_item_id' => $item->id,
            'quantity'      => 2,
            'opened'        => 1,
            'comment'       => 'Returning the rest',
        ])->assertRedirect();

        $this->assertDatabaseHas('order_returns', [
            'order_item_id' => $item->id,
            'quantity'      => 2,
            'comment'       => 'Returning the rest',
        ]);
    }

    public function test_customer_cannot_create_return_for_other_customers_order_item_via_web()
    {
        $victim      = $this->createCustomer('victim@example.com', 'Victim');
        $victimOrder = $this->createOrder($victim, 'ORD-VICTIM-WEB', 50.0);
        $victimItem  = $this->createItem($victimOrder, 'VSKU', 'Victim Product', 1, 50.0);

        $attacker = $this->createCustomer('attacker@example.com', 'Attacker');
        $this->actingAs($attacker, 'customer');

        // The controller wraps OrderReturnRepo::create in a try/catch that
        // converts the IDOR abort(403) into a redirect with errors. So we
        // verify (a) the request is rejected (no row written) and (b) the
        // session carries an error message — both prove the repo gate fired.
        $response = $this->post($this->accountEndpoint('order_returns.store'), [
            'order_item_id' => $victimItem->id,
            'quantity'      => 1,
            'opened'        => 0,
            'comment'       => 'idor-attempt',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('order_returns', [
            'order_item_id' => $victimItem->id,
            'customer_id'   => $attacker->id,
        ]);
    }

    public function test_customer_cannot_create_return_for_other_customers_order_item_via_api()
    {
        $victim      = $this->createCustomer('victim-api@example.com', 'Victim API');
        $victimOrder = $this->createOrder($victim, 'ORD-VICTIM-API', 30.0);
        $victimItem  = $this->createItem($victimOrder, 'VSKU-API', 'Victim Product API', 1, 30.0);

        $attacker = $this->createCustomer('attacker-api@example.com', 'Attacker API');
        $token    = $attacker->createToken('test', ['customer'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/order_returns', [
                'order_item_id' => $victimItem->id,
                'quantity'      => 1,
                'opened'        => 0,
                'comment'       => 'idor-via-api',
            ]);

        // IDOR must be blocked at the repo layer (403), no row written.
        $this->assertContains($response->getStatusCode(), [403, 422]);

        $this->assertDatabaseMissing('order_returns', [
            'order_item_id' => $victimItem->id,
            'customer_id'   => $attacker->id,
        ]);
    }

    private function createCustomer(string $email, string $name): Customer
    {
        $customer           = new Customer;
        $customer->name     = $name;
        $customer->email    = $email;
        $customer->password = Hash::make('password');
        $customer->active   = true;
        $customer->save();

        return $customer;
    }

    private function createOrder(Customer $customer, string $number, float $total): Order
    {
        return Order::create([
            'customer_id'            => $customer->id,
            'customer_group_id'      => 1,
            'number'                 => $number,
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => $total,
            'status'                 => 'completed',
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

    private function createItem(Order $order, string $sku, string $name, int $qty, float $price): Item
    {
        return Item::create([
            'order_id'      => $order->id,
            'order_number'  => $order->number,
            'product_id'    => 1,
            'product_sku'   => $sku,
            'variant_label' => '',
            'name'          => $name,
            'image'         => '',
            'quantity'      => $qty,
            'price'         => $price,
            'total'         => $qty * $price,
            'item_type'     => 'product',
            'item_id'       => 1,
        ]);
    }

    /**
     * Resolve the account-area POST endpoint for a given front route name.
     *
     * Same reason as PaymentDisclosureTest::paymentUrl(): the front route prefix
     * depends on the boot-time locales state, which can flip across test methods
     * in the same process. Probe the router and rebuild the URI from the
     * registered route rather than calling route() (which throws if the route
     * is registered with a locale-prefixed name like `en.front.account...`).
     */
    private function accountEndpoint(string $routeName): string
    {
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName() ?? '';
            if (str_ends_with($name, ".front.account.{$routeName}") || $name === "front.account.{$routeName}") {
                return '/'.ltrim($route->uri(), '/');
            }
        }

        return '/account/order_returns';
    }
}
