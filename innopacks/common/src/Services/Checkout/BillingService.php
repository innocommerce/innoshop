<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Checkout;

use InnoShop\Plugin\Repositories\PluginRepo;
use InnoShop\Plugin\Resources\Checkout\PaymentMethodItem;

class BillingService
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @throws \Exception
     */
    public function getMethods(): array
    {
        $billingPlugins = PluginRepo::getInstance()->getBillingMethods();

        return PaymentMethodItem::collection($billingPlugins)->jsonSerialize();
    }
}
