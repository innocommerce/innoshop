<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PayFilter\Services;

use InnoShop\Common\Services\CheckoutService;
use Throwable;

class PaymentFilterService
{
    private string $billingCode;

    public function __construct(string $billingCode)
    {
        $this->billingCode = $billingCode;
    }

    /**
     * @param  $code
     * @return static
     */
    public static function getInstance($code): static
    {
        return new static($code);
    }

    /**
     * @return false
     * @throws Throwable
     */
    public function checkValid(): bool
    {
        $countryID = plugin_setting('pay_filter', $this->billingCode, false);
        $address   = CheckoutService::getInstance()->getShippingAddress();

        if (empty($countryID)) {
            return false;
        }

        if (empty($address)) {
            return false;
        }

        return $countryID == $address['country_id'] ?? 0;
    }
}
