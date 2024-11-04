<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Fee;

use InnoShop\Common\Repositories\TaxRateRepo;
use Throwable;

class Tax extends BaseService
{
    /**
     * @return void
     * @throws Throwable
     */
    public function addFee(): void
    {
        $taxes = $this->getTaxes();
        foreach ($taxes as $taxRateId => $value) {
            if ($value <= 0) {
                continue;
            }
            $taxFee = [
                'code'         => 'tax',
                'title'        => TaxRateRepo::getInstance()->getNameByRateId($taxRateId),
                'total'        => $value,
                'total_format' => currency_format($value),
            ];
            $this->checkoutService->addFeeList($taxFee);
        }
    }

    /**
     * Get all taxes by address and product.
     *
     * @return array
     * @throws Throwable
     */
    public function getTaxes(): array
    {
        $taxes = [];

        $shippingAddress = $this->checkoutService->getCheckout()->shippingAddress;
        $billingAddress  = $this->checkoutService->getCheckout()->billingAddress;
        $addressInfo     = [
            'shipping_address' => $shippingAddress,
            'billing_address'  => $billingAddress,
        ];

        $taxLib = \InnoShop\Common\Libraries\Tax::getInstance($addressInfo);

        foreach ($this->checkoutService->getCartList() as $product) {
            if (empty($product['tax_class_id'])) {
                continue;
            }

            $taxRates = $taxLib->getRates($product['price'], $product['tax_class_id']);
            foreach ($taxRates as $taxRate) {
                if (! isset($taxes[$taxRate['tax_rate_id']])) {
                    $taxes[$taxRate['tax_rate_id']] = ($taxRate['amount'] * $product['quantity']);
                } else {
                    $taxes[$taxRate['tax_rate_id']] += ($taxRate['amount'] * $product['quantity']);
                }
            }
        }

        return $taxes;
    }
}
