<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\CheckoutService;
use InnoShop\Common\Services\Fee\BalanceService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BalanceClampTest extends TestCase
{
    #[Test]
    public function test_clamps_forged_balance_to_payable_total(): void
    {
        $checkout = $this->mockCheckout(1000.0, 100.0, 51.66);
        $checkout->expects($this->once())
            ->method('addFeeList')
            ->with($this->callback(fn (array $fee) => $fee['code'] === 'balance' && abs($fee['total'] - (-51.66)) < 0.0001));

        (new BalanceService($checkout))->addFee();
    }

    #[Test]
    public function test_clamps_forged_balance_to_available_wallet(): void
    {
        $checkout = $this->mockCheckout(1000.0, 30.0, 100.0);
        $checkout->expects($this->once())
            ->method('addFeeList')
            ->with($this->callback(fn (array $fee) => abs($fee['total'] - (-30.0)) < 0.0001));

        (new BalanceService($checkout))->addFee();
    }

    #[Test]
    public function test_keeps_requested_amount_when_smallest(): void
    {
        $checkout = $this->mockCheckout(20.0, 100.0, 100.0);
        $checkout->expects($this->once())
            ->method('addFeeList')
            ->with($this->callback(fn (array $fee) => abs($fee['total'] - (-20.0)) < 0.0001));

        (new BalanceService($checkout))->addFee();
    }

    #[Test]
    public function test_no_fee_when_wallet_empty(): void
    {
        $checkout = $this->mockCheckout(50.0, 0.0, 100.0);
        $checkout->expects($this->never())->method('addFeeList');

        (new BalanceService($checkout))->addFee();
    }

    #[Test]
    public function test_no_fee_when_nothing_requested(): void
    {
        $checkout = $this->mockCheckout(0.0, 100.0, 100.0);
        $checkout->expects($this->never())->method('addFeeList');

        (new BalanceService($checkout))->addFee();
    }

    private function mockCheckout(float $requested, float $available, float $payable): CheckoutService
    {
        $checkout = $this->createMock(CheckoutService::class);
        $checkout->method('getCheckoutData')->willReturn(['reference' => ['balance' => $requested]]);
        $checkout->method('getBalanceAmount')->willReturn($available);
        $checkout->method('getAccumulatedFeeTotal')->willReturn($payable);

        return $checkout;
    }
}
