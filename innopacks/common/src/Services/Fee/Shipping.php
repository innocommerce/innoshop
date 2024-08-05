<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Fee;

use InnoShop\Common\Services\Checkout\ShippingService;

class Shipping extends BaseService
{
    /**
     * Get shipping fee.
     *
     * @return void
     * @throws \Throwable
     */
    public function addFee(): void
    {
        $total = $this->getShippingFee();

        $shippingFee = [
            'code'         => 'shipping',
            'title'        => 'Shipping',
            'total'        => $total,
            'total_format' => currency_format($total),
        ];

        $this->checkoutService->addFeeList($shippingFee);
    }

    /**
     * Calculate the shipping cost based on the current delivery method from the corresponding plugin.
     *
     * @return float
     * @throws \Throwable
     */
    public function getShippingFee(): float
    {
        $checkoutData       = $this->checkoutService->getCheckoutData();
        $shippingMethodCode = $checkoutData['shipping_method_code'];
        $shippingMethods    = ShippingService::getInstance($this->checkoutService)->getMethods();

        foreach ($shippingMethods as $shippingMethod) {
            foreach ($shippingMethod['quotes'] as $quote) {
                if ($quote['code'] == $shippingMethodCode) {
                    return (float) ($quote['cost'] ?? 0);
                }
            }
        }

        return 0;
    }
}
