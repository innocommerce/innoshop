<?php

namespace Plugin\AutoCurrencyRate\Controllers;

use Illuminate\Http\Request;
use Plugin\AutoCurrencyRate\Services\AutoCurrencyTool;

class AutoCurrencyRateController
{
    public function task(Request $request)
    {

        $token   = $request->token;
        $setting = plugin_setting('auto_currency_rate');
        if (! isset($setting['api_token']) || $token != $setting['api_token']) {
            exit('非法操作');
        }
        $rs = AutoCurrencyTool::syncCurrency();
        echo $rs;
    }
}
