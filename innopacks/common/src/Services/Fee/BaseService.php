<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Fee;

use InnoShop\Common\Services\CheckoutService;

abstract class BaseService
{
    protected CheckoutService $checkoutService;

    /**
     * @param  CheckoutService  $checkoutService
     */
    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * @param  CheckoutService  $checkoutService
     * @return static
     */
    public static function getInstance(CheckoutService $checkoutService): static
    {
        return new static($checkoutService);
    }

    abstract public function addFee();
}
