<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *
 * @see https://platform.openai.com/docs/models/
 */

return [
    [
        'name'     => 'api_key',
        'label'    => 'API Key',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required',
    ],
    [
        'name'        => 'proxy_url',
        'label'       => '代理地址',
        'type'        => 'string',
        'required'    => true,
        'rules'       => 'required',
        'description' => '不填写则使用官方接口地址: https://api.openai.com/v1/',
    ],
    [
        'name'    => 'model_type',
        'label'   => '使用模型',
        'type'    => 'select',
        'options' => [
            ['value' => 'gpt-4o-mini', 'label' => 'GPT-4o mini'],
            ['value' => 'gpt-4o', 'label' => 'GPT-4o'],
            ['value' => 'gpt-4-turbo', 'label' => 'GPT-4 Turbo'],
        ],
        'emptyOption' => false,
        'required'    => true,
        'rules'       => 'required',
    ],
];
