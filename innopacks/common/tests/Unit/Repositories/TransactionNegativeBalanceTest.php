<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use Exception;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Customer\Transaction;
use InnoShop\Common\Repositories\Customer\TransactionRepo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionNegativeBalanceTest extends TestCase
{
    #[Test]
    public function test_consumption_beyond_balance_is_rejected_and_not_persisted(): void
    {
        $customer = $this->createCustomer('wallet@example.com');
        $repo     = TransactionRepo::getInstance();
        $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_RECHARGE, 'amount' => 100]);

        try {
            $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_CONSUMPTION, 'amount' => 150]);
            $this->fail('Expected an insufficient-balance exception.');
        } catch (Exception $e) {
            $this->assertSame(trans('common/transaction.insufficient_balance'), $e->getMessage());
        }

        $this->assertSame(1, Transaction::query()->where('customer_id', $customer->id)->count());
        $this->assertEquals(100.0, (float) $customer->fresh()->balance);
    }

    #[Test]
    public function test_sequential_redemptions_cannot_overdraw_wallet(): void
    {
        $customer = $this->createCustomer('drain@example.com');
        $repo     = TransactionRepo::getInstance();
        $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_RECHARGE, 'amount' => 100]);
        $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_CONSUMPTION, 'amount' => 100]);

        $this->assertEquals(0.0, (float) $customer->fresh()->balance);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(trans('common/transaction.insufficient_balance'));

        $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_CONSUMPTION, 'amount' => 1]);
    }

    #[Test]
    public function test_ledger_resync_after_destroy_restores_balance(): void
    {
        $customer = $this->createCustomer('refund@example.com');
        $repo     = TransactionRepo::getInstance();
        $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_RECHARGE, 'amount' => 100]);
        $consumption = $repo->create(['customer_id' => $customer->id, 'type' => Transaction::TYPE_CONSUMPTION, 'amount' => 40]);

        $this->assertEquals(60.0, (float) $customer->fresh()->balance);

        $repo->destroy($consumption);

        $this->assertEquals(100.0, (float) $customer->fresh()->balance);
    }

    private function createCustomer(string $email): Customer
    {
        $customer           = new Customer;
        $customer->name     = 'Wallet Tester';
        $customer->email    = $email;
        $customer->password = bcrypt('password');
        $customer->active   = true;
        $customer->save();

        return $customer;
    }
}
