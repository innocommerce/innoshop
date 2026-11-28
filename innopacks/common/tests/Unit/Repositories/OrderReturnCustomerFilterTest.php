<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\OrderReturn;
use InnoShop\Common\Repositories\OrderReturnRepo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderReturnCustomerFilterTest extends TestCase
{
    #[Test]
    public function test_builder_scopes_returns_to_requested_customer(): void
    {
        $alice = $this->createCustomer('alice-rma@example.com', 'Alice');
        $bob   = $this->createCustomer('bob-rma@example.com', 'Bob');

        $this->createReturn($alice, 'RMA-ALICE-1');
        $this->createReturn($bob, 'RMA-BOB-SECRET');

        $repo    = OrderReturnRepo::getInstance();
        $aliceRs = $repo->builder(['customer_id' => $alice->id])->get();

        $this->assertCount(1, $aliceRs);
        $this->assertSame('RMA-ALICE-1', $aliceRs->first()->number);
        $this->assertTrue($aliceRs->every(fn ($r) => $r->customer_id === $alice->id));

        // Unfiltered (panel) listing still sees both.
        $this->assertCount(2, $repo->builder()->get());
    }

    private function createCustomer(string $email, string $name): Customer
    {
        $customer           = new Customer;
        $customer->name     = $name;
        $customer->email    = $email;
        $customer->password = bcrypt('password');
        $customer->active   = true;
        $customer->save();

        return $customer;
    }

    private function createReturn(Customer $customer, string $number): OrderReturn
    {
        return OrderReturn::query()->create([
            'customer_id'   => $customer->id,
            'order_id'      => 1,
            'order_item_id' => 1,
            'product_id'    => 1,
            'number'        => $number,
            'order_number'  => 'ORD-'.$number,
            'product_name'  => 'Product '.$number,
            'product_sku'   => 'SKU-'.$number,
            'quantity'      => 1,
            'opened'        => 0,
            'comment'       => 'test',
            'status'        => 'pending',
        ]);
    }
}
