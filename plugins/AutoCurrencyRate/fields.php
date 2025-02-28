<?php

$currencies = \InnoShop\Common\Models\Currency::query()->get();

$default_code = system_setting('currency');

$rs   = [];
$rs[] = [
    'name'        => 'api_key',
    'label'       => '汇率接口Key',
    'type'        => 'string',
    'required'    => false,
    'description' => 'www.exchangerate-api.com',
];

$rs[] = [
    'name'        => 'api_token',
    'label'       => '接口Token',
    'type'        => 'string',
    'required'    => false,
    'description' => '定时任务地址：'.env('APP_URL').'/en/auto_currency_rate/task/{token}，其中{token}就是填写的 接口Token',
];

foreach ($currencies as $currency) {
    if ($default_code == $currency->code) {
        continue;
    }
    $rs[] = [
        'name'        => strtolower($currency->code).'_currency',
        'label'       => $currency->name.' 汇率益损（%）',
        'type'        => 'string',
        'required'    => false,
        'rules'       => 'required|numeric',
        'description' => '最终入库汇率 = 获取的汇率值 x (1+汇率益损/100)',
    ];
}

return $rs;
