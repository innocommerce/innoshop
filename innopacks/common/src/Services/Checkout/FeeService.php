<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Checkout;

use InnoShop\Common\Services\Fee\Shipping;
use InnoShop\Common\Services\Fee\Subtotal;
use InnoShop\Common\Services\Fee\Tax;

class FeeService extends BaseService
{
    /**
     * @return void
     */
    public function calculate(): void
    {
        $classes = $this->getFeeMethodClasses();
        foreach ($classes as $class) {
            (new $class($this->checkoutService))->addFee();
        }
    }

    /**
     * Get order fee method classes
     * @return mixed
     */
    public function getFeeMethodClasses(): mixed
    {
        $classes = [
            Subtotal::class,
            Tax::class,
            Shipping::class,
        ];

        return fire_hook_filter('service.checkout.fee.methods', $classes);
    }
}
