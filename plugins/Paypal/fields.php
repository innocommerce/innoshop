<?php

/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use InnoShop\Common\Repositories\CurrencyRepo;

$currencies = CurrencyRepo::getInstance()->asOptions();

return [
    [
        'name'      => 'sandbox_client_id',
        'label_key' => 'common.sandbox_client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'sandbox_secret',
        'label_key' => 'common.sandbox_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'live_client_id',
        'label_key' => 'common.live_client_id',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'live_secret',
        'label_key' => 'common.live_secret',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|size:80',
    ],
    [
        'name'      => 'currency',
        'label_key' => 'common.currency',
        'type'      => 'select',
        'required'  => true,
        'rules'     => 'required|size:3',
        'options'   => $currencies,
    ],
    [
        'name'      => 'sandbox_mode',
        'label_key' => 'common.sandbox_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.enabled'],
            ['value' => '0', 'label_key' => 'common.disabled'],
        ],
        'required' => true,
    ],
];
