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
use Illuminate\Support\Facades\View;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use Tests\TestCase;

/**
 * Covers the broken-access-control flaw on the public payment result pages
 * (/payment/{success,fail,cancel}) reported by the third-party security audit.
 *
 * PaymentController enforces three layers of authorization:
 *   1. callerOwnsOrder       — logged-in customer matches order->customer_id
 *   2. inSessionWhitelist    — order number seeded by Order::created into the
 *                              current browser session (covers guest checkout)
 *   3. validTokenProvided    — high-entropy payment_token fallback (covers
 *                              cross-session links, e.g. order emails)
 *
 * An order is rendered only when one of the layers passes.
 *
 * Routes are addressed by URL path (not the route() helper) because the front
 * route prefix depends on whether hide_url_locale() is true at app boot time,
 * which can flip between test methods as the locales table state changes.
 */
class PaymentDisclosureTest extends TestCase
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

        // The front layout (`layouts.app`) references `route('api.home.base')` and
        // a handful of other routes whose registration is gated behind `installed()`
        // in RestAPIServiceProvider. In the test env that gate short-circuits to
        // false (settings table is created by RefreshDatabase *after* the service
        // providers boot), so the layout blows up at render time. We prepend a
        // stub location so `layouts.app` resolves to a minimal template that just
        // yields content — the security behavior under test lives entirely in
        // the controller, and the stub still lets us verify order number
        // disclosure via assertSee().
        $stubDir = sys_get_temp_dir().'/innoshop_test_views_'.uniqid();
        @mkdir($stubDir.'/layouts', 0755, true);
        file_put_contents($stubDir.'/layouts/app.blade.php', "@yield('content')");
        View::getFinder()->prependLocation($stubDir);
        View::getFinder()->flush();
    }

    public function test_guest_without_session_or_token_cannot_view_any_order_details(): void
    {
        $order = $this->createOrder(null, 'ORD-GUEST-NO-PERMISSION');

        $response = $this->get($this->paymentUrl('success', ['order_number' => $order->number]));

        $response->assertOk();
        $response->assertDontSee($order->number);
    }

    public function test_guest_in_session_whitelist_can_view_order_details(): void
    {
        $order = $this->createOrder(null, 'ORD-GUEST-SESSION');

        $response = $this->withSession(['guest_payment_order_numbers' => [$order->number]])
            ->get($this->paymentUrl('success', ['order_number' => $order->number]));

        $response->assertOk();
        $response->assertSee($order->number);
    }

    public function test_guest_with_valid_token_can_view_order_details(): void
    {
        $order = $this->createOrder(null, 'ORD-GUEST-TOKEN');

        $response = $this->get($this->paymentUrl('success', [
            'order_number' => $order->number,
            'token'        => $order->payment_token,
        ]));

        $response->assertOk();
        $response->assertSee($order->number);
    }

    public function test_guest_with_invalid_token_cannot_view_order_details(): void
    {
        $order = $this->createOrder(null, 'ORD-GUEST-BAD-TOKEN');

        $response = $this->get($this->paymentUrl('success', [
            'order_number' => $order->number,
            'token'        => 'not-the-real-token',
        ]));

        $response->assertOk();
        $response->assertDontSee($order->number);
    }

    public function test_logged_in_owner_can_view_own_order(): void
    {
        $customer = $this->createCustomer('owner@example.com');
        $order    = $this->createOrder($customer, 'ORD-OWNER');

        $this->actingAs($customer, 'customer');
        $response = $this->get($this->paymentUrl('success', ['order_number' => $order->number]));

        $response->assertOk();
        $response->assertSee($order->number);
    }

    public function test_logged_in_non_owner_cannot_view_other_customers_order(): void
    {
        $victim = $this->createCustomer('victim@example.com');
        $order  = $this->createOrder($victim, 'ORD-VICTIM');

        $attacker = $this->createCustomer('attacker@example.com');
        $this->actingAs($attacker, 'customer');

        $response = $this->get($this->paymentUrl('success', ['order_number' => $order->number]));

        $response->assertOk();
        $response->assertDontSee($order->number);
    }

    public function test_fail_and_cancel_pages_enforce_same_protection(): void
    {
        $order = $this->createOrder(null, 'ORD-FAIL-CANCEL');

        // No session, no token — both pages must not leak.
        $this->get($this->paymentUrl('fail', ['order_number' => $order->number]))
            ->assertOk()
            ->assertDontSee($order->number);

        $this->get($this->paymentUrl('cancel', ['order_number' => $order->number]))
            ->assertOk()
            ->assertDontSee($order->number);

        // With session whitelist both pages must render the order.
        $this->withSession(['guest_payment_order_numbers' => [$order->number]])
            ->get($this->paymentUrl('fail', ['order_number' => $order->number]))
            ->assertOk()
            ->assertSee($order->number);

        $this->withSession(['guest_payment_order_numbers' => [$order->number]])
            ->get($this->paymentUrl('cancel', ['order_number' => $order->number]))
            ->assertOk()
            ->assertSee($order->number);
    }

    public function test_out_trade_no_lookup_path_enforces_same_protection(): void
    {
        $order = $this->createOrder(null, 'ORD-OTN');

        // out_trade_no = {id}-{timestamp}; without session/token it must not leak.
        $outTradeNo = $order->id.'-'.time();
        $this->get($this->paymentUrl('success', ['out_trade_no' => $outTradeNo]))
            ->assertOk()
            ->assertDontSee($order->number);

        // With matching token the same lookup must succeed.
        $this->get($this->paymentUrl('success', [
            'out_trade_no' => $outTradeNo,
            'token'        => $order->payment_token,
        ]))
            ->assertOk()
            ->assertSee($order->number);
    }

    /**
     * Build a payment page URL with query string.
     *
     * The route prefix depends on whether `hide_url_locale()` is true at the
     * moment FrontServiceProvider::boot() runs. Boot happens once per test
     * process — but the locales table state at that moment depends on whether
     * a prior test already seeded locales (the static LocaleRepo cache holds
     * onto the seeded list across tests within the same process). So tests in
     * the same run can end up with different prefixes.
     *
     * We probe the actual router to pick whichever prefix actually exists.
     */
    private function paymentUrl(string $page, array $params = []): string
    {
        $query       = $params ? '?'.http_build_query($params) : '';
        $uriPrefix   = '';
        $routePrefix = '';
        foreach (Route::getRoutes()->get('GET') as $route) {
            $name = $route->getName() ?? '';
            if (str_ends_with($name, "front.payment.{$page}")) {
                $uriPrefix   = trim(str_replace("front.payment.{$page}", '', $name), '.');
                $routePrefix = $uriPrefix ? $uriPrefix.'/' : '';
                break;
            }
        }

        return '/'.$routePrefix.'payment/'.$page.$query;
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

    private function createOrder(?Customer $customer, string $number): Order
    {
        return Order::create([
            'customer_id'            => $customer?->id ?? 0,
            'customer_group_id'      => 1,
            'number'                 => $number,
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'total'                  => 101.66,
            'status'                 => 'completed',
            'shipping_address_id'    => 0,
            'billing_address_id'     => 0,
            'customer_name'          => $customer?->name ?? 'Guest',
            'email'                  => $customer?->email ?? 'guest@example.com',
            'calling_code'           => '1',
            'telephone'              => '1234567890',
            'locale'                 => 'en',
            'ip'                     => '127.0.0.1',
            'user_agent'             => 'Test Agent',
            'shipping_method_code'   => 'flat',
            'shipping_method_name'   => 'Flat Rate',
            'billing_method_code'    => 'cod',
            'billing_method_name'    => 'Cash On Delivery',
            'shipping_customer_name' => $customer?->name ?? 'Guest',
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
            'billing_customer_name'  => $customer?->name ?? 'Guest',
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
