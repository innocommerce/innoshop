<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Fee;

use Throwable;

class BalanceService extends BaseService
{
    /**
     * @return void
     * @throws Throwable
     */
    public function addFee(): void
    {
        $checkoutData = $this->checkoutService->getCheckoutData();
        $reference    = $checkoutData['reference'] ?? [];
        $requested    = (float) ($reference['balance'] ?? 0);
        if ($requested <= 0) {
            return;
        }

        // Never trust the client-supplied amount: clamp to available wallet
        // balance and the current payable total (fees already accumulated).
        $available   = max(0, $this->checkoutService->getBalanceAmount());
        $payable     = max(0, $this->checkoutService->getAccumulatedFeeTotal());
        $usedBalance = round(min($requested, $available, $payable), 2);
        if ($usedBalance <= 0) {
            return;
        }

        $subtotalFee = [
            'code'         => 'balance',
            'title'        => 'Balance',
            'total'        => round(-$usedBalance, 2),
            'total_format' => currency_format($usedBalance),
        ];

        $this->checkoutService->addFeeList($subtotalFee);
    }
}
