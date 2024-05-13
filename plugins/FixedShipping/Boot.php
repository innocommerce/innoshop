<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FixedShipping;

use InnoShop\Common\Services\CheckoutService;
use InnoShop\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init() {}

    /**
     * Get quotes.
     *
     * @param  CheckoutService  $checkoutService
     * @return array
     */
    public function getQuotes(CheckoutService $checkoutService): array
    {
        $code     = $this->plugin->getCode();
        $resource = $this->pluginResource->jsonSerialize();
        $quotes[] = [
            'type'        => 'shipping',
            'code'        => "{$code}.0",
            'name'        => $resource['name'],
            'description' => $resource['description'],
            'icon'        => $resource['icon'],
            'cost'        => $this->getShippingFee($checkoutService),
        ];

        return $quotes;
    }

    /**
     * Calculate shipping fee.
     *
     * @param  CheckoutService  $checkoutService
     * @return float|int
     */
    public function getShippingFee(CheckoutService $checkoutService): float|int
    {
        $subtotal      = $checkoutService->getSubTotal();
        $shippingType  = plugin_setting('fixed_shipping', 'type', 'fixed');
        $shippingValue = plugin_setting('fixed_shipping', 'value', 0);
        if ($shippingType == 'fixed') {
            return $shippingValue;
        } elseif ($shippingType == 'percent') {
            return $subtotal * $shippingValue / 100;
        }

        return 0;
    }
}
