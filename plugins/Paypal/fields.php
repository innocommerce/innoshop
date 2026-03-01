<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    [
        'name'     => 'sandbox_client_id',
        'label'    => 'Sandbox Client ID',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|size:80',
    ],
    [
        'name'     => 'sandbox_secret',
        'label'    => 'Sandbox Secret',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|size:80',
    ],
    [
        'name'     => 'live_client_id',
        'label'    => 'Live Client ID',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|size:80',
    ],
    [
        'name'     => 'live_secret',
        'label'    => 'Live Secret',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|size:80',
    ],
    [
        'name'      => 'sandbox_mode',
        'label_key' => 'setting.sandbox_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'setting.enabled'],
            ['value' => '0', 'label' => '关闭'],
        ],
        'required' => true,
    ],
];
