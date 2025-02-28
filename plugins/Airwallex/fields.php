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
        'name'     => 'client_id',
        'label'    => 'Client ID',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|string',
    ],
    [
        'name'     => 'api_key',
        'label'    => 'API KEY',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|string',
    ],
    [
        'name'     => 'callback_secret',
        'label'    => 'Callback Secret',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|string',
    ],
    [
        'name'    => 'test_mode',
        'label'   => '测试模式',
        'type'    => 'select',
        'options' => [
            ['value' => '1', 'label' => '开启'],
            ['value' => '0', 'label' => '关闭'],
        ],
        'emptyOption' => false,
        'required'    => true,
        'description' => '如开启测试模式请填写测试配置, 关闭测试模式则填写正式配置',
    ],
];
