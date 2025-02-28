<?php

namespace Plugin\AutoCurrencyRate\Services;

use InnoShop\Common\Models\Currency;

class AutoCurrencyTool
{
    public static function syncCurrency()
    {
        $setting = plugin_setting('auto_currency_rate');
        if (! isset($setting['api_key']) || empty($setting['api_key'])) {
            return '请先在插件配置里填写 汇率 key';
        }
        $exchangerate_key = $setting['api_key'];
        set_time_limit(0);

        $currencies = Currency::query()->get();

        $default_code = system_setting('currency');
        $rateSetting  = [];
        foreach ($currencies as $currency) {
            if ($default_code == $currency->code) {
                continue;
            }
            $key = strtolower($currency->code).'_currency';
            if (! isset($setting[$key]) || empty($setting[$key])) {
                $rateSetting[$currency->code] = 0;
            } else {
                $rateSetting[$currency->code] = bcdiv($setting[$key], 100, 8);
            }
        }

        //print_r($rateSetting);exit;
        //获取接口数据
        $rateUrl       = 'https://v6.exchangerate-api.com/v6/'.$exchangerate_key.'/latest/'.$default_code;
        $response_json = file_get_contents($rateUrl);
        if ($response_json !== false) {
            try {
                $response = json_decode($response_json, true);
                if ($response['result'] === 'success') {
                    $conversion_rates = $response['conversion_rates'];
                    foreach ($currencies as $currency) {
                        $code = $currency->code;
                        if ($default_code == $code) {
                            continue;
                        }
                        $code = strtoupper($code);
                        if (isset($conversion_rates[$code])) {
                            $newValue = bcmul($conversion_rates[$code], bcadd(1, $rateSetting[$currency->code], 8), 8);
                            if ($newValue != $currency->value) {
                                $currency->value = $newValue;
                                $currency->update();
                            }
                        }
                    }
                }

                return 'success';
            } catch (Exception $e) {
                // Handle JSON parse error...
                return $rateUrl.' 请求失败';
            }
        } else {
            return '接口获取数据失败';
        }
    }
}
