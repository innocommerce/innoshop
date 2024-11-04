<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 * Inspired by https://github.com/opencart/opencart/blob/master/upload/system/library/cart/tax.php
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

use Exception;
use InnoShop\Common\Models\Address;
use InnoShop\Common\Models\TaxRate;
use InnoShop\Common\Models\TaxRule;

class Tax
{
    private static array $taxRules = [];

    private int $storeCountryID;

    private int $storeStateID;

    private array $taxRates = [];

    public const AVAILABLE_TYPES = ['shipping', 'billing', 'store'];

    /**
     * @param  array  $data
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        $this->storeCountryID = (int) system_setting('country_id');
        $this->storeStateID   = (int) system_setting('state_id');

        $shippingAddress = $data['shipping_address'] ?? null;
        $billingAddress  = $data['billing_address']  ?? null;
        $this->setAddress($shippingAddress, $billingAddress);
    }

    /**
     * @param  array  $data
     * @return self
     * @throws Exception
     */
    public static function getInstance(array $data = []): Tax
    {
        return new self($data);
    }

    /**
     * @param  $shippingAddress
     * @param  $billingAddress
     * @return void
     * @throws Exception
     */
    private function setAddress($shippingAddress, $billingAddress): void
    {
        if ($shippingAddress) {
            if ($shippingAddress instanceof Address) {
                $this->setShippingAddress($shippingAddress->country_id, $shippingAddress->state_id);
            } else {
                $this->setShippingAddress($shippingAddress['country_id'], $shippingAddress['state_id']);
            }
        } elseif (system_setting('tax_address') == 'shipping') {
            $this->setShippingAddress($this->storeCountryID, $this->storeStateID);
        }

        if ($billingAddress) {
            if ($billingAddress instanceof Address) {
                $this->setBillingAddress($billingAddress->country_id, $billingAddress->state_id);
            } else {
                $this->setBillingAddress($billingAddress['country_id'], $billingAddress['state_id']);
            }
        } elseif (system_setting('tax_address') == 'billing') {
            $this->setBillingAddress($this->storeCountryID, $this->storeStateID);
        }

        $this->setStoreAddress($this->storeCountryID, $this->storeStateID);
    }

    /**
     * @param  $type
     * @param  $countryId
     * @param  $stateId
     * @return mixed
     */
    private function getTaxRules($type, $countryId, $stateId): mixed
    {
        if (self::$taxRules !== null && isset(self::$taxRules["$type-$countryId-$stateId"])) {
            return self::$taxRules["$type-$countryId-$stateId"];
        }

        $builder = TaxRule::query()
            ->from('tax_rules as rule')
            ->select('rule.*', 'rate.*')
            ->leftJoin('tax_rates as rate', 'rule.tax_rate_id', '=', 'rate.id')
            ->leftJoin('region_states as rs', 'rate.region_id', '=', 'rs.region_id')
            ->leftJoin('regions as region', 'rate.region_id', '=', 'region.id')
            ->where('rule.based', $type)
            ->where('rs.country_id', $countryId)
            ->where(function ($query) use ($stateId) {
                $query->where('rs.state_id', '=', 0)
                    ->orWhere('rs.state_id', '=', (int) $stateId);
            })
            ->orderBy('rule.priority');

        $data = $builder->get();

        self::$taxRules["$type-$countryId-$stateId"] = $data;

        return $data;
    }

    /**
     * @param  $type
     * @param  $countryId
     * @param  $stateId
     * @return void
     * @throws Exception
     */
    private function setTaxRatesByAddress($type, $countryId, $stateId): void
    {
        if (! in_array($type, self::AVAILABLE_TYPES)) {
            throw new Exception('invalid tax types');
        }

        $data = $this->getTaxRules($type, $countryId, $stateId);

        foreach ($data as $result) {
            $this->taxRates[$result->tax_class_id][$result->tax_rate_id] = [
                'tax_rate_id' => $result->tax_rate_id,
                'name'        => $result->name,
                'rate'        => $result->rate,
                'type'        => $result->type,
                'priority'    => $result->priority,
            ];
        }
    }

    /**
     * @param  $countryId
     * @param  $stateId
     * @return void
     * @throws Exception
     */
    public function setShippingAddress($countryId, $stateId): void
    {
        $this->setTaxRatesByAddress('shipping', $countryId, $stateId);
    }

    /**
     * @param  $countryId
     * @param  $stateId
     * @return void
     * @throws Exception
     */
    public function setBillingAddress($countryId, $stateId): void
    {
        $this->setTaxRatesByAddress('billing', $countryId, $stateId);
    }

    /**
     * @param  $countryId
     * @param  $stateId
     * @return void
     * @throws Exception
     */
    public function setStoreAddress($countryId, $stateId): void
    {
        $this->setTaxRatesByAddress('store', $countryId, $stateId);
    }

    /**
     * @return void
     */
    public function unsetRates(): void
    {
        $this->taxRates = [];
    }

    /**
     * $tax = InnoShop\Common\Libraries\Tax::getInstance();
     * $tax->setShippingAddress(1, 0);
     * $tax->calculate(123.45, 2, true)
     *
     * @param  $value
     * @param  $taxClassId
     * @param  bool  $calculate
     * @return int|mixed
     */
    public function calculate($value, $taxClassId, bool $calculate = true): mixed
    {
        if ($taxClassId && $calculate) {
            $amount   = 0;
            $taxRates = $this->getRates($value, $taxClassId);
            foreach ($taxRates as $taxRate) {
                if ($calculate != 'P' && $calculate != 'F') {
                    $amount += $taxRate['amount'];
                } elseif ($taxRate['type'] == $calculate) {
                    $amount += $taxRate['amount'];
                }
            }

            return $value + $amount;
        }

        return $value;
    }

    /**
     * @param  $value
     * @param  $taxClassId
     * @return int|mixed
     */
    public function getTax($value, $taxClassId): mixed
    {
        $amount   = 0;
        $taxRates = $this->getRates($value, $taxClassId);
        foreach ($taxRates as $taxRate) {
            $amount += $taxRate['amount'];
        }

        return $amount;
    }

    /**
     * @param  $taxRateId
     * @return string
     */
    public function getRateName($taxRateId): string
    {
        $taxRate = TaxRate::query()->find($taxRateId);

        return $taxRate->name ?? '';
    }

    /**
     * @param  $value
     * @param  $taxClassId
     * @return array
     */
    public function getRates($value, $taxClassId): array
    {
        $taxRateData = [];

        if (! isset($this->taxRates[$taxClassId])) {
            return $taxRateData;
        }

        foreach ($this->taxRates[$taxClassId] as $taxRate) {
            if (isset($taxRateData[$taxRate['tax_rate_id']])) {
                $amount = $taxRateData[$taxRate['tax_rate_id']]['amount'];
            } else {
                $amount = 0;
            }

            if ($taxRate['type'] == 'fixed') {
                $amount += $taxRate['rate'];
            } elseif ($taxRate['type'] == 'percent') {
                $amount += ($value / 100 * $taxRate['rate']);
            }

            $taxRateData[$taxRate['tax_rate_id']] = [
                'tax_rate_id' => $taxRate['tax_rate_id'],
                'name'        => $taxRate['name'],
                'rate'        => $taxRate['rate'],
                'type'        => $taxRate['type'],
                'amount'      => $amount,
            ];
        }

        return $taxRateData;
    }
}
