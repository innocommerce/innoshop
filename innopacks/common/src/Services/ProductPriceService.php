<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Product\Sku;

class ProductPriceService
{
    private static array $finalPrices = [];

    private Sku $sku;

    private mixed $customer;

    /**
     * @param  Sku  $sku
     */
    public function __construct(Sku $sku)
    {
        $this->sku      = $sku;
        $this->customer = current_customer() ?: token_customer();
    }

    /**
     * @param  Sku  $sku
     * @return self
     */
    public static function getInstance(Sku $sku): ProductPriceService
    {
        return new self($sku);
    }

    /**
     * @return float
     */
    public function getFinal(): float
    {
        if (isset(self::$finalPrices[$this->sku->id])) {
            return self::$finalPrices[$this->sku->id];
        }

        $skuPrice = $this->sku->price;

        if ($this->customer instanceof Customer) {
            $skuPrice = $this->getCustomerPrice($skuPrice, $this->customer);
        }

        self::$finalPrices[$this->sku->id] = $skuPrice;

        return $skuPrice;
    }

    /**
     * @param  $skuPrice
     * @param  $customer
     * @return mixed
     */
    private function getCustomerPrice($skuPrice, $customer): mixed
    {
        $customerGroup = $customer->customerGroup;
        if (empty($customerGroup)) {
            return $skuPrice;
        }

        return round($skuPrice * $customerGroup->discount_rate / 100, 2);
    }
}
