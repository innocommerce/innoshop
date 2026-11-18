<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Database\Factories\CartItemFactory;
use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Config;
use InnoShop\Common\Models\Customer;
use InnoShop\Front\Controllers\CartController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CartOwnershipTest extends TestCase
{
    public function test_guest_cannot_delete_another_sessions_cart_item(): void
    {
        $cart = CartItemFactory::new()->create(['customer_id' => 0, 'guest_id' => 'victim-session', 'item_type' => 'normal']);
        $this->app['session']->driver()->setId('attacker-session');

        try {
            (new CartController)->destroy($cart);
            $this->fail('A guest deleted another session\'s cart item.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('cart_items', ['id' => $cart->id]);
    }

    public function test_customer_cannot_delete_another_customers_cart_item(): void
    {
        Config::set('auth.providers.customer', ['driver' => 'eloquent', 'model' => Customer::class]);
        Config::set('auth.guards.customer', ['driver' => 'session', 'provider' => 'customer']);

        $victim   = CustomerFactory::new()->create();
        $attacker = CustomerFactory::new()->create();
        $cart     = CartItemFactory::new()->forCustomer($victim)->create(['guest_id' => '', 'item_type' => 'normal']);
        $this->actingAs($attacker, 'customer');

        try {
            (new CartController)->destroy($cart);
            $this->fail('A customer deleted another customer\'s cart item.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('cart_items', ['id' => $cart->id]);
    }

    public function test_guest_can_delete_own_cart_item(): void
    {
        $cart = CartItemFactory::new()->create(['customer_id' => 0, 'guest_id' => str_repeat('a', 40), 'item_type' => 'normal']);
        $this->app['session']->driver()->setId(str_repeat('a', 40));
        $this->assertSame(str_repeat('a', 40), current_guest_id());
        $this->assertSame(str_repeat('a', 40), $cart->fresh()->guest_id);

        (new CartController)->destroy($cart->fresh());

        $this->assertDatabaseMissing('cart_items', ['id' => $cart->id]);
    }
}
