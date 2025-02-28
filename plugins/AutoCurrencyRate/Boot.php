<?php

namespace Plugin\AutoCurrencyRate;

use InnoShop\Common\Models\Currency;

class Boot
{
    public function init(): void
    {

        //计算金额
        listen_hook_filter('computer.currency.amount.default', function ($data) {

            $currency = Currency::query()->where('code', strtolower($data['from_code']))->first();
            if (! $currency) {
                return $data['amount'];
            }
            $setting      = plugin_setting('auto_currency_rate');
            $is_auto_rate = $data['is_auto_rate'];
            if ($is_auto_rate) {
                return bcmul($data['amount'], bcdiv(1, $currency->value, 5), 2);
            } else {
                $key = strtolower($currency->code).'_currency';
                if (! isset($setting[$key]) || empty($setting[$key])) {
                    return $data['amount'];
                } else {
                    $realRate = bcdiv($currency->value, bcadd(1, bcdiv($setting[$key], 100, 8), 8), 8);

                    //print_r($realRate);exit;
                    // print_r($data['amount']);exit;
                    return bcmul($data['amount'], bcdiv(1, $realRate, 5), 2);
                }
            }
        });
    }
}
