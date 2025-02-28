<?php

return [
    [
        'name'    => 'captcha_type',
        'label'   => '图形验证码',
        'type'    => 'select',
        'options' => [
            [
                'value' => '0',
                'label' => '关闭',
            ],
            [
                'value' => '2',
                'label' => '腾讯',
            ],
        ],
        'required'    => true,
        'description' => '开启后有效防止机器人注册',
    ],
    [
        'name'        => 'captcha_id',
        'label'       => 'Captcha ID',
        'type'        => 'string',
        'required'    => false,
        'placeholder' => '',

    ],
    [
        'name'        => 'captcha_key',
        'label'       => 'Captcha Key',
        'type'        => 'string',
        'required'    => false,
        'placeholder' => '',

    ],
    [
        'name'        => 'tencent_secret_id',
        'label'       => '腾讯云 Secret ID',
        'type'        => 'string',
        'required'    => false,
        'placeholder' => '',
    ],
    [
        'name'        => 'tencent_secret_key',
        'label'       => '腾讯云 Secret Key',
        'type'        => 'string',
        'required'    => false,
        'placeholder' => '',
    ],
];
