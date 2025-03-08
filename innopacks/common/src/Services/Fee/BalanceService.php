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
        $reference    = $checkoutData['reference'];
        $usedBalance  = $reference['balance'] ?? 0;
        if (empty($usedBalance)) {
            return;
        }

        $subtotalFee = [
            'code'         => 'balance',
            'title'        => 'Balance',
            'total'        => -$usedBalance,
            'total_format' => currency_format($usedBalance),
        ];

        $this->checkoutService->addFeeList($subtotalFee);
    }
}
