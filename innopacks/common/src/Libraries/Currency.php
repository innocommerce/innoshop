<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 * Inspired by https://github.com/opencart/opencart/blob/master/upload/system/library/cart/currency.php
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Repositories\CurrencyRepo;

class Currency
{
    private static ?Currency $instance = null;

    private Collection $currencies;

    /**
     * @return self
     */
    public static function getInstance(): Currency
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->currencies = $this->getCurrencies();
    }

    /**
     * @throws Exception
     */
    private function getCurrencies()
    {
        $currencies = CurrencyRepo::getInstance()->enabledList();
        if (empty($currencies)) {
            throw new Exception('Empty currencies!');
        }

        return $currencies->keyBy('code');
    }

    /**
     * @param  $price
     * @param  $currency
     * @param  float  $rate
     * @param  bool  $format
     * @return float|mixed|string
     */
    public function format($price, $currency, float $rate = 0, bool $format = true): mixed
    {
        $currency = strtolower($currency);
        if (empty($this->currencies)) {
            return $price;
        }

        $currencyRow = $this->currencies[$currency] ?? null;
        if (empty($currencyRow)) {
            return $price;
        }

        $price        = (float) $price;
        $symbol_left  = $currencyRow->symbol_left;
        $symbol_right = $currencyRow->symbol_right;
        $decimal      = (int) $currencyRow->decimal_place;

        if (! $rate) {
            $rate = $currencyRow->value;
        }

        if ($rate) {
            $price = $price * $rate;
        }
        $price = round($price, $decimal);

        if (! $format) {
            return $price;
        }

        $string = '';
        if ($price < 0) {
            $string = '-';
        }

        if ($symbol_left) {
            $string .= $symbol_left;
        }

        $string .= number_format(abs($price), $decimal);

        if ($symbol_right) {
            $string .= ' '.$symbol_right;
        }

        return $string;
    }

    /**
     * @param  $price
     * @param  $from
     * @param  $to
     * @return float|int
     */
    public function convert($price, $from, $to): float|int
    {
        if (isset($this->currencies[$from])) {
            $from = $this->currencies[$from]->value;
        } else {
            $from = 1;
        }

        if (isset($this->currencies[$to])) {
            $to = $this->currencies[$to]->value;
        } else {
            $to = 1;
        }

        return $price * ($to / $from);
    }

    /**
     * @param  $price
     * @param  $rate
     * @return float|int
     */
    public function convertByRate($price, $rate): float|int
    {
        return round($price * $rate, 2);
    }
}
