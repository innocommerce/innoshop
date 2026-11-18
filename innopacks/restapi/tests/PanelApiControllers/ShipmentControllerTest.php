<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Tests\PanelApiControllers;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use InnoShop\Common\Models\Admin;
use InnoShop\Common\Models\Order;
use Tests\TestCase;

/**
 * Creating a shipment from the panel must advance a paid order to "shipped",
 * otherwise the order stays "paid" forever even though the customer has been
 * given a tracking number.
 */
class ShipmentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The API route files are only loaded when the application is installed
        // (RestapiServiceProvider::boot() returns early when `installed()` is
        // false), which is never the case in the test environment. Register the
        // panel API routes by hand so the endpoints can be exercised.
        Route::prefix('api/panel')
            ->middleware([SubstituteBindings::class])
            ->name('api.panel.')
            ->group(function () {
                require __DIR__.'/../../routes/panel-api.php';
            });
    }

    public function test_creating_a_shipment_advances_a_paid_order_to_shipped(): void
    {
        $order = $this->createOrder('paid');

        $response = $this->withAdminToken()->postJson("/api/panel/orders/{$order->id}/shipments", [
            'express_code'    => 'sf',
            'express_company' => 'SF Express',
            'express_number'  => 'SF1234567890',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'shipped',
        ]);
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status'   => 'shipped',
        ]);
    }

    public function test_creating_a_shipment_writes_exactly_one_shipment_row(): void
    {
        $order = $this->createOrder('paid');

        $this->withAdminToken()->postJson("/api/panel/orders/{$order->id}/shipments", [
            'express_code'    => 'sf',
            'express_company' => 'SF Express',
            'express_number'  => 'SF1234567890',
        ])->assertOk();

        $this->assertDatabaseCount('order_shipments', 1);
        $this->assertDatabaseHas('order_shipments', [
            'order_id'        => $order->id,
            'express_code'    => 'sf',
            'express_company' => 'SF Express',
            'express_number'  => 'SF1234567890',
        ]);
    }

    public function test_creating_a_shipment_keeps_the_status_of_a_not_paid_order(): void
    {
        $order = $this->createOrder('unpaid');

        $this->withAdminToken()->postJson("/api/panel/orders/{$order->id}/shipments", [
            'express_code'    => 'sf',
            'express_company' => 'SF Express',
            'express_number'  => 'SF1234567890',
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'unpaid',
        ]);
        $this->assertDatabaseCount('order_shipments', 1);
    }

    private function withAdminToken(): static
    {
        $admin           = new Admin;
        $admin->name     = 'Admin';
        $admin->email    = 'admin@example.com';
        $admin->password = Hash::make('password');
        $admin->active   = true;
        $admin->save();

        $token = $admin->createToken('test', ['admin'])->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer '.$token);
    }

    private function createOrder(string $status): Order
    {
        return Order::query()->create([
            'customer_id'            => 0,
            'customer_group_id'      => 1,
            'number'                 => 'ORD-'.strtoupper($status).'001',
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => 101.66,
            'status'                 => $status,
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => 'Guest',
            'email'                  => 'guest@example.com',
            'calling_code'           => '1',
            'telephone'              => '1234567890',
            'locale'                 => 'en',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'Test Agent',
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat Rate',
            'billing_method_code'    => 'cod',
            'billing_method_name'    => 'Cash On Delivery',
            'shipping_customer_name' => 'Guest',
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
            'billing_customer_name'  => 'Guest',
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
