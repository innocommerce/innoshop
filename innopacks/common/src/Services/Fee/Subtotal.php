<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Fee;

class Subtotal extends BaseService
{
    /**
     * @return void
     */
    public function addFee(): void
    {
        $total = $this->getSubtotal();

        $subtotalFee = [
            'code'         => 'subtotal',
            'title'        => 'Subtotal',
            'total'        => $total,
            'total_format' => currency_format($total),
        ];

        $this->checkoutService->addFeeList($subtotalFee);
    }

    /**
     * @return float
     */
    public function getSubtotal(): float
    {
        $cartList = $this->checkoutService->getCartList();

        return round(collect($cartList)->sum('subtotal'), 4);
    }
}
